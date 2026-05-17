@extends('layouts.app')
@section('title', 'Historique des ventes')
@section('page-title', 'Historique des ventes')

@section('content')
<div class="space-y-6 pt-4">

    {{-- Filters --}}
    <div class="card">
        <form class="grid grid-cols-2 lg:grid-cols-4 gap-4" method="GET">
            <div>
                <label class="form-label">Date début</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Date fin</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
            </div>
            <div>
                <label class="form-label">Recherche client</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom client..." class="form-input">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn btn-primary flex-1 justify-center"><i class="fas fa-filter"></i> Filtrer</button>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>
    </div>

    {{-- Summary --}}
    <div class="flex items-center justify-between">
        <div class="stat-card flex items-center gap-4 w-auto px-6">
            <i class="fas fa-coins text-green-500 text-2xl"></i>
            <div>
                <div class="text-xs text-gray-500">Total de la période</div>
                <div class="text-xl font-bold text-gray-800">{{ number_format($total, 0, ',', ' ') }} XOF</div>
            </div>
        </div>
        <a href="{{ route('sales.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nouvelle vente
        </a>
    </div>

    {{-- Table --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left">
                    <th class="px-6 py-3 font-semibold text-gray-600">Date</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Client</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Produit</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Qté</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Prix unit.</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Montant</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Devise</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-600">{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-800">{{ $sale->client_name }}</div>
                            @if($sale->client)
                                <div class="text-xs text-blue-500"><i class="fas fa-star"></i> {{ $sale->client->loyalty_points }} pts</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $sale->brand }} {{ $sale->weight }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $sale->quantity }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ number_format($sale->unit_price, 0, ',', ' ') }}</td>
                        <td class="px-6 py-4 font-bold text-green-700">{{ number_format($sale->amount, 0, ',', ' ') }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $sale->currency }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-16 text-center text-gray-500">
                        <i class="fas fa-receipt text-4xl text-gray-200 block mb-3"></i>
                        Aucune vente pour cette période
                    </td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $sales->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
