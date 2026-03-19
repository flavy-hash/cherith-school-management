<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Payment Receipts (80mm) - Cherith Junior School</title>
    <style>
        @page { size: 80mm auto; margin: 4mm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #fff; }
        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }
        .receipt { width: 80mm; margin: 0 auto; padding: 0; }
        .center { text-align: center; }
        .title { font-size: 14px; font-weight: bold; }
        .sub { font-size: 11px; }
        .line { border-top: 1px dashed #000; margin: 6px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; font-size: 11px; }
        .small { font-size: 10px; }
        .qr { text-align: center; margin-top: 6px; }
        .qr-space { height: 44mm; display: flex; flex-direction: column; justify-content: center; }
        .logo { max-width: 28mm; max-height: 18mm; margin: 0 auto 4px; display: block; }
        .print-btn { display: block; width: 100%; margin: 10px auto 12px; padding: 8px 10px; background: #000; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <button class="print-btn" onclick="window.print()">Print All Receipts</button>

    @foreach($payments as $payment)
        @php($receipt = $receipts[$payment->id] ?? null)

        <div class="page">
            <div class="receipt">
                <div class="center">
                    @php($logoPath = public_path('receipt-logo.png'))
                    @if(file_exists($logoPath))
                        <img class="logo" src="{{ asset('receipt-logo.png') }}" alt="Logo">
                    @endif

                    <div class="title">{{ config('app.name', 'Cherith Junior School') }}</div>
                    @if(env('SCHOOL_ADDRESS'))
                        <div class="sub">{{ env('SCHOOL_ADDRESS') }}</div>
                    @endif
                    @if(env('SCHOOL_TIN') || env('SCHOOL_VAT'))
                        <div class="sub">
                            @if(env('SCHOOL_TIN')) TIN: {{ env('SCHOOL_TIN') }} @endif
                            @if(env('SCHOOL_TIN') && env('SCHOOL_VAT')) | @endif
                            @if(env('SCHOOL_VAT')) VAT: {{ env('SCHOOL_VAT') }} @endif
                        </div>
                    @endif
                    <div class="sub">Official Payment Receipt</div>
                    <div class="sub">Receipt #: {{ $payment->transaction_id ?? 'PAY-' . $payment->id }}</div>
                </div>

                <div class="line"></div>

                <div class="row"><span><strong>Date</strong></span><span>{{ $payment->payment_date->format('d M Y') }}</span></div>
                <div class="row"><span><strong>Student</strong></span><span>{{ $payment->student->full_name }}</span></div>
                <div class="row"><span><strong>Adm No</strong></span><span>{{ $payment->student->admission_number }}</span></div>
                <div class="row"><span><strong>Class</strong></span><span>{{ $payment->student->standard->name ?? 'N/A' }}</span></div>
                <div class="row"><span><strong>Term</strong></span><span>{{ ucfirst(str_replace('_', ' ', $payment->term)) }} {{ $payment->year }}</span></div>

                <div class="line"></div>

                <div class="row"><span><strong>Description</strong></span><span class="small">School Fee</span></div>
                <div class="row"><span><strong>Amount</strong></span><span><strong>TSH {{ number_format($payment->amount, 2) }}</strong></span></div>
                <div class="row"><span><strong>Method</strong></span><span>{{ ucfirst($payment->payment_method) }}</span></div>

                <div class="line"></div>

                <div class="small">
                    <div><strong>EFD</strong></div>
                    @if(is_array($receipt) && ! empty($receipt) && (($receipt['error'] ?? false) !== true))
                        <div class="small"><strong>Receipt No:</strong> {{ $receipt['receiptNumber'] ?? $receipt['receipt_number'] ?? 'N/A' }}</div>
                        <div class="small"><strong>Verif Code:</strong> {{ $receipt['verificationCode'] ?? $receipt['verification_code'] ?? 'N/A' }}</div>
                    @else
                        <div class="small"><strong>Receipt No:</strong> {{ (is_array($receipt) ? ($receipt['receiptNumber'] ?? $receipt['receipt_number'] ?? null) : null) ?? ($payment->transaction_id ?? 'PAY-' . $payment->id) }}</div>
                        <div class="small"><strong>Verif Code:</strong> {{ is_array($receipt) ? ($receipt['verificationCode'] ?? $receipt['verification_code'] ?? 'N/A') : 'N/A' }}</div>
                    @endif
                </div>

                @php($verificationUrl = is_array($receipt) ? ($receipt['qrCode'] ?? $receipt['verification_url'] ?? null) : null)

                <div class="qr">
                    <div class="qr-space">
                        @if($verificationUrl)
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)->generate($verificationUrl) !!}
                            <div class="small" style="word-break: break-all; margin-top: 4px;">{{ $verificationUrl }}</div>
                        @else
                            <div class="small">&nbsp;</div>
                        @endif
                    </div>
                </div>

                @if($payment->notes)
                    <div class="line"></div>
                    <div class="small"><strong>Notes:</strong> {{ $payment->notes }}</div>
                @endif

                <div class="line"></div>

                <div class="center small">
                    <div>Generated: {{ now()->format('d M Y H:i') }}</div>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
