<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Store;
use App\Models\Staff;
use App\Models\Stock;
use App\Models\Client;
use App\Models\GlobalCurrency;
use App\Models\LoyaltyProgram;
use App\Models\SubscriptionSetting;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        Admin::create([
            'name'     => 'Administrateur',
            'email'    => 'admin@gazmanager.com',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        // Global currencies
        GlobalCurrency::create(['name' => 'Franc CFA', 'code' => 'XOF', 'symbol' => 'CFA', 'rate' => 1.0, 'is_default' => true]);
        GlobalCurrency::create(['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'rate' => 655.957]);
        GlobalCurrency::create(['name' => 'Dollar US', 'code' => 'USD', 'symbol' => '$', 'rate' => 598.5]);

        // Subscription settings
        SubscriptionSetting::create([
            'monthly_price'    => 5000,
            'yearly_price'     => 50000,
            'currency'         => 'XOF',
            'mobile_providers' => ['Orange Money', 'Moov Money'],
        ]);

        // Demo store
        $store = Store::create([
            'store_name'          => 'Magasin Total Demo',
            'owner_name'          => 'Jean Konan',
            'email'               => 'manager@test.com',
            'phone'               => '+225 07 12 34 56',
            'password'            => Hash::make('manager123'),
            'address'             => 'Abidjan, Cocody',
            'status'              => 'active',
            'subscription_status' => 'active',
            'subscription_expiry' => Carbon::now()->addYear(),
        ]);

        // Staff for demo store
        Staff::create([
            'store_id' => $store->id,
            'name'     => 'Marie Koné',
            'email'    => 'employee@test.com',
            'phone'    => '+225 05 00 00 01',
            'password' => Hash::make('employee123'),
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        // Stock for demo store
        $products = [
            ['brand' => 'Total', 'weight' => '6kg',  'quantity' => 25, 'unit_price' => 3500,  'alert_threshold' => 5],
            ['brand' => 'Total', 'weight' => '12kg', 'quantity' => 15, 'unit_price' => 6500,  'alert_threshold' => 3],
            ['brand' => 'Total', 'weight' => '25kg', 'quantity' => 8,  'unit_price' => 13000, 'alert_threshold' => 2],
            ['brand' => 'Shell', 'weight' => '6kg',  'quantity' => 3,  'unit_price' => 3400,  'alert_threshold' => 5],
            ['brand' => 'Shell', 'weight' => '12kg', 'quantity' => 0,  'unit_price' => 6400,  'alert_threshold' => 3],
            ['brand' => 'Oryx',  'weight' => '6kg',  'quantity' => 12, 'unit_price' => 3300,  'alert_threshold' => 5],
        ];

        foreach ($products as $product) {
            $store->stock()->create($product);
        }

        // Clients for demo store
        $clients = [
            ['name' => 'Aminata Diallo', 'phone' => '+225 07 11 11 11', 'loyalty_points' => 15, 'total_orders' => 8],
            ['name' => 'Kouassi Bamba',  'phone' => '+225 05 22 22 22', 'loyalty_points' => 42, 'total_orders' => 20],
            ['name' => 'Fatou Traoré',   'phone' => '+225 01 33 33 33', 'loyalty_points' => 8,  'total_orders' => 4],
        ];

        foreach ($clients as $clientData) {
            $store->clients()->create($clientData);
        }

        // Loyalty program
        LoyaltyProgram::create([
            'store_id'         => $store->id,
            'points_per_unit'  => 1,
            'reward_threshold' => 100,
            'reward_value'     => 1000,
            'currency'         => 'XOF',
            'active'           => true,
        ]);

        $this->command->info('Base de données initialisée !');
        $this->command->info('Admin:    admin@gazmanager.com / admin123');
        $this->command->info('Manager:  manager@test.com / manager123');
        $this->command->info('Caissier: employee@test.com / employee123');
    }
}
