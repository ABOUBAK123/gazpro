@extends('layouts.app')
@section('title', 'Programme de fidélité')
@section('page-title', 'Programme de fidélité')

@section('content')
<div class="space-y-6 pt-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Settings --}}
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-star text-yellow-500 mr-2"></i>Configuration</h3>
            <form action="{{ route('loyalty.update') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="form-label">Points par bouteille vendue</label>
                    <input type="number" name="points_per_unit" min="1" class="form-input" required value="{{ $loyalty->points_per_unit ?? 1 }}">
                </div>
                <div>
                    <label class="form-label">Seuil pour récompense (points)</label>
                    <input type="number" name="reward_threshold" min="1" class="form-input" required value="{{ $loyalty->reward_threshold ?? 100 }}">
                </div>
                <div>
                    <label class="form-label">Valeur de la récompense</label>
                    <input type="number" name="reward_value" min="0" step="100" class="form-input" required value="{{ $loyalty->reward_value ?? 1000 }}">
                </div>
                <div>
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-input">
                        <option value="XOF" {{ ($loyalty->currency ?? 'XOF') === 'XOF' ? 'selected' : '' }}>XOF</option>
                        <option value="EUR" {{ ($loyalty->currency ?? '') === 'EUR' ? 'selected' : '' }}>EUR</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="active" value="1" {{ ($loyalty->active ?? true) ? 'checked' : '' }}>
                    Programme actif
                </label>

                @if($loyalty->exists ?? false)
                    <div class="bg-yellow-50 rounded-lg p-3 text-sm text-yellow-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ $loyalty->points_per_unit }} point(s) par bouteille · Récompense à {{ $loyalty->reward_threshold }} pts = {{ number_format($loyalty->reward_value, 0, ',', ' ') }} {{ $loyalty->currency }}
                    </div>
                @endif

                <button type="submit" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </form>
        </div>

        {{-- Clients leaderboard --}}
        <div class="lg:col-span-2 card">
            <h3 class="font-semibold text-gray-800 mb-4">Classement clients fidèles</h3>
            @forelse($clients as $i => $client)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 {{ $i < 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600' }} rounded-full flex items-center justify-center font-bold text-sm">
                            {{ $i + 1 }}
                        </div>
                        <div>
                            <div class="font-medium text-gray-800">{{ $client->name }}</div>
                            <div class="text-xs text-gray-500">{{ $client->phone ?? $client->email ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-yellow-600"><i class="fas fa-star text-xs mr-1"></i>{{ $client->loyalty_points }} pts</div>
                        <div class="text-xs text-gray-400">{{ $client->total_orders }} commandes</div>
                        @if($loyalty->exists && $client->loyalty_points >= $loyalty->reward_threshold)
                            <span class="badge bg-green-100 text-green-800 text-xs">Récompense dispo!</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-users text-4xl text-gray-200 block mb-3"></i>
                    Aucun client enregistré
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
