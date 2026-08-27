<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Store;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\AppSetting;
use App\Services\MtnMomoService;
use RuntimeException;

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
        $plans    = Plan::active()->get();
        $payments = Payment::where('store_id', $store->id)->latest()->limit(10)->get();

        return view('store.subscription', compact('store', 'plans', 'payments'));
    }

    public function initiate(Request $request)
    {
        $request->validate(['plan_id' => 'required|exists:plans,id']);

        $store  = $this->currentStore();
        $plan   = Plan::findOrFail($request->plan_id);
        $amount = $plan->price;

        $apiKey = AppSetting::get('cinetpay_api_key');
        $siteId = AppSetting::get('cinetpay_site_id');

        if (!$apiKey || !$siteId) {
            return back()->with('error', 'Passerelle de paiement non configurée. Contactez l\'administrateur.');
        }

        $transactionId = 'GAZ' . strtoupper(Str::random(8)) . time();

        $payment = Payment::create([
            'store_id'       => $store->id,
            'amount'         => $amount,
            'currency'       => $plan->currency,
            'payment_method' => 'cinetpay',
            'reference'      => $transactionId,
            'status'         => 'pending',
            'plan'           => $plan->slug,
            'plan_id'        => $plan->id,
        ]);

        $response = Http::timeout(15)->post('https://api-checkout.cinetpay.com/v2/payment', [
            'apikey'                 => $apiKey,
            'site_id'                => $siteId,
            'transaction_id'         => $transactionId,
            'amount'                 => (int) $amount,
            'currency'               => $plan->currency,
            'description'            => 'Abonnement ' . $plan->name . ' GazManager',
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
            $this->applySuccessfulPayment($payment, $method);
        } else {
            $payment->update(['status' => 'failed']);
        }

        return response('OK');
    }

    private function applySuccessfulPayment(Payment $payment, string $paymentMethod): void
    {
        $payment->update([
            'status'         => 'completed',
            'payment_method' => $paymentMethod,
        ]);

        $durationDays = $payment->plan_id ? Plan::find($payment->plan_id)?->duration_days ?? 30 : 30;
        $store = $payment->store;

        $baseline = $store->hasActiveSubscription() ? $store->subscription_expiry : now();
        $expiry = Carbon::parse($baseline)->addDays($durationDays);

        $store->update([
            'subscription_status' => 'active',
            'subscription_expiry' => $expiry,
            'plan_id'             => $payment->plan_id,
        ]);
    }

    public function initiateMtn(Request $request, MtnMomoService $mtn)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'phone'   => 'required|string|min:8|max:15',
        ]);

        $store = $this->currentStore();
        $plan  = Plan::findOrFail($request->plan_id);

        $providerConfig = AppSetting::get('payment_provider_mtn_money', []);
        if (empty($providerConfig['active'] ?? false) || empty($providerConfig['api_user'])) {
            return response()->json(['error' => "MTN Mobile Money n'est pas disponible actuellement."], 422);
        }

        $payment = Payment::create([
            'store_id'       => $store->id,
            'amount'         => $plan->price,
            'currency'       => $plan->currency,
            'payment_method' => 'mtn_money',
            'status'         => 'pending',
            'plan'           => $plan->slug,
            'plan_id'        => $plan->id,
            'phone'          => $request->phone,
        ]);

        try {
            $referenceId = $mtn->requestToPay(
                amount: $plan->price,
                planCurrency: $plan->currency,
                phone: $request->phone,
                externalId: (string) $payment->id,
            );
        } catch (RuntimeException $e) {
            $payment->update(['status' => 'failed']);
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $payment->update(['reference' => $referenceId]);

        return response()->json(['payment_id' => $payment->id, 'status' => 'pending']);
    }

    public function pollMtn(Request $request, Payment $payment, MtnMomoService $mtn)
    {
        $store = $this->currentStore();
        abort_unless($payment->store_id === $store->id, 403);

        if ($payment->status !== 'pending') {
            return response()->json(['status' => $payment->status]);
        }

        try {
            $result = $mtn->checkStatus($payment->reference);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'pending']);
        }

        $mtnStatus = $result['status'] ?? 'PENDING';

        if ($mtnStatus === 'SUCCESSFUL') {
            $this->applySuccessfulPayment($payment, 'mtn_money');
            return response()->json(['status' => 'completed']);
        }

        if ($mtnStatus === 'FAILED') {
            $payment->update(['status' => 'failed']);
            return response()->json(['status' => 'failed', 'reason' => $result['reason'] ?? null]);
        }

        return response()->json(['status' => 'pending']);
    }

    // MTN callback (public, no auth, no CSRF) — best-effort only, always re-verifies via checkStatus()
    public function notifyMtn(Request $request)
    {
        $referenceId = $request->input('referenceId');
        if (!$referenceId) {
            return response('OK');
        }

        $payment = Payment::where('reference', $referenceId)->where('payment_method', 'mtn_money')->first();
        if (!$payment || $payment->status !== 'pending') {
            return response('OK');
        }

        try {
            $result = app(MtnMomoService::class)->checkStatus($referenceId);
            $mtnStatus = $result['status'] ?? 'PENDING';

            if ($mtnStatus === 'SUCCESSFUL') {
                $this->applySuccessfulPayment($payment, 'mtn_money');
            } elseif ($mtnStatus === 'FAILED') {
                $payment->update(['status' => 'failed']);
            }
        } catch (RuntimeException $e) {
            // swallow — polling remains the source of truth
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
