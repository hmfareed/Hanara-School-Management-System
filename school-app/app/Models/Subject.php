<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'level', 'is_elective'];

    protected $casts = [
        'is_elective' => 'boolean',
    ];
}
