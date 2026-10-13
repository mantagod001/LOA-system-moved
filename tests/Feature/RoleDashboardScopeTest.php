<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Department;
use App\Models\LoaAccessToken;
use App\Models\LoaAttachment;
use App\Models\LoaRequest;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RoleDashboardScopeTest extends TestCase
{
    use RefreshDatabase;

    private Department $dcs;
    private Department $dte;
    private Program $bsit;
    private Program $beed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->dcs = Department::query()->where('code', 'DCS')->firstOrFail();
        $this->dte = Department::query()->where('code', 'DTE')->firstOrFail();
        $this->bsit = Program::query()->where('code', 'BSIT')->firstOrFail();
        $this->beed = Program::query()->where('code', 'BEED')->firstOrFail();
    }

    /**
     * AC-9: scopeForUser respects DH/global roles correctly.
     */
    public function test_scope_for_user_filters_dh_but_not_global(): void
    {
        $this->seedLoas(5, 5);

        $dcsDH = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $saso = User::factory()->create([
            'role' => UserRole::SasoOfficer,
        ]);

        $this->assertSame(5, LoaRequest::scopeForUser($dcsDH)->count());
        $this->assertSame(10, LoaRequest::scopeForUser($saso)->count());
    }

    /**
     * AC-1: DH dashboard uses staff shell sidebar + own-dept empty state (new unified layout).
     */
    public function test_dh_dashboard_uses_staff_shell_and_empty_state(): void
    {
        $dcsDH = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dcsDH)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            ->assertSeeText('Dashboard Overview')
            ->assertSeeText('No LOA submissions in your current scope yet.');
    }

    /**
     * AC-1: SASO dashboard uses staff shell sidebar; role name visible.
     */
    public function test_saso_dashboard_uses_staff_shell(): void
    {
        $user = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($user)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            ->assertSeeText('SASO Officer');
    }

    /**
     * AC-1: Campus Director dashboard uses staff shell.
     */
    public function test_campus_director_dashboard_uses_staff_shell(): void
    {
        $user = User::factory()->create(['role' => UserRole::CampusDirector]);

        $this->actingAs($user)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            ->assertSeeText('Campus Director');
    }

    /**
     * AC-1: Registrar dashboard uses staff shell.
     */
    public function test_registrar_dashboard_uses_staff_shell(): void
    {
        $user = User::factory()->create(['role' => UserRole::Registrar]);

        $this->actingAs($user)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            ->assertSeeText('Registrar');
    }

    /**
     * AC-1: Guidance dashboard uses staff shell.
     */
    public function test_guidance_dashboard_uses_staff_shell(): void
    {
        $user = User::factory()->create(['role' => UserRole::Guidance]);

        $this->actingAs($user)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Dashboard')
            ->assertSeeText('Approval Pipeline')
            ->assertSeeText('Reports & Analytics')
            ->assertSeeText('User Profile')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Request LOA')
            ->assertSeeText('Guidance Office');
    }

    /**
     * AC-1: Administrator dashboard uses staff shell and shows admin role label.
     */
    public function test_admin_is_redirected_from_dashboard_and_does_not_see_office_nav(): void
    {
        $user = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($user)
            ->get(route('staff.dashboard'))
            ->assertRedirect(route('staff.profile'));

        $this->actingAs($user)
            ->get(route('staff.profile'))
            ->assertOk()
            ->assertSeeText('User Profile')
            ->assertSeeText('User Management')
            ->assertSeeText('Logout')
            ->assertDontSeeText('Approval Pipeline')
            ->assertDontSeeText('Reports & Analytics')
            ->assertDontSeeText('Your Activity')
            ->assertDontSeeText('Request LOA')
            ->assertSeeText('System Administrator');
    }

    /**
     * AC-2: DH dashboard only shows own dept LOAs.
     */
    public function test_dh_dashboard_only_shows_own_department_loas(): void
    {
        $this->seedLoas(5, 5);

        $dcsDH = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $response = $this->actingAs($dcsDH)
            ->get(route('staff.dashboard'))
            ->assertOk();

        $loaCards = LoaRequest::query()->where('department_id', $this->dcs->id)->get();
        foreach ($loaCards as $loa) {
            $response->assertSeeText($loa->control_number);
        }

        $dteOnly = LoaRequest::query()->where('department_id', $this->dte->id)->firstOrFail();
        $response->assertDontSeeText($dteOnly->control_number, false);
    }

    /**
     * AC-3: Global roles see ALL LOAs.
     */
    public function test_global_roles_see_all_loas(): void
    {
        $this->seedLoas(5, 5);

        $roles = [
            UserRole::SasoOfficer,
            UserRole::CampusDirector,
            UserRole::Registrar,
        ];

        foreach ($roles as $role) {
            $user = User::factory()->create(['role' => $role]);

            $response = $this->actingAs($user)
                ->get(route('staff.dashboard'))
                ->assertOk();

            $all = LoaRequest::query()->get();
            $this->assertCount(10, $all);

            foreach ($all as $loa) {
                $response->assertSeeText($loa->control_number);
            }
        }
    }

    /**
     * AC-4: Cross-dept DH gets 403 on detail route.
     */
    public function test_dh_cross_department_detail_forbidden(): void
    {
        $this->seedLoas(0, 1);
        $dteLoa = LoaRequest::query()->where('department_id', $this->dte->id)->firstOrFail();

        $dcsDH = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dcsDH)
            ->get(route('staff.loa.show', $dteLoa))
            ->assertForbidden();
    }

    /**
     * AC-6: Same-dept DH gets 200 on detail route.
     */
    public function test_dh_same_department_detail_ok(): void
    {
        $this->seedLoas(1, 0);
        $dcsLoa = LoaRequest::query()->where('department_id', $this->dcs->id)->firstOrFail();

        $dcsDH = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dcsDH)
            ->get(route('staff.loa.show', $dcsLoa))
            ->assertOk();
    }

    /**
     * AC-5 + AC-6: Attachment scope check (cross-dept 403, own-dept 200).
     */
    public function test_attachment_scope_cross_dept_forbidden_own_dept_ok(): void
    {
        Storage::fake('local');
        $this->seedLoas(1, 1);

        $dcsLoa = LoaRequest::query()->where('department_id', $this->dcs->id)->firstOrFail();
        $dteLoa = LoaRequest::query()->where('department_id', $this->dte->id)->firstOrFail();

        $dcsFile = $this->addAttachmentTo($dcsLoa, 'dcs-medical.pdf');
        $dteFile = $this->addAttachmentTo($dteLoa, 'dte-medical.pdf');

        $dcsDH = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dcsDH)
            ->get(route('staff.loa.attachment', [$dcsLoa, $dcsFile]))
            ->assertSuccessful();

        $this->actingAs($dcsDH)
            ->get(route('staff.loa.attachment', [$dteLoa, $dteFile]))
            ->assertForbidden();
    }

    /**
     * SASO (global) can open any dept LOA detail.
     */
    public function test_saso_can_access_any_dept_detail(): void
    {
        $this->seedLoas(0, 1);
        $dteLoa = LoaRequest::query()->where('department_id', $this->dte->id)->firstOrFail();

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.loa.show', $dteLoa))
            ->assertOk();
    }

    /**
     * AC-8: Stage badge renders on dashboard cards.
     */
    public function test_status_badge_renders_on_dashboard_card(): void
    {
        $this->seedLoasWithStatuses();

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Submitted')
            ->assertSeeText('Rejected')
            ->assertSeeText('Withdrawn / Discontinued')
            ->assertSeeText('Draft');
    }

    /**
     * AC-2: Dashboard Overview table has correct 7 column headers.
     */
    public function test_dashboard_overview_table_has_7_correct_columns(): void
    {
        $this->seedLoas(3, 0);

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('App ID')
            ->assertSeeText('Student ID')
            ->assertSeeText('Student Name')
            ->assertSeeText('Date Effective')
            ->assertSeeText('Return Date')
            ->assertSeeText('Approval Stage / Status')
            ->assertSeeText('Actions');
    }

    /**
     * AC-8: Detail page shows status badge in header.
     */
    public function test_detail_page_shows_status_badge_in_header(): void
    {
        $this->seedLoas(1, 0);
        $loa = LoaRequest::query()->where('department_id', $this->dcs->id)->firstOrFail();

        $dcsDH = User::factory()->create([
            'role' => UserRole::DepartmentHead,
            'department_id' => $this->dcs->id,
        ]);

        $this->actingAs($dcsDH)
            ->get(route('staff.loa.show', $loa))
            ->assertOk()
            ->assertSeeText($loa->statusLabel());
    }

    /**
     * Dashboard cards contain clickable link to detail page (control# visible in card).
     */
    public function test_dashboard_card_contains_control_number_and_link(): void
    {
        $this->seedLoas(1, 0);
        $loa = LoaRequest::query()->firstOrFail();

        $saso = User::factory()->create(['role' => UserRole::SasoOfficer]);

        $this->actingAs($saso)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText($loa->control_number)
            ->assertSee(route('staff.loa.show', $loa), false);
    }

    private function seedLoas(int $dcsCount, int $dteCount): void
    {
        foreach (range(1, $dcsCount) as $i) {
            $this->createLoa($this->dcs, $this->bsit, 'submitted');
        }
        foreach (range(1, $dteCount) as $i) {
            $this->createLoa($this->dte, $this->beed, 'submitted');
        }
    }

    private function seedLoasWithStatuses(): void
    {
        $statuses = ['submitted', 'rejected', 'discontinued', 'draft'];
        foreach ($statuses as $status) {
            $this->createLoa($this->dcs, $this->bsit, $status);
        }
    }

    private function createLoa(Department $dept, Program $prog, string $status): LoaRequest
    {
        $token = LoaAccessToken::create([
            'student_id'   => fake()->numerify('####-####'),
            'full_name'    => fake()->name(),
            'email'        => fake()->safeEmail(),
            'token_hash'   => hash('sha256', Str::random(40)),
            'expires_at'   => now()->addHours(24),
        ]);

        $loa = LoaRequest::create([
            'loa_access_token_id' => $token->id,
            'student_id'          => $token->student_id,
            'full_name'           => $token->full_name,
            'email'               => $token->email,
            'department_id'       => $dept->id,
            'program_id'          => $prog->id,
            'year_level'          => $prog->code . '-1st',
            'start_date'          => now()->addDays(5)->toDateString(),
            'return_date'         => now()->addDays(60)->toDateString(),
            'reason'              => fake()->paragraph(),
            'parent_full_name'    => fake()->name(),
            'parent_relationship' => 'Parent',
            'parent_phone'        => fake()->phoneNumber(),
            'status'              => $status,
            'submitted_at'        => in_array($status, ['draft'], true) ? null : now()->subMinutes(rand(5, 5000)),
        ]);

        if (!in_array($status, ['draft'], true)) {
            $loa->assignControlNumber();
            $loa->save();
        }

        return $loa;
    }

    private function addAttachmentTo(LoaRequest $loa, string $fileName): LoaAttachment
    {
        $file = UploadedFile::fake()->create($fileName, 50);
        $path = $file->storeAs('attachments', $file->hashName(), 'local');

        return LoaAttachment::create([
            'loa_request_id' => $loa->id,
            'original_name'  => $fileName,
            'path'           => $path,
            'mime_type'      => 'application/pdf',
            'size'           => 50 * 1024,
        ]);
    }
}
