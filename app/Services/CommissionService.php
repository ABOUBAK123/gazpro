<?php

namespace App\Services;

use App\Models\CommissionTransaction;
use App\Models\Commissionnaire;
use App\Models\Payment;
use App\Models\Store;

class CommissionService
{
    private const RATE = 0.03;

    public function credit(Store $store, Payment $payment): void
    {
        if (!$store->commissionnaire_id) {
            return;
        }

        $commissionnaire = Commissionnaire::find($store->commissionnaire_id);
        if (!$commissionnaire || !$commissionnaire->isActive()) {
            return;
        }

        $amount = round($payment->amount * self::RATE, 2);

        CommissionTransaction::create([
            'commissionnaire_id' => $commissionnaire->id,
            'type'               => 'credit',
            'amount'             => $amount,
            'status'             => 'completed',
            'store_id'           => $store->id,
            'payment_id'         => $payment->id,
            'reference'          => (string) $payment->id,
        ]);

        $commissionnaire->increment('balance', $amount);
        $payment->update(['commissionnaire_id' => $commissionnaire->id]);
    }
}
