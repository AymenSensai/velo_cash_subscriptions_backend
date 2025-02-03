<?php

// app/Jobs/ChargeSubscribedCustomers.php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class ChargeSubscribedCustomers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        // Get users whose billing_day is today and have a customer with a subscription
        $users = User::whereDay('billing_day', now()->day)
            ->whereHas('customer', function ($query) {
                $query->whereNotNull('authorization_code')
                      ->whereHas('subscription');
            })
            ->with(['customer.subscription'])
            ->get();

        foreach ($users as $user) {
            // Charge via Lahza API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.lahza.secret_key'),
                'Content-Type' => 'application/json',
            ])->post('https://api.lahza.io/transaction/charge_authorization', [
                'authorization_code' => $user->customer->authorization_code,
                'email' => $user->customer->email, // Use customer's email
                'amount' => $user->customer->subscription->price * 100, // Convert to kobo
            ]);

            // Log results
            if ($response->successful()) {
                \Log::info("Recurring payment successful for Customer {$user->customer->id}");
            } else {
                \Log::error("Recurring payment failed for Customer {$user->customer->id}", $response->json());
            }
        }
    }
}
