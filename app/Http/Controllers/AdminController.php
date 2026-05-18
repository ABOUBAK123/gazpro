<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Store;
use App\Models\GlobalCurrency;
use App\Models\SubscriptionSetting;
use App\Models\Payment;
use App\Models\AppSetting;

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

    public function approveStore(Store $store)
    {
        $store->update(['status' => 'active']);
        return back()->with('success', "Le magasin \"{$store->store_name}\" a été approuvé.");
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
        $request->validate([
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price'  => 'required|numeric|min:0',
            'currency'      => 'required|string|max:10',
        ]);

        $settings = SubscriptionSetting::current();
        $settings->update([
            'monthly_price'    => $request->monthly_price,
            'yearly_price'     => $request->yearly_price,
            'currency'         => $request->currency,
            'mobile_providers' => $request->mobile_providers ? explode(',', $request->mobile_providers) : [],
        ]);

        if ($request->filled('cinetpay_api_key')) {
            AppSetting::set('cinetpay_api_key', $request->cinetpay_api_key);
        }
        if ($request->filled('cinetpay_site_id')) {
            AppSetting::set('cinetpay_site_id', $request->cinetpay_site_id);
        }

        return back()->with('success', 'Paramètres d\'abonnement mis à jour.');
    }
}
