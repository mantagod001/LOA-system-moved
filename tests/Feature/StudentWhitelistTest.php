<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaRequest;
use App\Models\Program;
use App\Models\StudentWhitelist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentWhitelistTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_can_upload_csv_and_update_without_duplicates(): void
    {
        $this->seed();
        $registrar = User::factory()->create([
            'role' => UserRole::Registrar,
            'department_id' => null,
        ]);

        $this->actingAs($registrar)
            ->get(route('staff.students.index'))
            ->assertOk()
            ->assertSeeText('Official student list');

        $csv = implode("\n", [
            'student_id,first_name,middle_name,last_name,email,contact_number',
            '2021-0001,Juan,Dela,Cruz,juan@evsu.edu.ph,09171234567',
            '2021-0002,Maria,Perez,Santos,,',
            '',
            '2021-0003,,',
        ]);

        $this->actingAs($registrar)
            ->post(route('staff.students.store'), [
                'csv' => $this->csvFile($csv),
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('student_whitelist', 2);
        $this->assertDatabaseHas('student_whitelist', [
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'email' => 'juan@evsu.edu.ph',
        ]);

        $update = implode("\n", [
            'id,fname,mname,lname,email,phone',
            '20210001,Juan,Dela,Cruz,,09170000000',
            '2021-0002,Maria,Perez,Santos,,',
        ]);

        $this->actingAs($registrar)
            ->post(route('staff.students.store'), [
                'csv' => $this->csvFile($update, 'round2.csv'),
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('student_whitelist', 2);
        $juan = StudentWhitelist::query()->where('student_id', '2021-0001')->first();
        $this->assertSame('juan@evsu.edu.ph', $juan->email);
        $this->assertSame('09170000000', $juan->contact_number);
    }

    public function test_registrar_can_upload_xlsx(): void
    {
        $this->seed();
        $registrar = User::factory()->create([
            'role' => UserRole::Registrar,
            'department_id' => null,
        ]);

        $this->actingAs($registrar)
            ->post(route('staff.students.store'), [
                'csv' => $this->xlsxFile(),
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('student_whitelist', [
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'email' => 'juan@evsu.edu.ph',
        ]);
    }

    public function test_department_head_and_admin_cannot_open_student_list(): void
    {
        $this->seed();
        $dcs = Department::query()->where('code', 'DCS')->firstOrFail();

        $dh = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $dcs->id,
        ]);
        $this->actingAs($dh)->get(route('staff.students.index'))->assertForbidden();

        $admin = User::factory()->create(['role' => UserRole::Administrator]);
        $this->actingAs($admin)->get(route('staff.students.index'))->assertForbidden();
    }

    public function test_lookup_returns_student_or_not_found(): void
    {
        $this->seed();
        StudentWhitelist::query()->create([
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
        ]);

        $this->getJson('/api/students/lookup/2021-0001')
            ->assertOk()
            ->assertJson([
                'found' => true,
                'student_id' => '2021-0001',
                'name' => 'Juan Dela Cruz',
            ]);

        $this->getJson('/api/students/lookup/2021-9999')
            ->assertNotFound()
            ->assertJson(['found' => false]);
    }

    public function test_form_prefills_id_and_name_from_csv_email_and_keeps_old_submit_process(): void
    {
        Mail::fake();
        Storage::fake('local');
        $this->seed();

        $department = Department::query()->where('code', 'DCS')->firstOrFail();
        $program = Program::query()->where('code', 'BSIT')->firstOrFail();

        StudentWhitelist::query()->create([
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'email' => 'juan@evsu.edu.ph',
        ]);

        $token = $this->issueFormToken('juan@evsu.edu.ph');
        $this->get(route('student.form.show', $token))
            ->assertOk()
            ->assertSee('2021-0001')
            ->assertSee('Juan')
            ->assertSee('Cruz')
            ->assertSee('Filled from the official student list');

        $this->post(route('student.form.store', $token), $this->formPayload($department->id, $program->id, [
            'student_id' => '2021-0001',
            'first_name' => 'Wrong',
            'last_name' => 'Name',
        ]))->assertRedirect(route('student.form.submitted', $token));

        $this->assertDatabaseHas('loa_requests', [
            'email' => 'juan@evsu.edu.ph',
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'status' => 'submitted',
        ]);

        $token2 = $this->issueFormToken('ana@evsu.edu.ph');
        $this->get(route('student.form.show', $token2))
            ->assertRedirect(route('student.identity'))
            ->assertSessionHasErrors('email');

        $this->post(route('student.form.store', $token2), $this->formPayload($department->id, $program->id, [
            'student_id' => '2021-8888',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
        ]))->assertRedirect(route('student.identity'));

        $this->assertDatabaseMissing('loa_requests', [
            'email' => 'ana@evsu.edu.ph',
        ]);
    }

    public function test_unlisted_evsu_email_does_not_receive_a_code(): void
    {
        Mail::fake();
        $this->seed();

        $this->post(route('student.identity.store'), [
            'email' => 'missing@evsu.edu.ph',
        ])->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_form_rejects_an_email_that_is_not_on_the_list(): void
    {
        Mail::fake();
        Storage::fake('local');
        $this->seed();

        $department = Department::query()->where('code', 'DCS')->firstOrFail();
        $program = Program::query()->where('code', 'BSIT')->firstOrFail();
        $token = $this->issueFormToken();
        $this->get(route('student.form.show', $token))
            ->assertRedirect(route('student.identity'))
            ->assertSessionHasErrors('email');

        $this->post(route('student.form.store', $token), $this->formPayload($department->id, $program->id))
            ->assertRedirect(route('student.identity'));

        $this->assertDatabaseMissing('loa_requests', [
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
        ]);
    }

    private function csvFile(string $contents, string $name = 'students.csv'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $contents);
    }

    private function xlsxFile(): UploadedFile
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'students-'.uniqid().'.xlsx';
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml" ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>
</workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>');
        $cell = fn (string $ref, string $text) => '<c r="'.$ref.'" t="inlineStr"><is><t>'.$text.'</t></is></c>';
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>
<row r="1">'.$cell('A1', 'student_id').$cell('B1', 'first_name').$cell('C1', 'middle_name').$cell('D1', 'last_name').$cell('E1', 'email').'</row>
<row r="2">'.$cell('A2', '2021-0001').$cell('B2', 'Juan').$cell('C2', 'Dela').$cell('D2', 'Cruz').$cell('E2', 'juan@evsu.edu.ph').'</row>
</sheetData></worksheet>');
        $zip->close();

        return new UploadedFile($path, 'students.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function issueFormToken(string $email = 'juan@evsu.edu.ph'): string
    {
        [$record, $plain] = LoaAccessToken::issueForEmail($email, [
            'student_id' => '',
            'first_name' => '',
            'last_name' => '',
        ]);
        $record->update(['otp_verified' => true]);

        return $plain;
    }

    /** @param  array<string, mixed>  $overrides */
    private function formPayload(int $departmentId, int $programId, array $overrides = []): array
    {
        return array_merge([
            'student_id' => '2021-0001',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'department_id' => $departmentId,
            'program_id' => $programId,
            'year_level' => 'BSIT-1st',
            'academic_year' => '2026-2027',
            'semester' => 'first',
            'start_date' => '2026-10-01',
            'return_date' => '2027-03-01',
            'reason' => 'Medical treatment requiring extended recovery.',
            'parent_first_name' => 'Maria',
            'parent_middle_name' => '',
            'parent_last_name' => 'Dela Cruz',
            'parent_relationship' => 'mother',
            'parent_phone' => '917-123-4567',
            'attachment_categories' => ['medical', 'valid_id'],
            'attachments' => [
                UploadedFile::fake()->create('medical.pdf', 120, 'application/pdf'),
                UploadedFile::fake()->image('id.jpg'),
            ],
        ], $overrides);
    }
}
