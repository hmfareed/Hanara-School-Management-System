<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Guardian extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'phone', 'email',
        'relationship', 'occupation', 'address', 'is_emergency_contact',
    ];

    protected $casts = [
        'is_emergency_contact' => 'boolean',
    ];

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function user(): MorphOne
    {
        return $this->morphOne(User::class, 'userable');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
