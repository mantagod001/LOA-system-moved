@props(['categories'])

<div class="space-y-3">
    <p class="text-sm font-medium text-maroon-900">Supporting files <span class="font-normal text-maroon-800/50">(optional)</span></p>
    <p class="text-xs text-maroon-800/70">Choose the document type, then attach the file. PDF or image (JPG, PNG, WEBP) — up to 10 files, 5 MB each.</p>

    <div class="space-y-3" data-attachment-rows>
        <div class="grid gap-3 rounded-xl border border-maroon-900/10 bg-cream-50/60 p-3 sm:grid-cols-[1fr_1fr_auto]" data-attachment-row>
            <div>
                <label class="block text-xs font-medium text-maroon-900">Document type</label>
                <select name="attachment_categories[]" class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-white px-3 py-2.5 text-sm outline-none ring-maroon-700 focus:ring-2">
                    <option value="">Select type…</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->value }}">{{ $category->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-maroon-900">File</label>
                <input name="attachments[]" type="file"
                       accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                       class="mt-1 w-full rounded-xl border border-maroon-900/15 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-maroon-800 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-cream-50">
            </div>
            <div class="flex items-end">
                <button type="button" data-remove-attachment-row class="hidden rounded-xl border border-maroon-900/15 px-3 py-2.5 text-xs font-semibold text-maroon-700 hover:bg-white">Remove</button>
            </div>
        </div>
    </div>

    <button type="button" data-add-attachment-row class="rounded-xl border border-maroon-800/20 bg-white px-4 py-2 text-sm font-semibold text-maroon-800 hover:bg-cream-100">
        + Add another file
    </button>
    @error('attachments') <p class="text-sm text-maroon-600">{{ $message }}</p> @enderror
    @error('attachments.*') <p class="text-sm text-maroon-600">{{ $message }}</p> @enderror
    @error('attachment_categories.*') <p class="text-sm text-maroon-600">{{ $message }}</p> @enderror
</div>
