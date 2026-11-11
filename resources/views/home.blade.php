@extends('layouts.app')

@section('title', 'Home')

@php
    $faq = [
        ['group' => 'Requesting', 'question' => 'How do I request a leave?', 'keywords' => ['request', 'apply', 'new leave'], 'answer' => 'Students do not create an account. Choose Request LOA and enter your official @evsu.edu.ph email. We email a 6-digit code that expires in 10 minutes. After that, we email a private form link. Open it the same day. It stops working one day after it is sent. Complete the form and submit. You then receive a control number.'],
        ['group' => 'Requesting', 'question' => 'Which email can I use?', 'keywords' => ['email', 'gmail', 'yahoo', 'evsu'], 'answer' => 'Only an @evsu.edu.ph address is accepted. Gmail, Yahoo, and other personal emails are rejected.'],
        ['group' => 'Requesting', 'question' => 'Why is my name already filled in?', 'keywords' => ['filled', 'prefill', 'student number', 'student id'], 'answer' => 'If the Registrar’s student list has your email, the form fills your student number and name. If you are not on the list, type them yourself. You can still submit.'],
        ['group' => 'Requesting', 'question' => 'What documents can I attach?', 'keywords' => ['document', 'documents', 'attach', 'medical', 'requirements'], 'answer' => 'Documents are optional. You can attach a medical certificate, valid ID, parent or guardian consent, barangay certificate, affidavit, transcript, or another supporting file. Use a PDF or a photo. You can add up to 10 files, and each file can be up to 5 MB. You can still submit if you have no file.'],
        ['group' => 'Requesting', 'question' => 'I did not get the code', 'keywords' => ['did not get', 'no code', 'spam', 'verification code'], 'answer' => 'Check your inbox and spam. The message comes from EVSU OCC LOA. The code lasts 10 minutes. If it expired, or the email was wrong, go back to Request LOA or Extend leave and send a new code.'],
        ['group' => 'Requesting', 'question' => 'My link expired. What now?', 'keywords' => ['expired', 'link expired', 'invalid link'], 'answer' => 'Go back to Request LOA or Extend leave and send a new verification code. That gives you a new form link. A form link stops working one day after it is emailed. The verification code lasts 10 minutes.'],
        ['group' => 'While it is reviewed', 'question' => 'Who approves a new leave?', 'keywords' => ['approve', 'approval', 'department head', 'saso', 'campus director', 'who approves'], 'answer' => 'A new leave goes to the Department Head, then SASO, then the Campus Director. You receive an email at each step. The President does not approve a new leave. Registrar and Guidance can view the record after approval. They do not approve or reject it.'],
        ['group' => 'While it is reviewed', 'question' => 'How do I check my status?', 'keywords' => ['check status', 'check my status', 'where is my application', 'track my leave'], 'answer' => 'On the home page, choose Check LOA Status. Enter the control number from your submission email and the student number on your form. Both must match. You do not sign in.'],
        ['group' => 'While it is reviewed', 'question' => 'Where is my control number?', 'keywords' => ['control number', 'where is my control', 'lost my number'], 'answer' => 'It appears on the confirmation page and in the email after you submit. After approval it is also on the official PDF. Check LOA Status on the home page uses that number and your student number.'],
        ['group' => 'While it is reviewed', 'question' => 'Can I change it after I submit?', 'keywords' => ['edit', 'change after', 'locked', 'already submitted'], 'answer' => 'After you submit, the form cannot be edited. Use Check LOA Status to see it. You can change it only if an office does not approve it. Then use the resubmit link in that email.'],
        ['group' => 'If it is not approved', 'question' => 'What if my leave is not approved?', 'keywords' => ['rejected', 'not approved', 'why was i rejected'], 'answer' => 'You get an email saying the leave was not approved, with the reason. The office that said no will look at it again if you resubmit. Offices that already said yes stay yes. Your control number stays the same, with a revision. Check LOA Status on the home page also shows that it was not approved.'],
        ['group' => 'If it is not approved', 'question' => 'How do I resubmit?', 'keywords' => ['resubmit', 'submit again', 'fix my application'], 'answer' => 'Open the email that says your leave was not approved. Click Resubmit My LOA Application. Change what they asked you to fix, then send it. The link lasts 7 days and works one time. Files you already sent stay. New files are added. If the link expired, contact your Department Head or SASO. Start a new Request LOA only if this is a different leave.'],
        ['group' => 'After it is approved', 'question' => 'What happens after approval?', 'keywords' => ['after approval', 'what happens after', 'official pdf', 'guidance'], 'answer' => 'You receive an email with the official PDF. Keep the control number. Registrar and Guidance update their office records. They do not approve the leave. Enrolling is done at the Registrar. LeaveFlow does not enroll you.'],
        ['group' => 'After it is approved', 'question' => 'How do I extend a leave?', 'keywords' => ['extend', 'extension', 'president', 'more time'], 'answer' => 'Choose Extend leave. Your student number and the same EVSU email must both match the approved leave. The President reviews an extension first, then the Department Head, SASO, and the Campus Director.'],
        ['group' => 'After it is approved', 'question' => 'Will I be reminded before my leave ends?', 'keywords' => ['remind', 'reminder', 'seven days', 'leave ends', 'before my leave', 'almost done', 'return date'], 'answer' => 'Seven days before the return date, you receive one email. Go to school that day and enroll at the Registrar, or use Extend leave before that date if you need more time. The email does not enroll you and does not extend the leave. A leave shorter than 7 days does not get this email.'],
        ['group' => 'After it is approved', 'question' => 'What if I come back or do not enroll?', 'keywords' => ['come back', 'returned', 'enroll', 'did not', 'overdue'], 'answer' => 'LeaveFlow does not see enrollment. After the return date, staff mark Returned if you came back, or Did not enroll if the office confirmed you did not enroll that term. Until one of those is recorded, the leave can show as overdue.'],
        ['group' => 'After it is approved', 'question' => 'What does discontinued mean?', 'keywords' => ['discontinue', 'discontinued', 'withdrawn'], 'answer' => 'Staff can mark a leave discontinued or withdrawn. You receive an email. You can confirm it on Check LOA Status with your control number and student number. Contact SASO or your Department Head if you think this was a mistake.'],
    ];
