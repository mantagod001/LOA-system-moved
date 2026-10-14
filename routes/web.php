<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\LoaRequestController;
use App\Http\Controllers\Staff\ProfileController;
use App\Http\Controllers\Staff\ReportsController;
use App\Http\Controllers\Student\GrammarCheckController;
use App\Http\Controllers\Student\IdentityController;
use App\Http\Controllers\Student\LoaFormController;
use App\Http\Controllers\Student\ResubmitController;
use App\Http\Controllers\Student\StatusController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', HomeController::class)->name('home');

// ── Staff login / logout (self-registration removed) ─────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/staff/login',  [LoginController::class, 'create'])->name('login');
    Route::post('/staff/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');

    Route::get('/staff/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/staff/forgot-password', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:staff-password')
        ->name('password.request.store');
    Route::get('/staff/forgot-password/verify/{token}', [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
    Route::post('/staff/forgot-password/verify/{token}', [ForgotPasswordController::class, 'verify'])
        ->middleware('throttle:6,1')
        ->name('password.verify.store');
    Route::get('/staff/forgot-password/check-email', [ForgotPasswordController::class, 'sent'])->name('password.sent');
    Route::get('/staff/reset-password/{token}', [ForgotPasswordController::class, 'showReset'])->name('password.reset');
    Route::post('/staff/reset-password/{token}', [ForgotPasswordController::class, 'reset'])
        ->middleware('throttle:6,1')
        ->name('password.reset.store');
});

Route::post('/staff/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// GET fallback — handles direct navigation to /staff/logout (e.g. browser back button, stale link)
Route::get('/staff/logout', function () {
    return redirect()->route('login');
});

// ── Invitation setup (public — guest only, no auth required) ─────────────────
Route::middleware('guest')->group(function () {
    Route::get('/staff/invite/{token}',  [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/staff/invite/{token}', [InvitationController::class, 'store'])->name('invitation.store');
});

// ── Authenticated staff ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Dashboard, pipeline, reports, profile
    Route::get('/staff/dashboard', DashboardController::class)->name('staff.dashboard');
    Route::get('/staff/pipeline',  [LoaRequestController::class, 'pipeline'])->name('staff.pipeline');
    Route::get('/staff/reports',   ReportsController::class)->name('staff.reports');
    Route::get('/staff/profile',          [ProfileController::class, 'show'])->name('staff.profile');
    Route::patch('/staff/profile',        [ProfileController::class, 'updateName'])->name('staff.profile.name');

    // LOA detail / approve / reject / PDF download (dept-scoped)
    Route::middleware('dept.scope')->group(function () {
        Route::get('/staff/loa/{loaRequest}',                        [LoaRequestController::class, 'show'])->name('staff.loa.show');
        Route::get('/staff/loa/{loaRequest}/pdf',                    [LoaRequestController::class, 'pdf'])->name('staff.loa.pdf');
        Route::get('/staff/loa/{loaRequest}/files/{attachment}',     [LoaRequestController::class, 'attachment'])->name('staff.loa.attachment');
        Route::post('/staff/loa/{loaRequest}/approve',               [LoaRequestController::class, 'approve'])->name('staff.loa.approve');
        Route::post('/staff/loa/{loaRequest}/reject',                [LoaRequestController::class, 'reject'])->name('staff.loa.reject');
        Route::post('/staff/loa/{loaRequest}/discontinue',           [LoaRequestController::class, 'discontinue'])->name('staff.loa.discontinue');
        Route::post('/staff/loa/{loaRequest}/returned',              [LoaRequestController::class, 'markReturned'])->name('staff.loa.returned');
        Route::post('/staff/loa/{loaRequest}/did-not-enroll',        [LoaRequestController::class, 'markDidNotEnroll'])->name('staff.loa.did-not-enroll');
    });

    // Admin — user management (Administrator role only, enforced in controller)
    Route::middleware('admin.only')->group(function () {
        Route::get('/admin/users',                         [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create',                  [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users',                        [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit',             [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::patch('/admin/users/{user}',                [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}',               [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/admin/users/{user}/resend',          [AdminUserController::class, 'resend'])->name('admin.users.resend');
        Route::post('/admin/users/{user}/get-link',        [AdminUserController::class, 'getLink'])->name('admin.users.get-link');
    });
});

// ── Student LOA request flow ──────────────────────────────────────────────────
Route::get('/loa/request',            [IdentityController::class, 'create'])->name('student.identity');
Route::post('/loa/request',           [IdentityController::class, 'store'])
    ->middleware('throttle:loa-identity')
    ->name('student.identity.store');
Route::get('/loa/extend',            [IdentityController::class, 'create'])->name('student.extend');
Route::post('/loa/extend',           [IdentityController::class, 'store'])
    ->middleware('throttle:loa-identity')
    ->name('student.extend.store');
Route::get('/loa/request/check-email',[IdentityController::class, 'sent'])->name('student.identity.sent');
Route::get('/loa/request/verify/{token}',  [IdentityController::class, 'showVerify'])->name('student.identity.verify');
Route::post('/loa/request/verify/{token}', [IdentityController::class, 'verify'])
    ->middleware('throttle:6,1')
    ->name('student.identity.verify.store');

Route::get('/loa/form/{token}',           [LoaFormController::class, 'show'])->name('student.form.show');
Route::post('/loa/form/{token}',          [LoaFormController::class, 'store'])->name('student.form.store');
Route::post('/loa/form/{token}/grammar',  [GrammarCheckController::class, 'form'])
    ->middleware('throttle:grammar-check')
    ->name('student.form.grammar');
Route::get('/loa/form/{token}/submitted', [LoaFormController::class, 'submitted'])->name('student.form.submitted');

// ── LOA resubmission (token from rejection email) ─────────────────────────────
Route::get('/loa/resubmit/{token}',       [ResubmitController::class, 'show'])->name('student.resubmit.show');
Route::post('/loa/resubmit/{token}',      [ResubmitController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('student.resubmit.store');
Route::post('/loa/resubmit/{token}/grammar', [GrammarCheckController::class, 'resubmit'])
    ->middleware('throttle:grammar-check')
    ->name('student.resubmit.grammar');
Route::get('/loa/resubmit/{token}/done',  [ResubmitController::class, 'done'])->name('student.resubmit.done');

// ── LOA status check (public — no login needed) ───────────────────────────────
Route::match(['get', 'post'], '/loa/status', [StatusController::class, 'show'])
    ->middleware('throttle:10,1')
    ->name('student.status');
