@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Gestion des clients')

@section('content')
<div class="pt-4">
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">{{ $clients->count() }} clients</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left">
                    <th class="px-6 py-3 font-semibold text-gray-600">Nom</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Contact</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Adresse</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Commandes</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Points fidélité</th>
                    <th class="px-6 py-3 font-semibold text-gray-600">Depuis</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($clients as $client)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ $client->name }}</td>
                        <td class="px-6 py-4 text-gray-600 text-xs">
                            @if($client->phone)<div>{{ $client->phone }}</div>@endif
                            @if($client->email)<div>{{ $client->email }}</div>@endif
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $client->address ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-800 font-semibold">{{ $client->total_orders }}</td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $client->loyalty_points > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-500' }}">
                                <i class="fas fa-star text-xs mr-1"></i>{{ $client->loyalty_points }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-400 text-xs">{{ $client->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-16 text-center text-gray-500">
                        <i class="fas fa-users text-4xl text-gray-200 block mb-3"></i>
                        Aucun client encore. Les clients s'ajoutent automatiquement lors des commandes.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
