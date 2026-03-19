<?php

namespace App\Services;

use App\Models\Payment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;

class TraEfdService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://demo.smartefd.co.tz',
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Build the EFD request body from a Payment record.
     */
    protected function buildRequestBody(Payment $payment): array
    {
        $receiptNumber = $payment->transaction_id ?? 'PAY-' . $payment->id;

        return [
            'serial_number' => env('SMARTEFD_SERIAL_NUMBER', ''),
            'password' => env('SMARTEFD_PASSWORD', ''),
            'customer_name' => $payment->student->parent_name,
            'customer_tin' => '',
            'customer_vrn' => '',
            'customer_phone' => $payment->student->parent_phone,
            'products' => [
                [
                    'item' => "School Fee - {$payment->term} {$payment->year}",
                    'amount' => (int) round((float) $payment->amount),
                    'quantity' => 1,
                    'unit_price' => (int) round((float) $payment->amount),
                    'tax_group' => env('SMARTEFD_TAX_GROUP', 'C'),
                ],
            ],
            // Keep a reference (not part of SmartEFD schema, but harmless if ignored).
            'reference' => $receiptNumber,
        ];
    }

    /**
     * Map internal payment method to TRA EFD payment method codes.
     */
    protected function mapPaymentMethod(string $method): string
    {
        return match ($method) {
            'cash' => 'CASH',
            'bank_transfer' => 'BANK_TRANSFER',
            'mobile_money' => 'MOBILE_MONEY',
            default => 'CASH',
        };
    }

    /**
     * Fetch receipt from TRA EFD API.
     */
    public function getReceipt(Payment $payment): ?array
    {
        if (is_array($payment->tra_receipt_payload) && ! empty($payment->tra_receipt_payload)) {
            return $payment->tra_receipt_payload;
        }

        return $this->requestReceiptFromTra($payment);
    }

    /**
     * Request a receipt directly from TRA, bypassing any stored payload.
     */
    protected function requestReceiptFromTra(Payment $payment): ?array
    {
        // Mock mode for testing without real TRA API
        if (env('SMARTEFD_MOCK', env('TRA_EFD_MOCK', false))) {
            return [
                'error' => false,
                'verification_code' => 'VC' . strtoupper(Str::random(8)),
                'verification_url' => null,
                // Convenience keys for the rest of the app
                'receiptNumber' => $payment->transaction_id ?? 'PAY-' . $payment->id,
                'verificationCode' => 'VC' . strtoupper(Str::random(8)),
                'qrCode' => null,
            ];
        }

        $serialNumber = env('SMARTEFD_SERIAL_NUMBER');
        $password = env('SMARTEFD_PASSWORD');

        if (empty($serialNumber) || empty($password)) {
            \Log::error('SmartEFD credentials missing: SMARTEFD_SERIAL_NUMBER and/or SMARTEFD_PASSWORD');
            return [
                'error' => true,
                'code' => 'missing_credentials',
                'receiptNumber' => $payment->transaction_id ?? 'PAY-' . $payment->id,
            ];
        }

        try {
            $response = $this->client->post('/api/receipt_api', [
                'json' => $this->buildRequestBody($payment),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (is_array($data)) {
                if (isset($data['receipt_number']) && ! isset($data['receiptNumber'])) {
                    $data['receiptNumber'] = (string) $data['receipt_number'];
                }

                $data['receiptNumber'] ??= ($payment->transaction_id ?? 'PAY-' . $payment->id);

                if (isset($data['verification_code']) && ! isset($data['verificationCode'])) {
                    $data['verificationCode'] = $data['verification_code'];
                }
                if (isset($data['verification_url']) && ! isset($data['qrCode'])) {
                    $data['qrCode'] = $data['verification_url'];
                }
            }

            return $data;
        } catch (RequestException $e) {
            $receiptNumber = $payment->transaction_id ?? 'PAY-' . $payment->id;
            if ($e->hasResponse()) {
                $body = $e->getResponse()->getBody()->getContents();
                \Log::error('SmartEFD API Error: ' . $body);

                $decoded = json_decode($body, true);
                if (is_array($decoded)) {
                    $decoded['error'] ??= true;
                    $decoded['receiptNumber'] ??= $receiptNumber;
                    return $decoded;
                }

                return [
                    'error' => true,
                    'code' => 'http_error',
                    'message' => $body,
                    'receiptNumber' => $receiptNumber,
                ];
            } else {
                \Log::error('SmartEFD API Network Error: ' . $e->getMessage());

                return [
                    'error' => true,
                    'code' => 'network_error',
                    'message' => $e->getMessage(),
                    'receiptNumber' => $receiptNumber,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('SmartEFD Service Error: ' . $e->getMessage());

            return [
                'error' => true,
                'code' => 'service_error',
                'message' => $e->getMessage(),
                'receiptNumber' => $payment->transaction_id ?? 'PAY-' . $payment->id,
            ];
        }
    }

    /**
     * Fetch receipt from TRA and persist it on the Payment record.
     */
    public function syncReceipt(Payment $payment, bool $force = false): ?array
    {
        if (! $force && is_array($payment->tra_receipt_payload) && ! empty($payment->tra_receipt_payload)) {
            return $payment->tra_receipt_payload;
        }

        $receipt = $force
            ? $this->requestReceiptFromTra($payment)
            : $this->getReceipt($payment);

        if (! is_array($receipt) || empty($receipt)) {
            return null;
        }

        if (($receipt['error'] ?? false) === true) {
            \Log::warning('SmartEFD returned error receipt payload', [
                'payment_id' => $payment->id,
                'code' => $receipt['code'] ?? null,
                'payload' => $receipt,
            ]);

            return $receipt;
        }

        $payment->forceFill([
            'tra_receipt_number' => $receipt['receiptNumber'] ?? $receipt['receipt_number'] ?? $payment->tra_receipt_number,
            'tra_verification_code' => $receipt['verificationCode'] ?? $receipt['verification_code'] ?? $payment->tra_verification_code,
            'tra_qr_code' => $receipt['qrCode'] ?? $receipt['verification_url'] ?? $payment->tra_qr_code,
            'tra_receipt_payload' => $receipt,
            'tra_receipt_synced_at' => now(),
        ])->save();

        return $receipt;
    }

    /**
     * Generate a printable receipt URL (if TRA provides a PDF URL).
     */
    public function getReceiptPdfUrl(Payment $payment): ?string
    {
        $receipt = $this->getReceipt($payment);

        return $receipt['pdfUrl'] ?? null;
    }
}
