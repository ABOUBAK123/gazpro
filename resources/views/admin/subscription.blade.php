@extends('layouts.app')
@section('title', 'Paramètres abonnement')
@section('page-title', 'Paramètres d\'abonnement')

@section('content')
@php
    $cpApiKey = \App\Models\AppSetting::get('cinetpay_api_key', '');
    $cpSiteId = \App\Models\AppSetting::get('cinetpay_site_id', '');
@endphp

<div class="pt-4 max-w-2xl space-y-6">

    {{-- Tarifs --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-6">Tarifs abonnement</h3>
        <form action="{{ route('admin.subscription.update') }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Prix mensuel</label>
                    <input type="number" name="monthly_price" value="{{ $settings->monthly_price }}" min="0" step="100" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Prix annuel</label>
                    <input type="number" name="yearly_price" value="{{ $settings->yearly_price }}" min="0" step="100" class="form-input" required>
                </div>
            </div>
            <div>
                <label class="form-label">Devise</label>
                <select name="currency" class="form-input">
                    <option value="XOF" {{ $settings->currency === 'XOF' ? 'selected' : '' }}>XOF (Franc CFA)</option>
                    <option value="EUR" {{ $settings->currency === 'EUR' ? 'selected' : '' }}>EUR (Euro)</option>
                    <option value="USD" {{ $settings->currency === 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                </select>
            </div>
            <div class="bg-blue-50 rounded-lg p-4 space-y-2">
                <div class="text-sm font-medium text-blue-800">Aperçu des tarifs</div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Mensuel</span>
                    <span class="font-semibold">{{ number_format($settings->monthly_price, 0, ',', ' ') }} {{ $settings->currency }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Annuel</span>
                    <span class="font-semibold">{{ number_format($settings->yearly_price, 0, ',', ' ') }} {{ $settings->currency }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600">Économie annuel</span>
                    <span class="font-semibold text-green-600">-{{ number_format(($settings->monthly_price * 12) - $settings->yearly_price, 0, ',', ' ') }} {{ $settings->currency }}</span>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer les tarifs
            </button>
        </form>
    </div>

    {{-- CinetPay --}}
    <div class="card">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:#ff6b00;">
                <i class="fas fa-plug text-white text-sm"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Passerelle CinetPay</h3>
                <p class="text-xs text-gray-500">Orange Money · MTN · Wave · Moov · Visa</p>
            </div>
            @if($cpApiKey && $cpSiteId)
                <span class="ml-auto badge bg-green-100 text-green-700">
                    <i class="fas fa-circle text-xs mr-1"></i>Configuré
                </span>
            @else
                <span class="ml-auto badge bg-amber-100 text-amber-700">
                    <i class="fas fa-exclamation-circle text-xs mr-1"></i>Non configuré
                </span>
            @endif
        </div>

        <form action="{{ route('admin.subscription.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="monthly_price" value="{{ $settings->monthly_price }}">
            <input type="hidden" name="yearly_price" value="{{ $settings->yearly_price }}">
            <input type="hidden" name="currency" value="{{ $settings->currency }}">

            <div>
                <label class="form-label">API Key CinetPay</label>
                <input type="text" name="cinetpay_api_key"
                       value="{{ $cpApiKey }}"
                       class="form-input font-mono text-sm"
                       placeholder="Votre API Key depuis votre espace CinetPay">
            </div>
            <div>
                <label class="form-label">Site ID CinetPay</label>
                <input type="text" name="cinetpay_site_id"
                       value="{{ $cpSiteId }}"
                       class="form-input font-mono text-sm"
                       placeholder="Votre Site ID depuis votre espace CinetPay">
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-xs text-gray-500 space-y-1">
                <p><i class="fas fa-info-circle text-blue-400 mr-1"></i>
                    Clés disponibles sur <strong>cinetpay.com</strong> → Mon compte → API</p>
                <p><i class="fas fa-link text-blue-400 mr-1"></i>
                    URL de notification à renseigner dans CinetPay :
                    <span class="font-mono bg-white border border-gray-200 rounded px-1.5 py-0.5 select-all">{{ route('subscription.notify') }}</span>
                </p>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer la configuration
            </button>
        </form>
    </div>

</div>
@endsection
