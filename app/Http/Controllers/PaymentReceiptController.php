<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\TraEfdService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentReceiptController extends Controller
{
    public function __construct(
        protected TraEfdService $traEfdService,
    ) {}

    public function show(Payment $payment): View
    {
        $format = request()->query('format', 'a4');
        $force = false;

        if (is_array($payment->tra_receipt_payload) && ! empty($payment->tra_receipt_payload)) {
            $payload = $payment->tra_receipt_payload;
            $force = ! isset($payload['verification_code'])
                && ! isset($payload['verificationCode'])
                && ! isset($payload['verification_url'])
                && ! isset($payload['qrCode']);
        }

        $receipt = $this->traEfdService->syncReceipt($payment, $force);

        $view = $format === '80mm'
            ? 'filament.payments.receipt-80mm'
            : 'filament.payments.receipt';

        return view($view, [
            'payment' => $payment,
            'receipt' => $receipt,
        ]);
    }

    public function bulk(Request $request): View
    {
        $format = $request->query('format', 'a4');
        $ids = collect(explode(',', (string) $request->query('ids', '')))
            ->map(fn ($id) => trim($id))
            ->filter(fn ($id) => $id !== '')
            ->map(fn ($id) => (int) $id)
            ->values();

        $paymentsQuery = Payment::query()
            ->with(['student.standard']);

        if ($ids->isNotEmpty()) {
            $paymentsQuery->whereIn('id', $ids);
        } else {
            $paymentsQuery
                ->when($request->query('student_id'), fn (Builder $q, $studentId) => $q->where('student_id', $studentId))
                ->when($request->query('term'), fn (Builder $q, $term) => $q->where('term', $term))
                ->when($request->query('year'), fn (Builder $q, $year) => $q->where('year', $year))
                ->when($request->query('from'), fn (Builder $q, $from) => $q->whereDate('payment_date', '>=', $from))
                ->when($request->query('to'), fn (Builder $q, $to) => $q->whereDate('payment_date', '<=', $to));
        }

        $payments = $paymentsQuery->orderBy('payment_date')->get();

        $receipts = [];
        foreach ($payments as $payment) {
            $receipts[$payment->id] = $this->traEfdService->syncReceipt($payment, true);
        }

        $view = $format === '80mm'
            ? 'filament.payments.bulk-receipts-80mm'
            : 'filament.payments.bulk-receipts';

        return view($view, [
            'payments' => $payments,
            'receipts' => $receipts,
        ]);
    }
}
