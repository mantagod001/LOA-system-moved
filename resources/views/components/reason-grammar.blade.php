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
    <p class="mt-1 text-xs text-maroon-800/60">Optional. Fixes spelling and grammar only. Your text is not changed until you accept.</p>
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
