<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Cherith Junior School</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .receipt-container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
        .school-name { font-size: 28px; font-weight: bold; margin-bottom: 5px; }
        .receipt-title { font-size: 20px; margin-bottom: 10px; }
        .receipt-details { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .detail-block h3 { margin: 0 0 10px 0; font-size: 16px; color: #555; }
        .detail-block p { margin: 5px 0; font-size: 14px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        .items-table th { background: #f8f9fa; font-weight: bold; }
        .total-row { font-weight: bold; background: #f8f9fa; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
        .tra-info { margin-top: 20px; padding: 15px; background: #e9ecef; border-radius: 4px; }
        .tra-info h4 { margin: 0 0 10px 0; }
        .tra-info p { margin: 5px 0; font-size: 13px; }
        .no-receipt { text-align: center; color: #dc3545; font-weight: bold; margin: 40px 0; }
        .print-btn { display: block; width: 150px; margin: 20px auto; padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .print-btn:hover { background: #0056b3; }
        @media print { .print-btn { display: none; } body { background: white; } .receipt-container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <div class="school-name">Cherith Junior School</div>
            <div class="receipt-title">Official Payment Receipt</div>
            <div>Receipt #: {{ $payment->transaction_id ?? 'PAY-' . $payment->id }}</div>
        </div>

        @if($receipt)
            <div class="receipt-details">
                <div class="detail-block">
                    <h3>Payment Details</h3>
                    <p><strong>Date:</strong> {{ $payment->payment_date->format('d M Y') }}</p>
                    <p><strong>Amount:</strong> TSH {{ number_format($payment->amount, 2) }}</p>
                    <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
                    <p><strong>Term:</strong> {{ ucfirst(str_replace('_', ' ', $payment->term)) }} {{ $payment->year }}</p>
                </div>
                <div class="detail-block">
                    <h3>Student Details</h3>
                    <p><strong>Name:</strong> {{ $payment->student->full_name }}</p>
                    <p><strong>Admission No:</strong> {{ $payment->student->admission_number }}</p>
                    <p><strong>Class:</strong> {{ $payment->student->standard->name ?? 'N/A' }}</p>
                    <p><strong>Parent/Guardian:</strong> {{ $payment->student->parent_name }}</p>
                </div>
            </div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>School Fee - {{ ucfirst($payment->term) }} {{ $payment->year }}</td>
                        <td>1</td>
                        <td>TSH {{ number_format($payment->amount, 2) }}</td>
                        <td>TSH {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" style="text-align: right;"><strong>Total Paid:</strong></td>
                        <td><strong>TSH {{ number_format($payment->amount, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <div class="tra-info">
                <h4>EFD Information</h4>
                <p><strong>Receipt Number:</strong> {{ $receipt['receiptNumber'] ?? $receipt['receipt_number'] ?? 'N/A' }}</p>
                <p><strong>Verification Code:</strong> {{ $receipt['verificationCode'] ?? $receipt['verification_code'] ?? 'N/A' }}</p>

                @php($verificationUrl = $receipt['qrCode'] ?? $receipt['verification_url'] ?? null)

                @if($verificationUrl)
                    <div style="text-align: center; margin-top: 10px;">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate($verificationUrl) !!}
                        <div style="margin-top: 8px; font-size: 12px; word-break: break-all;">
                            <a href="{{ $verificationUrl }}" target="_blank">{{ $verificationUrl }}</a>
                        </div>
                    </div>
                @endif
            </div>

            @if($payment->notes)
                <div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff;">
                    <strong>Notes:</strong> {{ $payment->notes }}
                </div>
            @endif
        @else
            <div class="no-receipt">
                Unable to fetch TRA EFD receipt. Please check your internet connection or contact support.
            </div>
        @endif

        <div class="footer">
            <p>This receipt was generated automatically by Cherith Junior School Management System.</p>
            <p>For verification, please visit the TRA website or scan the QR code above.</p>
            <p>Generated on: {{ now()->format('d M Y H:i:s') }}</p>
        </div>
    </div>

    <button class="print-btn" onclick="window.print()">Print Receipt</button>
</body>
</html>
