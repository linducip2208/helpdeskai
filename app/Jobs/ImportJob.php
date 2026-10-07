<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Department;
use App\Models\ImportLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    /**
     * @param  array<string, string>  $mapping  field => file column header
     */
    public function __construct(
        public int $logId,
        public array $mapping = [],
    ) {}

    public function handle(): void
    {
        $log = ImportLog::find($this->logId);

        if (! $log) {
            return;
        }

        // Idempotent: never re-run a log that already succeeded.
        if ($log->status === ImportLog::STATUS_SUCCESS) {
            return;
        }

        if (! Storage::disk('local')->exists($log->filename)) {
            $log->update(['status' => ImportLog::STATUS_FAILED, 'errors' => [
                ['row' => 0, 'errors' => [__('Import file not found.')]],
            ]]);

            return;
        }

        $log->update(['status' => ImportLog::STATUS_PROCESSING]);

        $path = Storage::disk('local')->path($log->filename);
        $data = self::readRows($path);
        $rows = self::applyMapping($data['rows'], $this->mapping ?: self::defaultMapping($log->type, $data['headers']));

        $imported = 0;
        $failed = 0;
        $errors = [];
        $seenInFile = [];

        foreach (array_chunk($rows, 200, true) as $chunk) {
            foreach ($chunk as $index => $row) {
                $rowNumber = $index + 2; // + header row, 1-based
                $rowErrors = self::validateRow($log->type, $row, $seenInFile);

                if ($rowErrors !== []) {
                    $failed++;
                    if (count($errors) < 100) {
                        $errors[] = ['row' => $rowNumber, 'errors' => $rowErrors];
                    }

                    continue;
                }

                try {
                    DB::transaction(function () use ($log, $row, &$seenInFile) {
                        $this->importRow($log->type, $row, $seenInFile);
                    });
                    $imported++;
                } catch (\Throwable $e) {
                    $failed++;
                    if (count($errors) < 100) {
                        $errors[] = ['row' => $rowNumber, 'errors' => [$e->getMessage()]];
                    }
                }
            }
        }

        $log->update([
            'total_rows' => count($rows),
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors === [] ? null : $errors,
            'status' => $failed === 0 ? ImportLog::STATUS_SUCCESS : ($imported > 0 ? ImportLog::STATUS_PARTIAL : ImportLog::STATUS_FAILED),
        ]);
    }

    /**
     * Read a CSV or XLSX file into headers + associative rows.
     *
     * @return array{headers: array<int, string>, rows: array<int, array<string, mixed>>}
     */
    public static function readRows(string $path, ?int $limit = null): array
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, ['xlsx', 'xls'], true)) {
            $sheet = IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);
            $raw = array_values(array_filter($sheet, fn ($r) => array_filter($r, fn ($c) => $c !== null && $c !== '')));
        } else {
            $raw = [];
            if (($handle = fopen($path, 'r')) !== false) {
                while (($line = fgetcsv($handle)) !== false) {
                    if (count(array_filter($line, fn ($c) => $c !== null && trim((string) $c) !== '')) === 0) {
                        continue;
                    }
                    $raw[] = $line;
                }
                fclose($handle);
            }
        }

        if ($raw === []) {
            return ['headers' => [], 'rows' => []];
        }

        $headers = array_map(fn ($h) => trim((string) $h), array_shift($raw));
        $rows = [];

        foreach ($raw as $line) {
            $line = array_values((array) $line);
            $assoc = [];
            foreach ($headers as $i => $header) {
                $assoc[$header] = isset($line[$i]) ? trim((string) $line[$i]) : null;
            }
            $rows[] = $assoc;
            if ($limit !== null && count($rows) >= $limit) {
                break;
            }
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * @return array<int, string>
     */
    public static function fieldsFor(string $type): array
    {
        return $type === 'tickets'
            ? ['subject', 'body', 'customer_email', 'department', 'category', 'priority', 'status']
            : ['name', 'email', 'phone', 'organization'];
    }

    /**
     * Auto-match file columns to fields (case-insensitive exact match).
     *
     * @param  array<int, string>  $headers
     * @return array<string, string>
     */
    public static function defaultMapping(string $type, array $headers): array
    {
        $lower = [];
        foreach ($headers as $header) {
            $lower[mb_strtolower($header)] = $header;
        }

        $mapping = [];
        foreach (self::fieldsFor($type) as $field) {
            if (isset($lower[mb_strtolower($field)])) {
                $mapping[$field] = $lower[mb_strtolower($field)];
            }
        }

        return $mapping;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<string, string>  $mapping
     * @return array<int, array<string, mixed>>
     */
    public static function applyMapping(array $rows, array $mapping): array
    {
        if ($mapping === []) {
            return $rows;
        }

        return array_map(function ($row) use ($mapping) {
            $out = [];
            foreach ($mapping as $field => $column) {
                $out[$field] = $row[$column] ?? null;
            }

            return $out;
        }, $rows);
    }

    /**
     * Validate one mapped row. Tracks in-file duplicates via $seenInFile.
     *
     * @param  array<string, mixed>  $row
     * @return array<int, string>
     */
    public static function validateRow(string $type, array $row, array &$seenInFile = []): array
    {
        $errors = [];

        if ($type === 'customers') {
            if (blank($row['name'] ?? null)) {
                $errors[] = __('Name is required.');
            }
            $email = mb_strtolower(trim((string) ($row['email'] ?? '')));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = __('A valid email is required.');
            } else {
                if (isset($seenInFile['email:'.$email])) {
                    $errors[] = __('Duplicate email in file.');
                } elseif (User::where('email', $email)->exists()) {
                    $errors[] = __('Email already exists.');
                }
            }
            if (! blank($row['phone'] ?? null) && mb_strlen((string) $row['phone']) > 32) {
                $errors[] = __('Phone is too long.');
            }
        } else {
            if (blank($row['subject'] ?? null)) {
                $errors[] = __('Subject is required.');
            }
            if (blank($row['body'] ?? null)) {
                $errors[] = __('Body is required.');
            }
            $customerEmail = mb_strtolower(trim((string) ($row['customer_email'] ?? '')));
            if ($customerEmail === '' || ! filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = __('A valid customer_email is required.');
            } elseif (! User::where('email', $customerEmail)->exists() && ! isset($seenInFile['new_customer:'.$customerEmail])) {
                $errors[] = __('Customer email not found. Import customers first.');
            }
            if (! blank($row['priority'] ?? null) && ! in_array(mb_strtolower((string) $row['priority']), ['low', 'medium', 'high', 'urgent'], true)) {
                $errors[] = __('Invalid priority.');
            }
            if (! blank($row['status'] ?? null) && ! in_array(mb_strtolower((string) $row['status']), ['open', 'in_progress', 'waiting', 'answered', 'resolved', 'closed'], true)) {
                $errors[] = __('Invalid status.');
            }
            if (! blank($row['department'] ?? null) && ! self::resolveDepartment($row['department'])) {
                $errors[] = __('Department not found.');
            }
            if (! blank($row['category'] ?? null) && ! self::resolveCategory($row['category'])) {
                $errors[] = __('Category not found.');
            }
            // Duplicate strategy: subject + customer_email (case-insensitive),
            // both inside the file and against existing tickets of that customer.
            $dupKey = mb_strtolower(trim((string) ($row['subject'] ?? ''))).'|'.$customerEmail;
            if (($row['subject'] ?? null) && $customerEmail !== '') {
                if (isset($seenInFile['ticket:'.$dupKey])) {
                    $errors[] = __('Duplicate ticket in file (same subject + customer).');
                } else {
                    $customer = User::where('email', $customerEmail)->first();
                    if ($customer && Ticket::where('user_id', $customer->id)->where('subject', $row['subject'])->exists()) {
                        $errors[] = __('Ticket already exists for this customer.');
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Dry-run: validate every row without writing anything.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public static function dryRun(string $type, array $rows): array
    {
        $errors = [];
        $seenInFile = [];
        $valid = 0;

        foreach ($rows as $index => $row) {
            $rowErrors = self::validateRow($type, $row, $seenInFile);
            // Register keys so later rows detect in-file duplicates.
            self::registerSeen($type, $row, $seenInFile);

            if ($rowErrors === []) {
                $valid++;
            } elseif (count($errors) < 20) {
                $errors[] = ['row' => $index + 2, 'errors' => $rowErrors];
            }
        }

        return [
            'total' => count($rows),
            'valid' => $valid,
            'invalid' => count($rows) - $valid,
            'errors' => $errors,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function importRow(string $type, array $row, array &$seenInFile): void
    {
        if ($type === 'customers') {
            $email = mb_strtolower(trim((string) $row['email']));
            $user = User::create([
                'name' => trim((string) $row['name']),
                'email' => $email,
                'phone' => blank($row['phone'] ?? null) ? null : trim((string) $row['phone']),
                'password' => Str::random(32),
            ]);
            $user->assignRole('customer');
            $seenInFile['email:'.$email] = true;

            // Note: 'organization' column is accepted from the file but not
            // persisted — this repo version has no organizations table.
            return;
        }

        $customerEmail = mb_strtolower(trim((string) $row['customer_email']));
        $customer = User::where('email', $customerEmail)->firstOrFail();
        $department = ! blank($row['department'] ?? null)
            ? self::resolveDepartment($row['department'])
            : Department::orderBy('id')->first();
        $category = blank($row['category'] ?? null) ? null : self::resolveCategory($row['category']);

        Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $customer->id,
            'department_id' => $department?->id,
            'category_id' => $category?->id,
            'subject' => trim((string) $row['subject']),
            'body' => trim((string) $row['body']),
            'priority' => in_array(mb_strtolower((string) ($row['priority'] ?? '')), ['low', 'medium', 'high', 'urgent'], true)
                ? mb_strtolower((string) $row['priority'])
                : 'medium',
            'status' => in_array(mb_strtolower((string) ($row['status'] ?? '')), ['open', 'in_progress', 'waiting', 'answered', 'resolved', 'closed'], true)
                ? mb_strtolower((string) $row['status'])
                : 'open',
            'source' => 'api',
        ]);

        $seenInFile['ticket:'.mb_strtolower(trim((string) $row['subject'])).'|'.$customerEmail] = true;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected static function registerSeen(string $type, array $row, array &$seenInFile): void
    {
        if ($type === 'customers') {
            $email = mb_strtolower(trim((string) ($row['email'] ?? '')));
            if ($email !== '') {
                $seenInFile['email:'.$email] = true;
            }
        } else {
            $key = mb_strtolower(trim((string) ($row['subject'] ?? ''))).'|'.mb_strtolower(trim((string) ($row['customer_email'] ?? '')));
            $seenInFile['ticket:'.$key] = true;
        }
    }

    protected static function resolveDepartment(mixed $value): ?Department
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Department::find((int) $value);
        }

        return Department::whereRaw('LOWER(name) = ?', [mb_strtolower($value)])->first();
    }

    protected static function resolveCategory(mixed $value): ?Category
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Category::find((int) $value);
        }

        return Category::whereRaw('LOWER(name) = ?', [mb_strtolower($value)])->first();
    }
}
