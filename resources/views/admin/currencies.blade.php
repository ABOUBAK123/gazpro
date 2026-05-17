@extends('layouts.app')
@section('title', 'Gestion des devises')
@section('page-title', 'Gestion des devises globales')

@section('content')
<div class="space-y-6 pt-4">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Add currency form --}}
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Ajouter une devise</h3>
            <form action="{{ route('admin.currencies.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-input" placeholder="Euro" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-input" placeholder="EUR" maxlength="10" required>
                    </div>
                    <div>
                        <label class="form-label">Symbole</label>
                        <input type="text" name="symbol" class="form-input" placeholder="€" maxlength="10" required>
                    </div>
                </div>
                <div>
                    <label class="form-label">Taux de change</label>
                    <input type="number" name="rate" class="form-input" placeholder="1.0000" step="0.0001" min="0" required>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="is_default"> Devise par défaut
                </label>
                <button type="submit" class="btn btn-primary w-full">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
            </form>
        </div>

        {{-- Currencies list --}}
        <div class="lg:col-span-2 card">
            <h3 class="font-semibold text-gray-800 mb-4">Devises configurées</h3>
            @forelse($currencies as $currency)
                <div class="flex items-center justify-between p-4 border border-gray-100 rounded-lg mb-3">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center font-bold text-blue-700">
                            {{ $currency->symbol }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">{{ $currency->name }} ({{ $currency->code }})</div>
                            <div class="text-sm text-gray-500">Taux: {{ $currency->rate }}</div>
                        </div>
                        @if($currency->is_default)
                            <span class="badge bg-blue-100 text-blue-800"><i class="fas fa-star text-xs mr-1"></i>Défaut</span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <form action="{{ route('admin.currencies.update', $currency) }}" method="POST" class="flex gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="symbol" value="{{ $currency->symbol }}" class="w-14 border rounded px-2 py-1 text-xs">
                            <input type="number" name="rate" value="{{ $currency->rate }}" step="0.0001" class="w-20 border rounded px-2 py-1 text-xs">
                            <button class="btn btn-warning text-xs py-1"><i class="fas fa-save"></i></button>
                        </form>
                        <form action="{{ route('admin.currencies.destroy', $currency) }}" method="POST"
                              onsubmit="return confirm('Supprimer cette devise ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger text-xs py-1"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-8">Aucune devise configurée</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
