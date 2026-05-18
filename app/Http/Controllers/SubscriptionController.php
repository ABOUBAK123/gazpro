<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Store;
use App\Models\Payment;
use App\Models\SubscriptionSetting;
use App\Models\AppSetting;

class SubscriptionController extends Controller
{
    private function currentStore(): Store
    {
        if (Auth::guard('store')->check()) {
            return Auth::guard('store')->user();
        }
        return Auth::guard('staff')->user()->store;
    }

    public function index()
    {
        $store    = $this->currentStore();
        $settings = SubscriptionSetting::current();
        $payments = Payment::where('store_id', $store->id)->latest()->limit(10)->get();

        return view('store.subscription', compact('store', 'settings', 'payments'));
    }

    public function initiate(Request $request)
    {
        $request->validate(['plan' => 'required|in:monthly,yearly']);

        $store    = $this->currentStore();
        $settings = SubscriptionSetting::current();
        $plan     = $request->plan;
        $amount   = $plan === 'monthly' ? $settings->monthly_price : $settings->yearly_price;

        $apiKey = AppSetting::get('cinetpay_api_key');
        $siteId = AppSetting::get('cinetpay_site_id');

        if (!$apiKey || !$siteId) {
            return back()->with('error', 'Passerelle de paiement non configurée. Contactez l\'administrateur.');
        }

        $transactionId = 'GAZ' . strtoupper(Str::random(8)) . time();

        $payment = Payment::create([
            'store_id'       => $store->id,
            'amount'         => $amount,
            'currency'       => $settings->currency,
            'payment_method' => 'cinetpay',
            'reference'      => $transactionId,
            'status'         => 'pending',
            'plan'           => $plan,
        ]);

        $response = Http::timeout(15)->post('https://api-checkout.cinetpay.com/v2/payment', [
            'apikey'                 => $apiKey,
            'site_id'                => $siteId,
            'transaction_id'         => $transactionId,
            'amount'                 => (int) $amount,
            'currency'               => $settings->currency,
            'description'            => 'Abonnement ' . ($plan === 'monthly' ? 'mensuel' : 'annuel') . ' GazManager',
            'customer_id'            => (string) $store->id,
            'customer_name'          => $store->store_name,
            'customer_surname'       => $store->owner_name,
            'customer_email'         => $store->email,
            'customer_phone_number'  => $store->phone,
            'customer_address'       => $store->address ?? 'Abidjan',
            'customer_city'          => 'Abidjan',
            'customer_country'       => 'CI',
            'customer_state'         => 'CI',
            'customer_zip_code'      => '00225',
            'notify_url'             => route('subscription.notify'),
            'return_url'             => route('subscription.return'),
            'channels'               => 'ALL',
            'metadata'               => 'payment_id:' . $payment->id,
            'lang'                   => 'fr',
        ]);

        if (!$response->successful()) {
            $payment->update(['status' => 'failed']);
            return back()->with('error', 'Impossible de joindre la passerelle de paiement. Réessayez.');
        }

        $data = $response->json();

        if (($data['code'] ?? '') !== '201') {
            $payment->update(['status' => 'failed']);
            return back()->with('error', 'Erreur passerelle : ' . ($data['message'] ?? 'Paiement non initié.'));
        }

        return redirect($data['data']['payment_url']);
    }

    // IPN webhook — called by CinetPay after payment (no auth, no CSRF)
    public function notify(Request $request)
    {
        $transactionId = $request->input('cpm_trans_id');
        if (!$transactionId) {
            return response('INVALID', 400);
        }

        $payment = Payment::where('reference', $transactionId)->first();
        if (!$payment || $payment->status === 'completed') {
            return response('OK');
        }

        $apiKey = AppSetting::get('cinetpay_api_key');
        $siteId = AppSetting::get('cinetpay_site_id');

        $check = Http::timeout(10)->get('https://api-checkout.cinetpay.com/v2/payment/check', [
            'apikey'         => $apiKey,
            'site_id'        => $siteId,
            'transaction_id' => $transactionId,
        ]);

        if (!$check->successful()) {
            return response('CHECK_FAILED', 500);
        }

        $result = $check->json();
        $status = $result['data']['status'] ?? 'REFUSED';

        if ($status === 'ACCEPTED') {
            $method = $result['data']['payment_method'] ?? 'cinetpay';

            $payment->update([
                'status'         => 'completed',
                'payment_method' => $method,
            ]);

            $expiry = $payment->plan === 'monthly' ? now()->addMonth() : now()->addYear();

            // Extend if already active
            $store = $payment->store;
            if ($store->hasActiveSubscription()) {
                $expiry = $store->subscription_expiry
                    ->addMonth($payment->plan === 'monthly' ? 1 : 12);
            }

            $store->update([
                'subscription_status' => 'active',
                'subscription_expiry' => $expiry,
            ]);
        } else {
            $payment->update(['status' => 'failed']);
        }

        return response('OK');
    }

    public function returnPage(Request $request)
    {
        $store = $this->currentStore();
        $store->refresh();

        if ($store->hasActiveSubscription()) {
            return redirect()->route('subscription.index')
                ->with('success', 'Paiement confirmé ! Votre abonnement est actif.');
        }

        return redirect()->route('subscription.index')
            ->with('info', 'Paiement en cours de vérification. Le statut sera mis à jour automatiquement.');
    }
}
