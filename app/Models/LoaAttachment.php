<?php

namespace App\Models;

use App\Enums\AttachmentCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoaAttachment extends Model
{
    protected $fillable = [
        'loa_request_id',
        'original_name',
        'category',
        'path',
        'mime_type',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'category' => AttachmentCategory::class,
        ];
    }

    public function loaRequest(): BelongsTo
    {
        return $this->belongsTo(LoaRequest::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}
