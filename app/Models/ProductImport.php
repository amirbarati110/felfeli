<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'filename', 'disk', 'path', 'status',
        'total_rows', 'created_count', 'updated_count', 'skipped_count', 'failed_count',
        'report',
    ];

    protected function casts(): array
    {
        return [
            'report' => 'array',
            'total_rows' => 'integer',
            'created_count' => 'integer',
            'updated_count' => 'integer',
            'skipped_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
