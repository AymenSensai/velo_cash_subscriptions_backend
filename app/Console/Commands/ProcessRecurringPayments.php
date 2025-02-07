<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessRecurringPayments extends Command
{
    protected $signature = 'payments:process-recurring';
    protected $description = 'Process recurring payments for all customers on the 16th of each month';

    public function handle()
    {
        $customers = Customer::whereHas('subscriptions', function ($query) {
            $query->where('is_paused', false);
        })->get();

        foreach ($customers as $customer) {
            $totalAmount = $customer->subscriptions->where('pivot.is_paused', false)->sum('price');

            if ($totalAmount > 0 && !empty($customer->authorization_code)) {
                // Process payment
                $paymentResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . env('LAHZA_SECRET_KEY'),
                    'Content-Type' => 'application/json',
                ])->post('https://api.lahza.io/transaction/charge_authorization', [
                    'authorization_code' => $customer->authorization_code,
                    'email' => $customer->email,
                    'amount' => $totalAmount * 100,
                    'currency' => 'ILS',
                ]);

                if ($paymentResponse->successful()) {
                    $customer->update(['payed_subscriptions' => true]);

                    Log::info("Payment successful for customer ID: {$customer->id}");

                    // Send WhatsApp message
                    $this->sendWhatsAppMessage($customer, $totalAmount);
                } else {
                    $customer->update(['payed_subscriptions' => false]);

                    Log::error("Payment failed for customer ID: {$customer->id}", [
                        'response' => $paymentResponse->body(),
                    ]);
                }
            }
        }

        $this->info('Recurring payments processed successfully.');
    }

    private function sendWhatsAppMessage(Customer $customer, $amount)
    {
        $message = "Dear {$customer->name}, your payment of {$amount} ILS has been processed successfully. Thank you!";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://velocash.site/api/create-message', [
            'appkey' => env('VELOCASH_APP_KEY'),
            'authkey' => env('VELOCASH_AUTH_KEY'),
            'to' => $customer->phone_number,
            'message' => $message,
        ]);

        if ($response->successful()) {
            Log::info("WhatsApp message sent to {$customer->phone_number}");
        } else {
            Log::error("Failed to send WhatsApp message to {$customer->phone_number}", [
                'response' => $response->body(),
            ]);
        }
    }
}
