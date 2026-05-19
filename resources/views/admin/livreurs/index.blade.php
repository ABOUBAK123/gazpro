@extends('layouts.app')
@section('title', 'Gestion des Livreurs')

@section('content')
<div x-data="{ showAdd: false, editId: null, editData: {} }">
<div class="space-y-6 pt-4">

    {{-- Header --}}
    <div class="rounded-2xl p-6 text-white relative overflow-hidden"
         style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 60%,#2563eb 100%);">
        <div class="absolute inset-0 opacity-10"
             style="background-image:radial-gradient(circle at 80% 50%,white 1px,transparent 1px);background-size:24px 24px;"></div>
        <div class="relative flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="text-blue-200 text-xs font-medium uppercase tracking-widest mb-1">Administration</div>
                <h2 class="text-2xl font-bold flex items-center gap-3">
                    <i class="fas fa-motorcycle text-amber-400"></i>Livreurs indépendants
                </h2>
                <p class="text-blue-200 text-sm mt-1">
                    {{ $livreurs->count() }} livreur(s) ·
                    {{ $livreurs->where('is_available', true)->where('status','active')->count() }} disponible(s)
                </p>
            </div>
            <button @click="showAdd = true"
                    class="shrink-0 flex items-center gap-2 bg-white/20 hover:bg-white/30 backdrop-blur text-white font-bold px-5 py-2.5 rounded-xl transition">
                <i class="fas fa-plus"></i>Ajouter un livreur
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-4">
        @php
            $active    = $livreurs->where('status','active')->count();
            $available = $livreurs->where('status','active')->where('is_available',true)->count();
            $onRoute   = $livreurs->sum('active_count');
            $delivered = $livreurs->sum('delivered_count');
        @endphp
        <div class="card text-center">
            <div class="text-3xl font-black text-blue-600">{{ $active }}</div>
            <div class="text-xs text-gray-500 mt-1">Livreurs actifs</div>
        </div>
        <div class="card text-center">
            <div class="text-3xl font-black text-green-600">{{ $available }}</div>
            <div class="text-xs text-gray-500 mt-1">Disponibles</div>
        </div>
        <div class="card text-center">
            <div class="text-3xl font-black text-orange-500">{{ $onRoute }}</div>
            <div class="text-xs text-gray-500 mt-1">En course</div>
        </div>
        <div class="card text-center">
            <div class="text-3xl font-black text-indigo-600">{{ $delivered }}</div>
            <div class="text-xs text-gray-500 mt-1">Livraisons totales</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-motorcycle text-blue-600 text-sm"></i>
            </div>
            <h3 class="font-bold text-gray-800">Tous les livreurs</h3>
        </div>

        @if($livreurs->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center px-6">
            <div class="w-16 h-16 bg-gray-50 rounded-2xl flex items-center justify-center mb-3">
                <i class="fas fa-motorcycle text-gray-300 text-2xl"></i>
            </div>
            <p class="text-gray-500 font-medium">Aucun livreur enregistré</p>
            <button @click="showAdd = true" class="mt-4 btn btn-primary">
                <i class="fas fa-plus"></i>Ajouter un livreur
            </button>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="table-header">Livreur</th>
                        <th class="table-header">Téléphone</th>
                        <th class="table-header">Véhicule</th>
                        <th class="table-header">Statut</th>
                        <th class="table-header">Disponibilité</th>
                        <th class="table-header">Position GPS</th>
                        <th class="table-header">Courses</th>
                        <th class="table-header">Lien app</th>
                        <th class="table-header">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($livreurs as $livreur)
                    <tr class="table-row">
                        <td class="table-cell">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white shrink-0"
                                     style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                                    {{ strtoupper(substr($livreur->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800">{{ $livreur->name }}</div>
                                    @if($livreur->vehicle_plate)
                                        <div class="text-xs text-gray-400 font-mono">{{ $livreur->vehicle_plate }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <a href="tel:{{ $livreur->phone }}"
                               class="flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium">
                                <i class="fas fa-phone text-xs"></i>{{ $livreur->phone }}
                            </a>
                        </td>
                        <td class="table-cell">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 text-xs font-semibold">
                                <i class="fas {{ $livreur->vehicle_icon }} text-xs"></i>
                                {{ $livreur->vehicle_label }}
                            </span>
                        </td>
                        <td class="table-cell">
                            @if($livreur->status === 'active')
                                <span class="badge bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block mr-1 animate-pulse"></span>
                                    Actif
                                </span>
                            @else
                                <span class="badge bg-gray-100 text-gray-500">Inactif</span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($livreur->is_available && $livreur->status === 'active')
                                <span class="badge bg-emerald-100 text-emerald-700">
                                    <i class="fas fa-circle text-xs mr-1 text-emerald-500"></i>Disponible
                                </span>
                            @else
                                <span class="badge bg-orange-100 text-orange-700">
                                    <i class="fas fa-motorcycle text-xs mr-1"></i>En course
                                </span>
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($livreur->latitude && $livreur->longitude)
                                <a href="https://www.google.com/maps?q={{ $livreur->latitude }},{{ $livreur->longitude }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-800 font-semibold">
                                    <i class="fas fa-location-dot"></i>Voir sur carte
                                </a>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    Mis à jour {{ $livreur->updated_at->diffForHumans() }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Non partagée</span>
                            @endif
                        </td>
                        <td class="table-cell text-center">
                            <div class="flex items-center gap-2 justify-center">
                                <span class="font-bold text-orange-600">{{ $livreur->active_count }}</span>
                                <span class="text-gray-300">|</span>
                                <span class="font-bold text-green-600">{{ $livreur->delivered_count }}</span>
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">en cours | livrées</div>
                        </td>
                        <td class="table-cell">
                            @php $appUrl = route('livreur.app', $livreur->access_token); @endphp
                            <div class="space-y-1.5">
                                {{-- Token Flutter --}}
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-400 w-10 shrink-0">Token</span>
                                    <code class="text-xs bg-gray-100 text-gray-700 font-mono px-2 py-0.5 rounded w-28 truncate block"
                                          title="{{ $livreur->access_token }}">{{ $livreur->access_token }}</code>
                                    <button onclick="copyText('{{ $livreur->access_token }}', this)"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 transition shrink-0"
                                            title="Copier le token (app Flutter)">
                                        <i class="fas fa-copy text-xs"></i>
                                    </button>
                                </div>
                                {{-- Lien PWA --}}
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-gray-400 w-10 shrink-0">PWA</span>
                                    <input type="text" readonly value="{{ $appUrl }}"
                                           class="text-xs border border-gray-200 rounded-lg px-2 py-0.5 w-28 text-gray-500 bg-gray-50 font-mono truncate">
                                    <button onclick="copyText('{{ $appUrl }}', this)"
                                            class="w-7 h-7 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition shrink-0"
                                            title="Copier le lien PWA">
                                        <i class="fas fa-copy text-xs"></i>
                                    </button>
                                    <a href="{{ $appUrl }}" target="_blank"
                                       class="w-7 h-7 flex items-center justify-center rounded-lg bg-gray-50 text-gray-500 hover:bg-gray-100 transition shrink-0">
                                        <i class="fas fa-external-link-alt text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td class="table-cell">
                            <div class="flex items-center gap-1">
                                <button @click="editId = {{ $livreur->id }}; editData = {
                                            name: '{{ addslashes($livreur->name) }}',
                                            phone: '{{ $livreur->phone }}',
                                            vehicle_type: '{{ $livreur->vehicle_type }}',
                                            vehicle_plate: '{{ $livreur->vehicle_plate }}',
                                            status: '{{ $livreur->status }}'
                                        }"
                                        class="btn btn-secondary text-xs py-1.5 px-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.livreurs.token', $livreur) }}" method="POST"
                                      title="Régénérer le lien"
                                      onsubmit="return confirm('Régénérer le lien ? L\'ancien sera invalide.')">
                                    @csrf
                                    <button class="btn btn-warning text-xs py-1.5 px-3">
                                        <i class="fas fa-rotate"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.livreurs.destroy', $livreur) }}" method="POST"
                                      onsubmit="return confirm('Supprimer ce livreur ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger text-xs py-1.5 px-3">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>

{{-- ── Modal Ajouter ── --}}
<div x-show="showAdd" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @click.self="showAdd = false">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-7">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fas fa-motorcycle text-blue-600"></i>Nouveau livreur
            </h3>
            <button @click="showAdd = false" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form action="{{ route('admin.livreurs.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="form-label">Nom complet *</label>
                <input type="text" name="name" class="form-input" placeholder="Jean Kouassi" required>
            </div>
            <div>
                <label class="form-label">Téléphone *</label>
                <input type="tel" name="phone" class="form-input" placeholder="+225 07 00 00 00 00" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Type de véhicule *</label>
                    <select name="vehicle_type" class="form-select" required>
                        <option value="moto">🏍️ Moto</option>
                        <option value="tricycle">🛺 Tricycle</option>
                        <option value="voiture">🚗 Voiture</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Immatriculation</label>
                    <input type="text" name="vehicle_plate" class="form-input" placeholder="AB 1234 CD">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary flex-1 justify-center">
                    <i class="fas fa-plus"></i>Ajouter
                </button>
                <button type="button" @click="showAdd = false" class="btn btn-secondary flex-1 justify-center">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Modal Modifier ── --}}
<div x-show="editId !== null" x-cloak
     class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
     @click.self="editId = null">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-7">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-gray-900 text-lg flex items-center gap-2">
                <i class="fas fa-edit text-blue-600"></i>Modifier le livreur
            </h3>
            <button @click="editId = null" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form :action="`{{ url('/admin/livreurs') }}/${editId}`" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Nom complet *</label>
                <input type="text" name="name" class="form-input" x-model="editData.name" required>
            </div>
            <div>
                <label class="form-label">Téléphone *</label>
                <input type="tel" name="phone" class="form-input" x-model="editData.phone" required>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Type de véhicule *</label>
                    <select name="vehicle_type" class="form-select" x-model="editData.vehicle_type">
                        <option value="moto">🏍️ Moto</option>
                        <option value="tricycle">🛺 Tricycle</option>
                        <option value="voiture">🚗 Voiture</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Immatriculation</label>
                    <input type="text" name="vehicle_plate" class="form-input" x-model="editData.vehicle_plate">
                </div>
            </div>
            <div>
                <label class="form-label">Statut</label>
                <select name="status" class="form-select" x-model="editData.status">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary flex-1 justify-center">
                    <i class="fas fa-save"></i>Enregistrer
                </button>
                <button type="button" @click="editId = null" class="btn btn-secondary flex-1 justify-center">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

</div>{{-- end x-data --}}

@push('scripts')
<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-xs"></i>';
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
}
</script>
@endpush
@endsection
