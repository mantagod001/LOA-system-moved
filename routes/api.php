<?php

use App\Http\Controllers\Api\StudentLookupController;
use Illuminate\Support\Facades\Route;

Route::get('/students/lookup/{student_id}', [StudentLookupController::class, 'show'])
    ->middleware('throttle:student-lookup')
    ->where('student_id', '[0-9\-]+')
    ->name('api.students.lookup');
