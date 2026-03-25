<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSubject extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'standard_id',
        'term',
        'year',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }
}
