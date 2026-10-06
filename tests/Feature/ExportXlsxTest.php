<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\BypassesPairing;
use Tests\TestCase;

class ExportXlsxTest extends TestCase
{
    use BypassesPairing, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->bypassPairing();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_tickets_xlsx_downloads_and_parses(): void
    {
        $department = Department::create(['name' => 'Support', 'is_active' => true]);
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Ticket::create([
            'uid' => Ticket::generateUid(),
            'user_id' => $owner->id,
            'department_id' => $department->id,
            'subject' => 'XLSX Subject',
            'body' => 'Body',
            'priority' => 'high',
            'status' => 'open',
            'source' => 'web',
        ]);

        $response = $this->actingAs($admin)->get('/admin/export/tickets.xlsx');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx').'.xlsx';
        file_put_contents($tmp, $response->streamedContent());

        try {
            $sheet = IOFactory::load($tmp)->getActiveSheet();
            $this->assertEquals('UID', $sheet->getCell('A1')->getValue());
            $this->assertEquals('Subject', $sheet->getCell('B1')->getValue());

            $found = false;
            foreach ($sheet->getRowIterator(2) as $row) {
                $values = [];
                foreach ($row->getCellIterator() as $cell) {
                    $values[] = $cell->getValue();
                }
                if (in_array('XLSX Subject', $values, true)) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'Ticket row missing from XLSX.');
        } finally {
            @unlink($tmp);
        }
    }

    public function test_agents_and_sla_xlsx_downloads(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin/export/agents.xlsx')->assertOk();
        $this->actingAs($admin)->get('/admin/export/sla.xlsx')->assertOk();
        $this->actingAs($admin)->get('/admin/export/ai-usage.xlsx')->assertOk();
    }
}
