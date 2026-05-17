@extends('layouts.app')
@section('title', 'Commandes')

@section('content')
@php
$livreursJson = $livreurs->map(function($l) {
    return [
        'id'            => $l->id,
        'name'          => $l->name,
        'initial'       => strtoupper(substr($l->name, 0, 1)),
        'vehicle_type'  => $l->vehicle_type,
        'vehicle_icon'  => $l->vehicle_icon,
        'vehicle_label' => $l->vehicle_label,
        'distance_km'   => $l->distance_km,
        'is_available'  => (bool)$l->is_available,
        'active_count'  => (int)$l->active_count,
    ];
})->values();
@endphp
@push('scripts')
<script>
function ordersPage() {
    return {
        assignOrderId:   null,
        assignOrderDesc: '',
        selectedLivreur: null,
        vehicleFilter:   'all',
        livreurs: @json($livreursJson),

        get filteredLivreurs() {
            if (this.vehicleFilter === 'all') return this.livreurs;
            return this.livreurs.filter(l => l.vehicle_type === this.vehicleFilter);
        },
        openAssign(orderId, desc) {
            this.assignOrderId   = orderId;
            this.assignOrderDesc = desc;
            this.selectedLivreur = null;
            this.vehicleFilter   = 'all';
        },
        submitAssign() {
            if (!this.selectedLivreur || !this.assignOrderId) return;
            const f = document.getElementById('assignForm');
            f.action = window.location.origin + '{{ parse_url(url('/commandes'), PHP_URL_PATH) }}/' + this.assignOrderId + '/assigner';
            document.getElementById('assignLivreurInput').value = this.selectedLivreur;
            f.submit();
        },
        submitConfirmOnly() {
            if (!this.assignOrderId) return;
            const f = document.getElementById('confirmOnlyForm');
            f.action = window.location.origin + '{{ parse_url(url('/commandes'), PHP_URL_PATH) }}/' + this.assignOrderId + '/statut';
            f.submit();
        },
    };
}
</script>
@endpush

<div x-data="ordersPage()" class="space-y-6 pt-4">

{{-- Hidden forms (outside modal, inside x-data) --}}
<form id="assignForm" method="POST" style="display:none">
    @csrf
    <input type="hidden" name="livreur_id" id="assignLivreurInput">
</form>
<form id="confirmOnlyForm" method="POST" style="display:none">
    @csrf @method('PATCH')
    <input type="hidden" name="status" value="confirmed">
