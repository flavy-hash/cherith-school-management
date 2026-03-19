<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Standard extends Model
{
    protected $fillable = [
        'name',
        'class_teacher',
        'term_one_fee',
        'term_two_fee',
        'expected_students',
    ];

    protected $casts = [
        'term_one_fee' => 'decimal:2',
        'term_two_fee' => 'decimal:2',
        'expected_students' => 'integer',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
