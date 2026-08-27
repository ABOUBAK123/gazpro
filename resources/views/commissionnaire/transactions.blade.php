@extends('layouts.app')
@section('title', 'Transactions')
@section('page-title', 'Historique des transactions')

@section('content')
<div class="pt-4 max-w-4xl">
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-4">Toutes mes transactions</h3>
        @if($transactions->isEmpty())
            <p class="text-sm text-gray-400">Aucune transaction pour l'instant.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-200">
                        <th class="pb-3 font-semibold text-gray-600">Date</th>
                        <th class="pb-3 font-semibold text-gray-600">Type</th>
                        <th class="pb-3 font-semibold text-gray-600">Magasin</th>
                        <th class="pb-3 text-right font-semibold text-gray-600">Montant</th>
                        <th class="pb-3 text-center font-semibold text-gray-600">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($transactions as $tx)
                    <tr>
                        <td class="py-3 text-gray-600">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3">
                            <span class="badge {{ $tx->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $tx->type === 'credit' ? 'Commission' : 'Retrait' }}
                            </span>
                        </td>
                        <td class="py-3 text-gray-600">{{ $tx->store?->store_name ?? '—' }}</td>
                        <td class="py-3 text-right font-semibold {{ $tx->type === 'credit' ? 'text-green-700' : 'text-gray-700' }}">
                            {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} XOF
                        </td>
                        <td class="py-3 text-center">
                            @if($tx->status === 'completed')
                                <span class="badge bg-green-100 text-green-700"><i class="fas fa-check-circle mr-1"></i>Terminé</span>
                            @elseif($tx->status === 'pending')
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
        <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
