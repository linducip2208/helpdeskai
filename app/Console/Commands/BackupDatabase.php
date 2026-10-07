<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : Number of daily backups to retain}';

    protected $description = 'Dump the MySQL database to storage/app/backups';

    public function handle(): int
    {
        $connection = config('database.default');

        if ($connection !== 'mysql') {
            $this->warn("Database connection is '{$connection}', not MySQL. Skipping mysqldump backup.");

            return self::SUCCESS;
        }

        $db = config('database.connections.mysql');
        $filename = 'backup-'.now()->format('Y-m-d_His').'.sql';
        $dir = storage_path('app/backups');

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir.DIRECTORY_SEPARATOR.$filename;
        $mysqldump = config('database.mysqldump_path', 'mysqldump');

        $args = [
            $mysqldump,
            '--host='.$db['host'],
            '--port='.$db['port'],
            '--user='.$db['username'],
            '--password='.$db['password'],
            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            $db['database'],
        ];

        $process = new Process($args);
        $process->setTimeout(600);

        $handle = fopen($path, 'w');
        $process->run(function ($type, $buffer) use ($handle) {
            if ($type === Process::OUT) {
                fwrite($handle, $buffer);
            }
        });
        fclose($handle);

        if (! $process->isSuccessful()) {
            @unlink($path);
            $this->error('Backup failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        $this->pruneOld($dir, (int) $this->option('keep'));

        $this->info('Backup written to '.$path);

        return self::SUCCESS;
    }

    private function pruneOld(string $dir, int $keep): void
    {
        $files = collect(glob($dir.DIRECTORY_SEPARATOR.'backup-*.sql'))
            ->sortDesc()
            ->values();

        foreach ($files->slice($keep) as $old) {
            @unlink($old);
        }
    }
}
