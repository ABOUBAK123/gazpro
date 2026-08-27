@extends('layouts.app')
@section('title', 'Mes filleuls')
@section('page-title', 'Mes filleuls')

@section('content')
<div class="pt-4 max-w-4xl">
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-4">Magasins parrainés</h3>
        @if($stores->isEmpty())
            <p class="text-sm text-gray-400">Aucun magasin n'a encore utilisé votre code de parrainage.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b border-gray-200">
                        <th class="pb-3 font-semibold text-gray-600">Magasin</th>
                        <th class="pb-3 font-semibold text-gray-600">Propriétaire</th>
                        <th class="pb-3 font-semibold text-gray-600">Statut</th>
                        <th class="pb-3 font-semibold text-gray-600">Inscrit le</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($stores as $store)
                    <tr>
                        <td class="py-3 font-medium text-gray-800">{{ $store->store_name }}</td>
                        <td class="py-3 text-gray-600">{{ $store->owner_name }}</td>
                        <td class="py-3">
                            <span class="badge {{ $store->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($store->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-gray-500">{{ $store->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
