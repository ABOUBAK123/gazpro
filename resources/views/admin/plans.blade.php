@extends('layouts.app')
@section('title', 'Formules d\'abonnement')

@section('content')
<div class="pt-4 max-w-4xl space-y-6" x-data="plansAdmin()">

    {{-- ── Formulaire d'ajout ─────────────────────────────────────── --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <i class="fas fa-plus-circle text-blue-500"></i> Nouvelle formule
        </h3>
        <form action="{{ route('admin.plans.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            @csrf
            <div class="sm:col-span-1">
                <label class="form-label">Nom</label>
                <input type="text" name="name" class="form-input" placeholder="Ex: Trimestriel" required>
            </div>
            <div>
                <label class="form-label">Prix</label>
                <input type="number" name="price" min="0" step="100" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Devise</label>
                <select name="currency" class="form-input">
                    <option value="XOF">XOF — Franc CFA</option>
                    <option value="EUR">EUR — Euro</option>
                    <option value="USD">USD — Dollar</option>
                </select>
            </div>
            <div>
                <label class="form-label">Durée (jours)</label>
                <input type="number" name="duration_days" min="1" class="form-input" placeholder="Ex: 30" required>
            </div>
            <div class="sm:col-span-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Ajouter la formule
                </button>
            </div>
        </form>
    </div>

    {{-- ── Liste des formules ─────────────────────────────────────── --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <i class="fas fa-list text-purple-500"></i> Formules existantes
        </h3>

        @if($plans->isEmpty())
            <p class="text-sm text-gray-400">Aucune formule pour l'instant.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="py-2 pr-4">Nom</th>
                        <th class="py-2 pr-4">Prix</th>
                        <th class="py-2 pr-4">Durée</th>
                        <th class="py-2 pr-4">Statut</th>
                        <th class="py-2 pr-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($plans as $plan)
                    <tr class="border-b border-gray-50" x-data="{ editing: false }">
                        <td class="py-3 pr-4">
                            <template x-if="!editing">
                                <span class="font-medium text-gray-800">{{ $plan->name }}</span>
                            </template>
                        </td>
                        <td class="py-3 pr-4">{{ number_format($plan->price, 0, ',', ' ') }} {{ $plan->currency }}</td>
                        <td class="py-3 pr-4">{{ $plan->duration_days }} jours</td>
                        <td class="py-3 pr-4">
                            @if($plan->is_active)
                                <span class="badge bg-green-100 text-green-700">Active</span>
                            @else
                                <span class="badge bg-gray-100 text-gray-500">Désactivée</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4">
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" @click="editing = true" x-show="!editing"
                                        class="text-blue-500 hover:text-blue-700" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.plans.toggle', $plan) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-amber-500 hover:text-amber-700"
                                            title="{{ $plan->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas {{ $plan->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.plans.destroy', $plan) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Supprimer cette formule ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr x-show="editing" class="border-b border-gray-50 bg-gray-50">
                        <td colspan="5" class="py-3 px-4">
                            <form action="{{ route('admin.plans.update', $plan) }}" method="POST"
                                  class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                                @csrf @method('PUT')
                                <div>
                                    <label class="form-label text-xs">Nom</label>
                                    <input type="text" name="name" value="{{ $plan->name }}" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label text-xs">Prix</label>
                                    <input type="number" name="price" value="{{ $plan->price }}" min="0" step="100" class="form-input" required>
                                </div>
                                <div>
                                    <label class="form-label text-xs">Devise</label>
                                    <select name="currency" class="form-input">
                                        @foreach(['XOF','EUR','USD'] as $cur)
                                        <option value="{{ $cur }}" {{ $plan->currency === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label text-xs">Durée (jours)</label>
                                    <input type="number" name="duration_days" value="{{ $plan->duration_days }}" min="1" class="form-input" required>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-1">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="button" @click="editing = false" class="btn bg-gray-100 text-gray-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function plansAdmin() {
    return {};
}
</script>
@endpush
@endsection
