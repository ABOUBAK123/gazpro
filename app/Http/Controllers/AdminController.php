<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Store;
use App\Models\GlobalCurrency;
use App\Models\SubscriptionSetting;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\AppSetting;
use App\Services\StoreQrService;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_stores'    => Store::count(),
            'active_stores'   => Store::where('status', 'active')->count(),
            'pending_stores'  => Store::where('status', 'pending')->count(),
            'rejected_stores' => Store::where('status', 'rejected')->count(),
            'total_revenue'   => Payment::where('status', 'completed')->sum('amount'),
        ];

        $recent_stores = Store::latest()->take(5)->get();
        $pending_stores = Store::where('status', 'pending')->latest()->get();

        return view('admin.dashboard', compact('stats', 'recent_stores', 'pending_stores'));
    }

    public function inscriptions()
    {
        $pending = Store::where('status', 'pending')->latest()->get();
        $active  = Store::where('status', 'active')->latest()->get();
        $rejected = Store::where('status', 'rejected')->latest()->get();

        return view('admin.inscriptions', compact('pending', 'active', 'rejected'));
    }

    public function approveStore(Store $store, StoreQrService $qrService)
    {
        if (!$store->qr_token) {
            $store->qr_token = (string) Str::uuid();
        }
        $store->status = 'active';

        // First-time approval: grant the free trial plan automatically so the
        // store can start using the platform right away without paying.
        if (!$store->plan_id) {
            $trial = Plan::where('slug', 'essai-gratuit')->where('is_active', true)->first();
            if ($trial) {
                $store->plan_id             = $trial->id;
                $store->subscription_status = 'active';
                $store->subscription_expiry = now()->addDays($trial->duration_days);
            }
        }

        $store->save();

        $qrService->generate($store);

        return back()->with('success', "Le magasin \"{$store->store_name}\" a été approuvé.");
    }

    public function generateStoreQr(Store $store, StoreQrService $qrService)
    {
        if (!$store->qr_token) {
            $store->qr_token = (string) Str::uuid();
            $store->save();
        }

        $qrService->generate($store);

        return back()->with('success', "QR code généré pour \"{$store->store_name}\".");
    }

    public function rejectStore(Store $store)
    {
        $store->update(['status' => 'rejected']);
        return back()->with('error', "Le magasin \"{$store->store_name}\" a été rejeté.");
    }

    public function currencies()
    {
        $currencies = GlobalCurrency::all();
        return view('admin.currencies', compact('currencies'));
    }

    public function storeCurrency(Request $request)
    {
        $request->validate([
            'name'   => 'required|string',
            'code'   => 'required|string|max:10|unique:global_currencies,code',
            'symbol' => 'required|string|max:10',
            'rate'   => 'required|numeric|min:0',
        ]);

        if ($request->boolean('is_default')) {
            GlobalCurrency::where('is_default', true)->update(['is_default' => false]);
        }

        GlobalCurrency::create($request->only('name', 'code', 'symbol', 'rate') + [
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Devise ajoutée avec succès.');
    }

    public function updateCurrency(Request $request, GlobalCurrency $currency)
    {
        $request->validate([
            'name'   => 'required|string',
            'symbol' => 'required|string|max:10',
            'rate'   => 'required|numeric|min:0',
        ]);

        if ($request->boolean('is_default')) {
            GlobalCurrency::where('is_default', true)->update(['is_default' => false]);
        }

        $currency->update($request->only('name', 'symbol', 'rate') + [
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Devise mise à jour.');
    }

    public function deleteCurrency(GlobalCurrency $currency)
    {
        $currency->delete();
        return back()->with('success', 'Devise supprimée.');
    }

    public function subscriptionSettings()
    {
        $settings = SubscriptionSetting::current();
        return view('admin.subscription', compact('settings'));
    }

    public function updateSubscription(Request $request)
    {
        // CinetPay gateway keys
        if ($request->filled('cinetpay_api_key')) {
            AppSetting::set('cinetpay_api_key', $request->cinetpay_api_key);
        }
        if ($request->filled('cinetpay_site_id')) {
            AppSetting::set('cinetpay_site_id', $request->cinetpay_site_id);
        }

        // Per-provider settings (active toggle + API credentials)
        $providers = ['orange_money', 'mtn_money', 'wave', 'moov_money', 'visa_card'];
        foreach ($providers as $key) {
            $providerData = AppSetting::get("payment_provider_{$key}", []);
            if (!is_array($providerData)) $providerData = [];

            $providerData['active'] = $request->boolean("provider_{$key}_active");

            foreach ($request->input("provider_{$key}", []) as $field => $value) {
                // Skip blank submissions so re-saving the form (secret fields are
                // never re-echoed, see admin/subscription.blade.php) doesn't wipe
                // out an already-configured credential.
                if ($field !== 'active' && trim((string) $value) !== '') {
                    $providerData[$field] = $value;
                }
            }

            AppSetting::set("payment_provider_{$key}", $providerData);
        }

        return back()->with('success', 'Paramètres mis à jour.');
    }

    public function uploadPaymentLogos(Request $request)
    {
        $request->validate([
            'logos.*' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:512',
        ]);

        $providers = ['orange_money', 'mtn_money', 'wave', 'moov_money', 'visa_card'];

        foreach ($providers as $key) {
            if ($request->hasFile("logos.{$key}") && $request->file("logos.{$key}")->isValid()) {
                $file     = $request->file("logos.{$key}");
                $filename = $key . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/payment-logos'), $filename);
                AppSetting::set("payment_logo_{$key}", $filename);
            }
        }

        return back()->with('success', 'Logos mis à jour avec succès.');
    }

    public function saveDisbursementConfig(Request $request)
    {
        $request->validate([
            'subscription_key'   => 'nullable|string',
            'api_user'           => 'nullable|string',
            'api_key'            => 'nullable|string',
            'target_environment' => 'nullable|string',
            'base_url'           => 'nullable|string',
        ]);

        $config = AppSetting::get('payment_provider_mtn_disbursement', []);
        foreach (['subscription_key', 'api_user', 'api_key', 'target_environment', 'base_url'] as $field) {
            if ($request->filled($field)) {
                $config[$field] = $request->input($field);
            }
        }

        AppSetting::set('payment_provider_mtn_disbursement', $config);

        return back()->with('success', 'Configuration MTN Disbursement mise à jour.');
    }
}