@endphp

@section('content')
<section class="hero-panel flex min-h-[calc(100vh-4.25rem)] items-center text-maroon-950">
    <div class="mx-auto grid w-full max-w-7xl gap-12 px-4 py-16 sm:px-8 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-10 lg:py-24">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.22em] text-maroon-700">EVSU Ormoc Campus</p>
            <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-maroon-900 sm:text-5xl lg:text-6xl">Leave of Absence, without the paper trail.</h1>
            <p class="mt-6 max-w-2xl text-lg leading-relaxed text-maroon-800/80 lg:text-xl">
                LeaveFlow is the campus LOA system for students who need an official leave. Request online,
                verify with your EVSU email, then track your application with a Control Number.
            </p>
            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                @guest
                    <a href="{{ route('student.identity') }}" class="inline-flex items-center justify-center rounded-xl bg-maroon-800 px-6 py-3.5 text-center text-base font-semibold text-cream-50 hover:bg-maroon-700">
                        Request LOA
                    </a>
                    <a href="{{ route('student.extend') }}" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                        Extend leave
                    </a>
                    <a href="{{ route('student.status') }}" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                        Check LOA Status
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                        Staff login
                    </a>
                @else
                    <a href="{{ route('staff.dashboard') }}" class="inline-flex items-center justify-center rounded-xl bg-maroon-800 px-6 py-3.5 text-center text-base font-semibold text-cream-50 hover:bg-maroon-700">
                        Go to Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline-flex">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-maroon-800/20 bg-white px-6 py-3.5 text-center text-base font-semibold text-maroon-900 hover:bg-cream-100">
                            Log out
                        </button>
                    </form>
                @endguest
            </div>
            @guest
                <p class="mt-5 text-sm text-maroon-800/70">Students do not create an account. Staff (Department Head, SASO, Campus Director, President, Registrar, Guidance) sign in here.</p>
            @else
                <p class="mt-5 text-sm text-maroon-800/70">Signed in as <strong>{{ auth()->user()->name }}</strong> &middot; {{ auth()->user()->role->label() }}.</p>
            @endguest
        </div>

        <div class="rounded-2xl border border-maroon-900/10 bg-white p-6 sm:p-8 lg:p-10">
            <p class="text-base font-semibold text-maroon-800">How a student request works</p>
            <ol class="mt-6 space-y-5 text-base leading-relaxed text-maroon-800/80 lg:text-lg">
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">1</span>
                    <span>For a new leave, enter your official EVSU email. To extend, student number and that same email must both match the approved leave.</span>
                </li>
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">2</span>
                    <span>We send a 6-digit verification code to that email, followed by your private form link.</span>
                </li>
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">3</span>
                    <span>Fill in your student information and leave details on EVSU-SASO-F-040. You receive a Control Number upon submission.</span>
                </li>
                <li class="flex gap-4">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-maroon-800 text-sm font-bold text-cream-50">4</span>
                    <span>Department Head, SASO, then Campus Director review a new leave. Extensions go to the President first, then the same three offices.</span>
                </li>
            </ol>
        </div>
    </div>