</form>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <form class="flex gap-3 items-center flex-wrap" method="GET">
            <select name="status" class="form-select w-44" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="pending"   {{ request('status')==='pending'   ? 'selected':'' }}>En attente</option>
                <option value="confirmed" {{ request('status')==='confirmed' ? 'selected':'' }}>Confirmées</option>
                <option value="en_route"  {{ request('status')==='en_route'  ? 'selected':'' }}>En route</option>
                <option value="delivered" {{ request('status')==='delivered' ? 'selected':'' }}>Livrées</option>
                <option value="cancelled" {{ request('status')==='cancelled' ? 'selected':'' }}>Annulées</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Rechercher client…" class="form-input w-48">
            <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i></button>
        </form>
        <a href="{{ route('orders.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i>Nouvelle commande
        </a>
    </div>

    {{-- Table --}}
    <div class="card p-0 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="text-left">
                    <th class="table-header">#</th>
                    <th class="table-header">Client</th>
                    <th class="table-header">Produit</th>
                    <th class="table-header">Total</th>
                    <th class="table-header">Livreur</th>
                    <th class="table-header">Statut</th>
                    <th class="table-header">Date</th>
                    <th class="table-header">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50/60 transition-colors">

                    <td class="table-cell text-gray-400 text-xs">#{{ $order->id }}</td>

                    {{-- Client --}}
                    <td class="table-cell">
                        <div class="font-semibold text-gray-800">{{ $order->client_name }}</div>
                        @if($order->client_phone)
                            <a href="tel:{{ $order->client_phone }}"
                               class="text-xs text-blue-600 flex items-center gap-1 hover:underline">
                                <i class="fas fa-phone text-xs"></i>{{ $order->client_phone }}
                            </a>
                        @endif
                        @if($order->client_address)
                            <div class="text-xs text-gray-400 mt-0.5">
                                <i class="fas fa-map-marker-alt mr-1"></i>{{ $order->client_address }}
                            </div>
                        @endif
                        @if($order->latitude && $order->longitude)
                            <a href="https://www.google.com/maps?q={{ $order->latitude }},{{ $order->longitude }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 mt-0.5 text-xs text-green-600 hover:text-green-800 font-semibold">
                                <i class="fas fa-location-dot text-xs"></i>GPS disponible
                            </a>
                        @endif
                    </td>

                    {{-- Produit --}}
                    <td class="table-cell">
                        <div class="font-semibold text-gray-800">{{ $order->brand }} {{ $order->weight }}</div>
                        <div class="text-xs text-gray-500">
                            {{ $order->quantity }} × {{ number_format($order->unit_price, 0, ',', ' ') }} {{ $order->currency }}
                        </div>
                    </td>

                    {{-- Total --}}
                    <td class="table-cell font-bold text-gray-800">
                        {{ number_format($order->total_price, 0, ',', ' ') }} {{ $order->currency }}
                    </td>

                    {{-- Livreur --}}
                    <td class="table-cell">
                        @if($order->livreur)
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold text-white shrink-0"
                                     style="background:linear-gradient(135deg,#059669,#34d399);">
                                    {{ strtoupper(substr($order->livreur->name,0,1)) }}
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-gray-800">{{ $order->livreur->name }}</div>
                                    <div class="text-xs text-gray-400 flex items-center gap-1">
                                        <i class="fas {{ $order->livreur->vehicle_icon }} text-xs"></i>
                                        {{ $order->livreur->vehicle_label }}
                                    </div>
                                </div>
                            </div>
                            @if(!in_array($order->status, ['delivered','cancelled']))
                            <form action="{{ route('orders.unassign', $order) }}" method="POST" class="mt-1">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:text-red-600 underline">Retirer</button>
                            </form>
                            @endif
                        @else
                            @if(!in_array($order->status, ['delivered','cancelled']))
                            <button @click="openAssign({{ $order->id }}, '{{ addslashes($order->client_name) }} · {{ $order->brand }} {{ $order->weight }} × {{ $order->quantity }}')"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1.5 rounded-lg
                                           bg-amber-50 text-amber-700 hover:bg-amber-100 transition border border-amber-200">
                                <i class="fas fa-motorcycle text-xs"></i>Assigner
                            </button>
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        @endif
                    </td>

                    {{-- Statut --}}
                    <td class="table-cell">
                        <span class="badge
                            {{ $order->status==='pending'   ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $order->status==='confirmed' ? 'bg-blue-100 text-blue-800'     : '' }}
                            {{ $order->status==='en_route'  ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $order->status==='delivered' ? 'bg-green-100 text-green-800'   : '' }}
                            {{ $order->status==='cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                        ">
                            <i class="fas
                                {{ $order->status==='pending'   ? 'fa-clock'        : '' }}
                                {{ $order->status==='confirmed' ? 'fa-check'        : '' }}
                                {{ $order->status==='en_route'  ? 'fa-motorcycle'   : '' }}
                                {{ $order->status==='delivered' ? 'fa-check-circle' : '' }}
                                {{ $order->status==='cancelled' ? 'fa-ban'          : '' }}
                                text-xs mr-1"></i>
                            {{ $order->status_label }}
                        </span>
                    </td>

                    {{-- Date --}}
                    <td class="table-cell text-gray-500 text-xs">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>

                    {{-- Actions --}}
                    <td class="table-cell">
                        <div class="flex gap-1 flex-wrap">
                            {{-- Pending → ouvre le modal d'assignation (confirme + assigne en même temps) --}}
                            @if($order->status === 'pending')
                                <button @click="openAssign({{ $order->id }}, '{{ addslashes($order->client_name) }} · {{ $order->brand }} {{ $order->weight }} × {{ $order->quantity }}')"
                                        class="btn btn-primary text-xs py-1 px-2.5"
                                        title="Accepter & choisir un livreur">
                                    <i class="fas fa-check"></i><i class="fas fa-motorcycle text-xs ml-1"></i>
                                </button>
                            @endif
                            @if(in_array($order->status, ['confirmed','en_route']))
                                <form action="{{ route('orders.status', $order) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="delivered">
                                    <button class="btn btn-success text-xs py-1 px-2" title="Marquer livré">
                                        <i class="fas fa-truck"></i>
                                    </button>
                                </form>
                            @endif
                            @if(in_array($order->status, ['pending','confirmed','en_route']))
                                <form action="{{ route('orders.status', $order) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="cancelled">
                                    <button class="btn btn-danger text-xs py-1 px-2" title="Annuler">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('orders.destroy', $order) }}" method="POST"
                                  onsubmit="return confirm('Supprimer cette commande ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-secondary text-xs py-1 px-2" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-16 text-center text-gray-500">
                        <i class="fas fa-shopping-cart text-4xl text-gray-200 block mb-3"></i>
                        Aucune commande trouvée
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $orders->withQueryString()->links() }}
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         MODAL — CHOISIR UN LIVREUR
         (inside x-data div so Alpine can access state)
    ══════════════════════════════════════════════ --}}
    <div x-show="assignOrderId !== null" x-cloak
         class="fixed inset-0 bg-black/60 z-50 flex items-end sm:items-center justify-center"
         @keydown.escape.window="assignOrderId = null">

        <div class="bg-white w-full sm:rounded-3xl sm:max-w-2xl shadow-2xl flex flex-col"
             style="max-height:90dvh"
             @click.stop>

            {{-- Header --}}
            <div class="flex items-start justify-between px-6 py-5 border-b border-gray-100 shrink-0">
                <div>
                    <h3 class="font-black text-gray-900 text-lg">Choisir un livreur</h3>
                    <p class="text-sm text-gray-500 mt-0.5" x-text="assignOrderDesc"></p>
                </div>
                <button @click="assignOrderId = null"
                        class="text-gray-400 hover:text-gray-600 ml-4 mt-0.5">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            {{-- Vehicle filter chips --}}
            <div class="px-6 pt-4 pb-3 flex gap-2 flex-wrap shrink-0 border-b border-gray-100">
                <span class="text-xs text-gray-400 font-semibold self-center mr-1">Véhicule :</span>
                <template x-for="[val,label] in [['all','Tous'],['moto','🏍️ Moto'],['tricycle','🛺 Tricycle'],['voiture','🚗 Voiture']]" :key="val">
                    <button @click="vehicleFilter = val"
                            :class="vehicleFilter === val
                                ? 'bg-blue-600 text-white shadow'
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition"
                            x-text="label">
                    </button>
                </template>
                <span class="ml-auto text-xs text-gray-400 self-center"
                      x-text="filteredLivreurs.length + ' livreur(s)'"></span>
            </div>

            {{-- Livreur cards --}}
            <div class="overflow-y-auto flex-1 p-5">
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <template x-for="l in filteredLivreurs" :key="l.id">
                        <button @click="selectedLivreur = l.id"
                                :class="selectedLivreur === l.id
                                    ? 'ring-2 ring-blue-500 bg-blue-50 border-blue-200'
                                    : (l.is_available ? 'bg-white border-gray-200 hover:border-blue-300 hover:bg-blue-50/50' : 'bg-gray-50 border-gray-200 opacity-75 hover:opacity-100')"
                                class="rounded-2xl p-3.5 text-left transition border-2 relative">

                            {{-- Selected checkmark --}}
                            <div x-show="selectedLivreur === l.id"
                                 class="absolute top-2 right-2 w-5 h-5 bg-blue-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>

                            {{-- Avatar + availability dot --}}
                            <div class="flex items-center gap-2.5 mb-3">
                                <div class="relative shrink-0">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center font-black text-white"
                                         :style="selectedLivreur === l.id
                                             ? 'background:linear-gradient(135deg,#1d4ed8,#1e40af)'
                                             : 'background:linear-gradient(135deg,#2563eb,#1d4ed8)'"
                                         x-text="l.initial"></div>
                                    <div class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full border-2 border-white"
                                         :class="l.is_available ? 'bg-green-500' : 'bg-orange-400'"></div>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-900 text-sm leading-tight truncate" x-text="l.name"></div>
                                    <div class="text-xs mt-0.5"
                                         :class="l.is_available ? 'text-green-600 font-semibold' : 'text-orange-500'"
                                         x-text="l.is_available ? 'Disponible' : 'En course'"></div>
                                </div>
                            </div>

                            {{-- Vehicle type badge --}}
                            <div class="inline-flex items-center gap-1.5 bg-gray-100 rounded-lg px-2.5 py-1 mb-2">
                                <i :class="'fas ' + l.vehicle_icon + ' text-xs text-gray-600'"></i>
                                <span class="text-xs font-semibold text-gray-700" x-text="l.vehicle_label"></span>
                            </div>

                            {{-- Distance --}}
                            <div class="flex items-center gap-1 text-xs mt-1"
                                 :class="l.distance_km !== null ? 'text-blue-600 font-bold' : 'text-gray-400 italic'">
                                <i class="fas fa-location-dot text-xs"></i>
                                <span x-text="l.distance_km !== null ? l.distance_km + ' km du magasin' : 'Position inconnue'"></span>
                            </div>

                            {{-- Active courses --}}
                            <div x-show="l.active_count > 0" class="flex items-center gap-1 text-xs text-orange-500 mt-1">
                                <i class="fas fa-motorcycle text-xs"></i>
                                <span x-text="l.active_count + ' course(s) en cours'"></span>
                            </div>
                        </button>
                    </template>

                    {{-- Empty state --}}
                    <div x-show="filteredLivreurs.length === 0"
                         class="col-span-3 py-12 text-center text-gray-400">
                        <i class="fas fa-motorcycle text-4xl text-gray-200 block mb-3"></i>
                        <p class="font-semibold">Aucun livreur pour ce type de véhicule</p>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-gray-100 shrink-0 flex items-center gap-3 flex-wrap">
                <button @click="submitAssign()"
                        :disabled="!selectedLivreur"
                        :class="selectedLivreur
                            ? 'bg-blue-600 hover:bg-blue-700 text-white'
                            : 'bg-gray-100 text-gray-400 cursor-not-allowed'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition">
                    <i class="fas fa-motorcycle"></i>
                    <span x-text="selectedLivreur ? 'Assigner ce livreur' : 'Sélectionnez un livreur'"></span>
                </button>
                <button @click="submitConfirmOnly()"
                        class="text-sm text-gray-500 hover:text-gray-700 underline underline-offset-2 transition">
                    Confirmer sans livreur
                </button>
                <button @click="assignOrderId = null"
                        class="ml-auto btn btn-secondary text-sm">
                    <i class="fas fa-times"></i>Annuler
                </button>
            </div>
        </div>
    </div>

</div>{{-- end x-data --}}
@endsection
