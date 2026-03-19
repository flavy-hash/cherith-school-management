<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Student extends Model
{
    protected $fillable = [
        'standard_id', 'admission_number', 'first_name', 'last_name',
        'gender', 'date_of_birth', 'parent_name', 'parent_phone',
        'parent_email', 'address', 'status'
    ];

    protected static function booted(): void
    {
        static::saving(function (self $student): void {
            $shouldRegenerate = ! $student->admission_number
                || $student->isDirty('standard_id')
                || $student->isDirty('first_name')
                || $student->isDirty('last_name');

            if (! $shouldRegenerate) {
                return;
            }

            if (! $student->standard_id || ! $student->first_name || ! $student->last_name) {
                return;
            }

            $student->admission_number = static::generateAdmissionNumber(
                standardId: (int) $student->standard_id,
                firstName: (string) $student->first_name,
                lastName: (string) $student->last_name,
                ignoreStudentId: $student->id,
            );
        });
    }

    public static function generateAdmissionNumber(int $standardId, string $firstName, string $lastName, ?int $ignoreStudentId = null): string
    {
        $standard = Standard::find($standardId);
        $standardName = $standard?->name ?? ('STD' . $standardId);

        $standardCode = Str::upper((string) $standardName);
        $standardCode = str_replace('STANDARD', 'STD', $standardCode);
        $standardCode = preg_replace('/\s+/', '', $standardCode) ?: $standardCode;

        $initials = Str::upper(
            Str::substr(trim($firstName), 0, 1) . Str::substr(trim($lastName), 0, 1)
        );

        $prefix = $standardCode . '-' . $initials;

        $firstInitial = Str::upper(Str::substr(trim($firstName), 0, 1));
        $alphaIndex = 1;
        if ($firstInitial !== '' && preg_match('/^[A-Z]$/', $firstInitial) === 1) {
            $alphaIndex = (ord($firstInitial) - ord('A')) + 1;
        }

        $sequencePrefix = $standardCode . '-' . $firstInitial;

        $query = static::query()
            ->where('standard_id', $standardId)
            ->where('admission_number', 'like', $sequencePrefix . '%');

        if ($ignoreStudentId) {
            $query->where('id', '!=', $ignoreStudentId);
        }

        $latest = $query->orderByDesc('admission_number')->value('admission_number');

        $next = $alphaIndex;
        if (is_string($latest) && preg_match('/(\d{3})$/', $latest, $m)) {
            $next = max($alphaIndex, (int) $m[1] + 1);
        }

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getTermOneBalanceAttribute(): float
    {
        $termOneFee = $this->standard->term_one_fee;
        $paid = $this->payments()
            ->where('term', 'term_one')
            ->where('year', date('Y'))
            ->sum('amount');
        
        return $termOneFee - $paid;
    }

    public function getTermTwoBalanceAttribute(): float
    {
        $termTwoFee = $this->standard->term_two_fee;
        $paid = $this->payments()
            ->where('term', 'term_two')
            ->where('year', date('Y'))
            ->sum('amount');
        
        return $termTwoFee - $paid;
    }

    public function getTotalDebtAttribute(): float
    {
        return max(0, $this->term_one_balance) + max(0, $this->term_two_balance);
    }
}
