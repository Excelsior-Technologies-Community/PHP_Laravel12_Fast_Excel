<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileHistory extends Model
{
    protected $fillable = [
        'file_name',
        'type',
        'status',
        'batch_id',
        'total_rows',
        'current_row',
        'success_rows',
        'failed_rows',
        'errors',
        'failed_file_path',
        'upsert_mode'
    ];

    protected $casts = [
        'errors' => 'array',
        'total_rows' => 'integer',
        'current_row' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer'
    ];

    /**
     * Get completion percentage
     */
    public function getPercentageAttribute(): int
    {
        if ($this->total_rows <= 0) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $percentage = (int) round(($this->current_row / $this->total_rows) * 100);
        return min($percentage, 100);
    }
}
