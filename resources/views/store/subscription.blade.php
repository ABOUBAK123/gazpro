@extends('layouts.app')
@section('title', 'Mon Abonnement')

@section('content')
@php
    $isActive  = $store->hasActiveSubscription();
    $expiry    = $store->subscription_expiry;
    $daysLeft  = $expiry ? now()->diffInDays($expiry, false) : null;
    $planLabel = fn($p) => $p === 'monthly' ? 'Mensuel' : 'Annuel';
    $methodLabel = function(string $m): string {
        return match(true) {
            str_contains($m, 'orange')  => 'Orange Money',
            str_contains($m, 'mtn')     => 'MTN MoMo',
            str_contains($m, 'wave')    => 'Wave',
            str_contains($m, 'moov')    => 'Moov Money',
            str_contains($m, 'visa') || str_contains($m, 'card') || str_contains($m, 'mastercard') => 'Carte bancaire',
            default => ucfirst(str_replace('_', ' ', $m)),
        };
    };
@endphp

<div class="pt-6 space-y-6 max-w-4xl">

    {{-- ── Statut actuel ────────────────────────────────────────── --}}
    <div class="card flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex-1">
            <div class="flex items-center gap-3 mb-1">
                <h2 class="text-lg font-bold text-gray-800">Mon abonnement</h2>
                @if($isActive)
                    <span class="badge bg-green-100 text-green-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block mr-1"></span>Actif
                    </span>
                @elseif($store->subscription_status === 'expired')
                    <span class="badge bg-red-100 text-red-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block mr-1"></span>Expiré
                    </span>
                @else
                    <span class="badge bg-gray-100 text-gray-600">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block mr-1"></span>Inactif
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500">{{ $store->store_name }}</p>
        </div>

        @if($isActive && $expiry)
        <div class="text-right">
            <div class="text-2xl font-bold {{ $daysLeft <= 7 ? 'text-red-600' : ($daysLeft <= 15 ? 'text-amber-600' : 'text-green-600') }}">
                {{ $daysLeft }} jours
            </div>
            <div class="text-xs text-gray-500">
                Expire le {{ $expiry->format('d/m/Y') }}
            </div>
            @if($daysLeft <= 7)
            <div class="text-xs text-red-600 font-medium mt-0.5">
                <i class="fas fa-exclamation-triangle"></i> Renouveler bientôt
            </div>
            @endif
        </div>
        @elseif(!$isActive)
        <div class="text-right">
            <div class="text-sm text-gray-500">Aucun abonnement actif</div>
            <div class="text-xs text-gray-400 mt-0.5">Choisissez un plan ci-dessous</div>
        </div>
        @endif
    </div>

    {{-- ── Choix du plan ────────────────────────────────────────── --}}
    <div x-data="{ plan: 'monthly' }">
        <h3 class="font-semibold text-gray-800 mb-4">
            {{ $isActive ? 'Renouveler / Prolonger' : 'Choisir un plan' }}
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

            {{-- Plan mensuel --}}
            <div @click="plan='monthly'"
                 :class="plan==='monthly' ? 'ring-2 ring-blue-600 border-blue-200 bg-blue-50' : 'border-gray-200 hover:border-blue-300 cursor-pointer'"
                 class="border-2 rounded-2xl p-5 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-gray-800">Plan Mensuel</span>
                    <div :class="plan==='monthly' ? 'bg-blue-600' : 'bg-gray-200'"
                         class="w-5 h-5 rounded-full flex items-center justify-center transition-colors">
                        <i class="fas fa-check text-white text-xs" x-show="plan==='monthly'"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">
                    {{ number_format($settings->monthly_price, 0, ',', ' ') }}
                    <span class="text-base font-normal text-gray-500">{{ $settings->currency }}/mois</span>
                </div>
                <p class="text-sm text-gray-500">Accès complet · Renouvelable chaque mois</p>
            </div>

            {{-- Plan annuel --}}
            <div @click="plan='yearly'"
                 :class="plan==='yearly' ? 'ring-2 ring-blue-600 border-blue-200 bg-blue-50' : 'border-gray-200 hover:border-blue-300 cursor-pointer'"
                 class="border-2 rounded-2xl p-5 transition-all relative overflow-hidden">
                @php $saving = ($settings->monthly_price * 12) - $settings->yearly_price; @endphp
                @if($saving > 0)
                <div class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold rounded-full px-2 py-0.5">
                    -{{ round($saving / ($settings->monthly_price * 12) * 100) }}%
                </div>
                @endif
                <div class="flex items-center justify-between mb-3">
                    <span class="font-semibold text-gray-800">Plan Annuel</span>
                    <div :class="plan==='yearly' ? 'bg-blue-600' : 'bg-gray-200'"
                         class="w-5 h-5 rounded-full flex items-center justify-center transition-colors">
                        <i class="fas fa-check text-white text-xs" x-show="plan==='yearly'"></i>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-1">
                    {{ number_format($settings->yearly_price, 0, ',', ' ') }}
                    <span class="text-base font-normal text-gray-500">{{ $settings->currency }}/an</span>
                </div>
                <p class="text-sm text-gray-500">
                    Accès complet · 12 mois
                    @if($saving > 0)
                    · <span class="text-green-600 font-medium">Économie {{ number_format($saving, 0, ',', ' ') }} {{ $settings->currency }}</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Méthodes de paiement (dynamiques depuis les settings admin) --}}
        @php
            $activeMethods = [
                'orange_money' => ['label'=>'Orange Money','color'=>'#FF6600','bg'=>'#FFF3EB','border'=>'#FFBB99'],
                'mtn_money'    => ['label'=>'MTN MoMo',    'color'=>'#FFCC00','bg'=>'#FFFDE6','border'=>'#FFE680'],
                'wave'         => ['label'=>'Wave',         'color'=>'#1DC8EE','bg'=>'#E8F9FD','border'=>'#99E5F5'],
                'moov_money'   => ['label'=>'Moov Money',  'color'=>'#0055A5','bg'=>'#E8F0FA','border'=>'#99BBEE'],
                'visa_card'    => ['label'=>'Visa / Mastercard','color'=>'#1A1F71','bg'=>'#EEEFFE','border'=>'#AAAADD'],
            ];
            $logoBase2 = asset('images/payment-logos');
        @endphp
        <div class="card mb-5 py-4">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Méthodes de paiement acceptées</p>
            <div class="flex flex-wrap gap-3 items-center">
                @foreach($activeMethods as $mKey => $m)
                @php
                    $pd       = \App\Models\AppSetting::get("payment_provider_{$mKey}", []);
                    $isOn     = !empty($pd) ? ($pd['active'] ?? false) : true;
                    $logoFile = \App\Models\AppSetting::get("payment_logo_{$mKey}", '');
                    $logoUrl2 = $logoFile ? $logoBase2 . '/' . $logoFile : null;
                @endphp
                @if($isOn)
                <div class="flex items-center gap-2 border rounded-xl px-3 py-2 shadow-xs"
                     style="background:{{ $m['bg'] }}; border-color:{{ $m['border'] }};">
                    @if($logoUrl2)
                        <img src="{{ $logoUrl2 }}" alt="{{ $m['label'] }}" class="h-6 w-auto object-contain">
                    @else
                        <span class="w-3 h-3 rounded-full inline-block shrink-0" style="background:{{ $m['color'] }}"></span>
                        <span class="text-sm font-medium" style="color:{{ $m['color'] }}">{{ $m['label'] }}</span>
                    @endif
                </div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Bouton payer --}}
        <form action="{{ route('subscription.initiate') }}" method="POST">
            @csrf
            <input type="hidden" name="plan" :value="plan">
            <button type="submit"
                    class="btn btn-primary w-full sm:w-auto px-8 py-3 text-base">
                <i class="fas fa-lock mr-1"></i>
                Payer maintenant —
                <span x-text="plan === 'monthly'
                    ? '{{ number_format($settings->monthly_price, 0, ',', ' ') }} {{ $settings->currency }}/mois'
                    : '{{ number_format($settings->yearly_price, 0, ',', ' ') }} {{ $settings->currency }}/an'">
                </span>
            </button>
        </form>

        <p class="text-xs text-gray-400 mt-3 flex items-center gap-1">
            <i class="fas fa-shield-alt text-green-500"></i>
            Paiement sécurisé via CinetPay · Vos données bancaires ne nous sont jamais transmises
        </p>
    </div>

    {{-- ── Historique des paiements ──────────────────────────────── --}}
    @if($payments->count() > 0)
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-4">Historique des paiements</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left pb-2 font-medium text-gray-500">Date</th>
                        <th class="text-left pb-2 font-medium text-gray-500">Plan</th>
                        <th class="text-left pb-2 font-medium text-gray-500">Méthode</th>
                        <th class="text-right pb-2 font-medium text-gray-500">Montant</th>
                        <th class="text-center pb-2 font-medium text-gray-500">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($payments as $p)
                    <tr>
                        <td class="py-2.5 text-gray-600">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2.5 text-gray-800 font-medium">{{ $planLabel($p->plan) }}</td>
                        <td class="py-2.5 text-gray-600">{{ $methodLabel($p->payment_method) }}</td>
                        <td class="py-2.5 text-right font-semibold text-gray-800">
                            {{ number_format($p->amount, 0, ',', ' ') }} {{ $p->currency }}
                        </td>
                        <td class="py-2.5 text-center">
                            @if($p->status === 'completed')
                                <span class="badge bg-green-100 text-green-700">
                                    <i class="fas fa-check-circle mr-1"></i>Payé
                                </span>
                            @elseif($p->status === 'pending')
                                <span class="badge bg-amber-100 text-amber-700">
                                    <i class="fas fa-clock mr-1"></i>En attente
                                </span>
                            @else
                                <span class="badge bg-red-100 text-red-700">
                                    <i class="fas fa-times-circle mr-1"></i>Échoué
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
