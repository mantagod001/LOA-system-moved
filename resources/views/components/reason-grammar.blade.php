@props([
    'label' => 'Reason for leave',
    'value' => '',
    'url',
])

<div data-grammar-check data-grammar-url="{{ $url }}">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <label for="reason" class="block text-sm font-medium text-maroon-900">{{ $label }}</label>
        <button type="button" data-grammar-run
            class="rounded-lg border border-maroon-900/20 bg-white px-3 py-1.5 text-xs font-semibold text-maroon-800 hover:bg-maroon-50">
            Check grammar
        </button>
    </div>
    <p class="mt-1 text-xs text-maroon-800/60">Check grammar is optional. Your text is not changed until you accept.</p>
    <button type="button" data-reason-examples-toggle
        class="mt-1 text-xs font-semibold text-maroon-800 underline decoration-maroon-800/30 underline-offset-2 hover:text-maroon-950">
        Need an example?
    </button>
    <div class="mt-2 hidden space-y-2 rounded-xl border border-maroon-900/10 bg-cream-50 px-3 py-3" data-reason-examples>
        <p class="text-xs text-maroon-800/70">Tap one to start. You can edit it after.</p>
        <button type="button" data-reason-example
            class="block w-full rounded-lg border border-maroon-900/10 bg-white px-3 py-2 text-left text-sm text-maroon-900 hover:bg-maroon-50"
            data-reason-text="I need to take a leave of absence for medical treatment and recovery. I will return when I am cleared to continue my studies.">Medical</button>
        <button type="button" data-reason-example
            class="block w-full rounded-lg border border-maroon-900/10 bg-white px-3 py-2 text-left text-sm text-maroon-900 hover:bg-maroon-50"
            data-reason-text="I need to take a leave of absence to assist my family with an urgent personal matter. I will return as soon as I can continue my studies.">Family</button>
        <button type="button" data-reason-example
            class="block w-full rounded-lg border border-maroon-900/10 bg-white px-3 py-2 text-left text-sm text-maroon-900 hover:bg-maroon-50"
            data-reason-text="I need to take a leave of absence to work and support my education. I will return in the school year and semester listed on this form.">Work</button>
    </div>
    <textarea id="reason" name="reason" rows="5" required data-grammar-source
        class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-cream-50 px-3 py-3 text-base outline-none ring-maroon-700 focus:ring-2">{{ $value }}</textarea>
    @error('reason') <p class="mt-1 text-sm text-maroon-600">{{ $message }}</p> @enderror

    <p class="mt-2 hidden text-sm text-maroon-700" data-grammar-status></p>

    <div class="mt-3 hidden space-y-3 rounded-xl border border-maroon-900/10 bg-white p-4" data-grammar-preview>
        <p class="text-xs font-semibold uppercase tracking-wider text-maroon-600">Suggested wording</p>
        <p class="whitespace-pre-wrap text-sm text-maroon-900" data-grammar-suggested></p>
        <div class="flex flex-wrap gap-2">
            <button type="button" data-grammar-accept
                class="rounded-lg bg-maroon-800 px-3 py-1.5 text-xs font-semibold text-cream-50 hover:bg-maroon-700">
                Use this version
            </button>
            <button type="button" data-grammar-keep
                class="rounded-lg border border-maroon-900/20 bg-white px-3 py-1.5 text-xs font-semibold text-maroon-800 hover:bg-maroon-50">
                Keep mine
            </button>
        </div>
    </div>
</div>
