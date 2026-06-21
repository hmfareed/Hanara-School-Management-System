<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffCode extends Model
{
    use HasFactory;

    protected $table = 'staff_codes';

    protected $fillable = [
        'code',
        'is_used',
        'used_by_user_id',
    ];

    protected $casts = [
        'is_used' => 'boolean',
    ];

    /**
     * Get the user who used this code.
     */
    public function usedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by_user_id');
    }
}
