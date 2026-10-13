<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LoaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusController extends Controller
{
    public function show(Request $request): View
    {
        $loa      = null;
        $notFound = false;

        if ($request->isMethod('post')) {
            $request->validate([
                'control_number' => ['required', 'string', 'max:30'],
                'student_id'     => ['required', 'string', 'regex:/^\d{4}-\d{4,6}$/'],
            ], [
                'control_number.required' => 'Please enter your control number.',
                'student_id.required'     => 'Please enter your student ID number.',
                'student_id.regex'        => 'Student ID must follow the format YYYY-NNNNN, e.g. 2021-12345.',
            ]);

            // Both control number AND student ID must match — prevents cross-student lookup.
            $loa = LoaRequest::query()
                ->with([
                    'department',
                    'program',
                    'parentLoa',
                    'presidentActor',
                    'deptHeadActor',
                    'sasoActor',
                    'campusDirectorActor',
                    'rejectedByActor',
                    'rejectionHistory.rejectedByActor',
                ])
                ->where('control_number', trim($request->input('control_number')))
                ->where('student_id', trim($request->input('student_id')))
                ->whereIn('status', ['submitted', 'approved', 'rejected', 'discontinued'])
                ->first();

            if (! $loa) {
                $notFound = true;
            }
        }

        return view('student.status', compact('loa', 'notFound'));
    }
}
