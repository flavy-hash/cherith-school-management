<?php

namespace App\Imports;

use App\Models\Payment;
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

class PaymentsImport implements OnEachRow, WithHeadingRow, SkipsEmptyRows
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

        $data = $this->normalizeStudentAdmissionNumber($data);
        $data = $this->normalizePaymentMethod($data);

        $validator = Validator::make($data, [
            'student_admission_number' => ['required', 'string', 'exists:students,admission_number'],
            'term' => ['required', 'in:term_one,term_two'],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:bank_transfer,mobile_money'],
            'payment_date' => ['required'],
            'transaction_id' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_verified' => ['nullable', 'in:0,1'],
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

        $student = Student::where('admission_number', $payload['student_admission_number'])->first();
        if (! $student) {
            $this->skipped++;
            $this->rowErrors[] = [
                'row' => $rowIndex,
                'errors' => ['Student not found for admission number: ' . $payload['student_admission_number']],
            ];
            return;
        }

        $paymentDate = $this->parseDate($payload['payment_date']);
        $transactionId = $payload['transaction_id'] ?? null;

        try {
            if ($transactionId !== null && $transactionId !== '') {
                $existing = Payment::where('transaction_id', $transactionId)->first();

                if ($existing) {
                    $existing->fill([
                        'student_id' => $student->id,
                        'term' => $payload['term'],
                        'year' => (int) $payload['year'],
                        'amount' => (float) $payload['amount'],
                        'payment_method' => $payload['payment_method'],
                        'payment_date' => $paymentDate,
                        'notes' => $payload['notes'] ?? null,
                        'is_verified' => (bool) ((int) ($payload['is_verified'] ?? 0)),
                    ])->save();

                    $this->updated++;
                    return;
                }
            }

            Payment::create([
                'student_id' => $student->id,
                'term' => $payload['term'],
                'year' => (int) $payload['year'],
                'amount' => (float) $payload['amount'],
                'balance' => $student->total_debt,
                'payment_method' => $payload['payment_method'],
                'transaction_id' => $transactionId ?: null,
                'notes' => $payload['notes'] ?? null,
                'is_verified' => (bool) ((int) ($payload['is_verified'] ?? 0)),
                'verified_at' => ((int) ($payload['is_verified'] ?? 0)) === 1 ? now() : null,
                'verified_by' => ((int) ($payload['is_verified'] ?? 0)) === 1 ? auth()->id() : null,
                'payment_date' => $paymentDate,
            ]);

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

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeStudentAdmissionNumber(array $data): array
    {
        $candidateKeys = [
            'student_admission_number',
            'admission_number',
            'student_admission_no',
            'admission_no',
            'adm_no',
            'adm',
        ];

        if (! array_key_exists('student_admission_number', $data) || $data['student_admission_number'] === null || $data['student_admission_number'] === '') {
            foreach ($candidateKeys as $key) {
                if (! array_key_exists($key, $data)) {
                    continue;
                }

                $value = $data[$key];
                if (is_string($value)) {
                    $value = trim($value);
                }

                if ($value !== null && $value !== '') {
                    $data['student_admission_number'] = $value;
                    break;
                }
            }
        } elseif (is_string($data['student_admission_number'])) {
            $data['student_admission_number'] = trim($data['student_admission_number']);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizePaymentMethod(array $data): array
    {
        if (! array_key_exists('payment_method', $data)) {
            return $data;
        }

        $value = $data['payment_method'];
        if (! is_string($value)) {
            return $data;
        }

        $normalized = strtolower(trim($value));
        $normalized = str_replace([' ', '-', '/'], '_', $normalized);

        $data['payment_method'] = match ($normalized) {
            'crdb' => 'bank_transfer',
            'nmb' => 'mobile_money',
            default => $data['payment_method'],
        };

        return $data;
    }
}
