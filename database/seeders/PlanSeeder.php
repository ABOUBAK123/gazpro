<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\SubscriptionSetting;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $settings = SubscriptionSetting::query()->first();

        $monthlyPrice = $settings->monthly_price ?? 5000;
        $yearlyPrice  = $settings->yearly_price ?? 50000;
        $currency     = $settings->currency ?? 'XOF';

        Plan::firstOrCreate(
            ['slug' => 'essai-gratuit'],
            [
                'name'          => 'Essai gratuit',
                'price'         => 0,
                'currency'      => $currency,
                'duration_days' => 7,
                'is_active'     => true,
                'sort_order'    => 0,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'mensuel'],
            [
                'name'          => 'Mensuel',
                'price'         => $monthlyPrice,
                'currency'      => $currency,
                'duration_days' => 30,
                'is_active'     => true,
                'sort_order'    => 1,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'annuel'],
            [
                'name'          => 'Annuel',
                'price'         => $yearlyPrice,
                'currency'      => $currency,
                'duration_days' => 365,
                'is_active'     => true,
                'sort_order'    => 2,
            ]
        );
    }
}
