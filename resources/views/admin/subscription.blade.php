@extends('layouts.app')
@section('title', 'Paramètres abonnement')
@section('page-title', 'Paramètres d\'abonnement')

@section('content')
<div class="pt-4 max-w-2xl">
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-6">Configuration des abonnements</h3>
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
            <div>
                <label class="form-label">Fournisseurs Mobile Money (séparés par virgule)</label>
                <input type="text" name="mobile_providers"
                       value="{{ is_array($settings->mobile_providers) ? implode(',', $settings->mobile_providers) : $settings->mobile_providers }}"
                       class="form-input" placeholder="Orange Money,Moov Money">
            </div>

            {{-- Preview --}}
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
                <i class="fas fa-save"></i> Enregistrer les paramètres
            </button>
        </form>
    </div>
</div>
@endsection
