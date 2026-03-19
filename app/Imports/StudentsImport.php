<?php

namespace App\Imports;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class StudentsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    /**
     * @var array<int, array{row:int, errors:array<int,string>}>
     */
    public array $rowErrors = [];

    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $data = $row->toArray();

        if (array_key_exists('gender', $data)) {
            $data['gender'] = $this->normalizeGender($data['gender']);
        }

        $validator = Validator::make($data, [
            'admission_number' => ['nullable', 'string'],
            'standard_id' => ['required', 'integer', 'exists:standards,id'],
            'first_name' => ['required', 'string'],
            'last_name' => ['required', 'string'],
            'gender' => ['required', 'in:male,female'],
            'date_of_birth' => ['required'],
            'parent_name' => ['required', 'string'],
            'parent_phone' => ['required', 'string'],
            'address' => ['required', 'string'],
            'parent_email' => ['nullable', 'email'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        if ($validator->fails()) {
            $this->skipped++;
            $this->rowErrors[] = [
                'row' => $rowIndex,
                'errors' => Arr::flatten($validator->errors()->all()),
            ];
            return;
        }

        $payload = $validator->validated();
        $payload['date_of_birth'] = $this->parseDate($payload['date_of_birth']);
        $payload['status'] = $payload['status'] ?? 'active';

        try {
            $student = null;

            if (! empty($payload['admission_number'])) {
                $student = Student::where('admission_number', $payload['admission_number'])->first();
            }

            if (! $student) {
                $student = Student::query()
                    ->where('standard_id', $payload['standard_id'])
                    ->where('first_name', $payload['first_name'])
                    ->where('last_name', $payload['last_name'])
                    ->where('date_of_birth', $payload['date_of_birth'])
                    ->first();
            }

            if ($student) {
                $student->fill($payload)->save();
                $this->updated++;
                return;
            }

            Student::create($payload);
            $this->created++;
        } catch (\Throwable $e) {
            $this->skipped++;
            $this->rowErrors[] = [
                'row' => $rowIndex,
                'errors' => [$e->getMessage()],
            ];
        }
    }

    private function parseDate(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }

        return Carbon::parse((string) $value)->toDateString();
    }

    private function normalizeGender(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $normalized = strtolower(trim($value));
        $normalized = preg_replace('/\s+/', '', $normalized) ?? $normalized;

        return match ($normalized) {
            'm', 'male', 'man', 'boy' => 'male',
            'f', 'female', 'woman', 'girl' => 'female',
            default => $value,
        };
    }
}
