<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class SendPaymentVerificationNotification
{
    public function handle(PaymentVerified $event): void
    {
        $payment = $event->payment;
        
        Notification::make()
            ->title('Payment Verified')
            ->body("Payment of TSH {$payment->amount} for {$payment->student->full_name} has been verified.")
            ->success()
            ->sendToDatabase(User::where('id', '!=', auth()->id())->get());
    }
}