</section>

<section id="about" class="mx-auto max-w-7xl px-4 py-16 sm:px-8 lg:px-10 lg:py-24">
    <div class="max-w-3xl">
        <h2 class="text-3xl font-semibold text-maroon-900 lg:text-4xl">Built for EVSU Ormoc’s LOA process</h2>
        <p class="mt-4 text-base leading-relaxed text-maroon-800/80 lg:text-lg">
            The current paper route is slow when a student cannot come to campus. LeaveFlow keeps the same offices
            in sequence, stores supporting files, and logs every approval with name and date.
        </p>
    </div>

    <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['Public request', 'No student portal. Identity is confirmed through your official EVSU email link.'],
            ['Control Number', 'Each application gets an ID such as EVSU-OC-LOA-2026-0001 for tracking and the PDF.'],
            ['Multi-level review', 'New leave: Department Head → SASO → Campus Director. Extensions: President first, then the same three offices.'],
            ['Registrar & Guidance', 'After Campus Director approval, Registrar and Guidance update office records. They do not approve the leave.'],
            ['Return follow-up', 'Seven days before your return date, you receive an email. Go to school that day to enroll at the Registrar, or use Extend leave if you need more time. Staff later mark that the student returned or did not enroll.'],
            ['Staff workspace', 'Employees are invited by the System Administrator to access their office queue.'],
        ] as $card)
            <article class="rounded-2xl border border-maroon-900/10 bg-white p-6 lg:p-7">
                <h3 class="text-lg font-semibold text-maroon-900">{{ $card[0] }}</h3>
                <p class="mt-3 text-base leading-relaxed text-maroon-800/75">{{ $card[1] }}</p>
            </article>
        @endforeach
    </div>
</section>

@guest
<div class="fixed bottom-4 right-4 z-50 flex flex-col items-end gap-3">
    <section id="faq-chat" class="hidden w-[min(100vw-2rem,24rem)] overflow-hidden rounded-2xl border border-maroon-900/10 bg-white shadow-xl" aria-label="Student LOA help">
        <div class="bg-maroon-900 px-4 py-3 text-cream-50">
            <p class="text-sm font-semibold">Student help</p>
            <p class="text-xs text-cream-100/80">How a leave works, from request to return</p>
        </div>
        <div id="faq-chat-log" class="max-h-80 space-y-3 overflow-y-auto px-4 py-4 text-sm"></div>
        <div class="border-t border-maroon-900/10 px-4 py-3">
            <label for="faq-chat-list" class="mb-1 block text-xs font-semibold text-maroon-800">Choose a question</label>
            <select id="faq-chat-list" class="w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm outline-none ring-maroon-700 focus:ring-2">
                <option value="">Choose a question</option>
            </select>
        </div>
        <form id="faq-chat-form" class="flex gap-2 border-t border-maroon-900/10 px-4 py-3">
            <label for="faq-chat-input" class="sr-only">Ask a question</label>
            <input id="faq-chat-input" type="text" maxlength="200" placeholder="Ask about your leave" class="min-w-0 flex-1 rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-2 text-sm outline-none ring-maroon-700 focus:ring-2">
            <button type="submit" class="rounded-xl bg-maroon-800 px-3 py-2 text-sm font-semibold text-cream-50 hover:bg-maroon-700">Ask</button>
        </form>
    </section>
    <button type="button" id="faq-chat-toggle" class="rounded-full bg-maroon-800 px-5 py-3 text-sm font-semibold text-cream-50 shadow-lg hover:bg-maroon-700" aria-expanded="false" aria-controls="faq-chat">
        Ask about LOA
    </button>
</div>
<script type="application/json" id="faq-chat-data">@json($faq)</script>
<script src="{{ asset('js/faq-chat.js') }}"></script>
@endguest
@endsection
