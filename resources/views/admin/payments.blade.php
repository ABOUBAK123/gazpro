@extends('layouts.app')
@section('title', 'Historique des paiements')
@section('page-title', 'Historique des paiements')

@section('content')
@php
    $planLabel = fn($p) => $p->plan_id
        ? (\App\Models\Plan::find($p->plan_id)?->name ?? ucfirst($p->plan))
        : ucfirst($p->plan);
    $methodLabel = function(?string $m): string {
        if (!$m) return '—';
        return match(true) {
            str_contains($m, 'orange')  => 'Orange Money',
            str_contains($m, 'mtn')     => 'MTN MoMo',
            str_contains($m, 'wave')    => 'Wave',
            str_contains($m, 'moov')    => 'Moov Money',
            str_contains($m, 'cinetpay') => 'CinetPay',
            str_contains($m, 'visa') || str_contains($m, 'card') || str_contains($m, 'mastercard') => 'Carte bancaire',
            default => ucfirst(str_replace('_', ' ', $m)),
        };
    };
    $tabs = [
        'all'       => 'Tous',
        'completed' => 'Réussis',
        'failed'    => 'Échoués',
        'pending'   => 'En attente',
    ];
@endphp

<div class="pt-4 max-w-6xl space-y-6">

    {{-- Résumé --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="text-xs text-gray-500 mb-1">Total paiements</div>
            <div class="text-2xl font-bold text-gray-800">{{ $counts['all'] }}</div>
        </div>
        <div class="stat-card">
            <div class="text-xs text-gray-500 mb-1">Réussis</div>
            <div class="text-2xl font-bold text-green-600">{{ $counts['completed'] }}</div>
        </div>
        <div class="stat-card">
            <div class="text-xs text-gray-500 mb-1">Échoués</div>
            <div class="text-2xl font-bold text-red-600">{{ $counts['failed'] }}</div>
        </div>
        <div class="stat-card">
            <div class="text-xs text-gray-500 mb-1">Revenu total (réussis)</div>
            <div class="text-2xl font-bold text-gray-800">{{ number_format($totalRevenue, 0, ',', ' ') }} XOF</div>
        </div>
    </div>

    <div class="card">
        {{-- Filtres --}}
        <div class="flex gap-1 bg-gray-100 rounded-lg p-1 w-fit mb-5">
            @foreach($tabs as $key => $label)
            <a href="{{ route('admin.payments', $key === 'all' ? [] : ['status' => $key]) }}"
               class="px-4 py-2 rounded-md text-sm font-medium transition {{ ($status ?? 'all') === $key ? 'bg-white shadow-sm text-blue-700' : 'text-gray-600 hover:text-gray-800' }}">
                {{ $label }}
                @if($key !== 'all')
                    <span class="ml-1 text-xs">({{ $counts[$key] }})</span>
                @endif
            </a>
            @endforeach
        </div>

        @if($payments->isEmpty())
            <p class="text-sm text-gray-400">Aucun paiement pour l'instant.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-200">
                        <th class="pb-3 font-semibold text-gray-600">Date</th>
                        <th class="pb-3 font-semibold text-gray-600">Magasin</th>
                        <th class="pb-3 font-semibold text-gray-600">Formule</th>
                        <th class="pb-3 font-semibold text-gray-600">Méthode</th>
                        <th class="pb-3 text-right font-semibold text-gray-600">Montant</th>
                        <th class="pb-3 font-semibold text-gray-600">Référence</th>
                        <th class="pb-3 text-center font-semibold text-gray-600">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($payments as $p)
                    <tr>
                        <td class="py-3 text-gray-600">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 font-medium text-gray-800">{{ $p->store?->store_name ?? '—' }}</td>
                        <td class="py-3 text-gray-600">{{ $planLabel($p) }}</td>
                        <td class="py-3 text-gray-600">{{ $methodLabel($p->payment_method) }}</td>
                        <td class="py-3 text-right font-semibold text-gray-800">
                            {{ number_format($p->amount, 0, ',', ' ') }} {{ $p->currency }}
                        </td>
                        <td class="py-3 text-gray-400 font-mono text-xs">{{ $p->reference ?? '—' }}</td>
                        <td class="py-3 text-center">
                            @if($p->status === 'completed')
                                <span class="badge bg-green-100 text-green-700"><i class="fas fa-check-circle mr-1"></i>Réussi</span>
                            @elseif($p->status === 'pending')
                                <span class="badge bg-amber-100 text-amber-700"><i class="fas fa-clock mr-1"></i>En attente</span>
                            @else
                                <span class="badge bg-red-100 text-red-700"><i class="fas fa-times-circle mr-1"></i>Échoué</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
