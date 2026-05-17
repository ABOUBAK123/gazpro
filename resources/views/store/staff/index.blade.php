@extends('layouts.app')
@section('title', 'Personnel')
@section('page-title', 'Gestion du personnel')

@section('content')
<div class="space-y-6 pt-4">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Add form --}}
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-user-plus text-blue-600 mr-2"></i>Ajouter un employé
            </h3>
            <form action="{{ route('staff.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Nom complet *</label>
                    <input type="text" name="name" class="form-input" required value="{{ old('name') }}" placeholder="Prénom Nom">
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-input" required value="{{ old('email') }}" placeholder="email@exemple.com">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone') }}" placeholder="+225...">
                </div>
                <div>
                    <label class="form-label">Rôle *</label>
                    <select name="role" class="form-input" required>
                        <option value="employee" {{ old('role') === 'employee' ? 'selected' : '' }}>Employé</option>
                        <option value="cashier" {{ old('role') === 'cashier' ? 'selected' : '' }}>Caissier</option>
                        <option value="supervisor" {{ old('role') === 'supervisor' ? 'selected' : '' }}>Superviseur</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" class="form-input" required placeholder="Minimum 6 caractères">
                </div>
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
            </form>
        </div>

        {{-- Staff list --}}
        <div class="lg:col-span-2 card">
            <h3 class="font-semibold text-gray-800 mb-4">Équipe ({{ $staff->count() }} membres)</h3>
            <div class="space-y-3">
                @forelse($staff as $member)
                    <div class="border border-gray-100 rounded-xl p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center font-bold text-blue-700">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-800">{{ $member->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $member->email }}</div>
                                    @if($member->phone)
                                        <div class="text-xs text-gray-400">{{ $member->phone }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="badge {{ $member->role === 'supervisor' ? 'bg-purple-100 text-purple-800' : ($member->role === 'cashier' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $member->role === 'supervisor' ? 'Superviseur' : ($member->role === 'cashier' ? 'Caissier' : 'Employé') }}
                                </span>
                                <span class="badge {{ $member->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $member->status === 'active' ? 'Actif' : 'Inactif' }}
                                </span>
                                <button onclick="toggleEdit({{ $member->id }})" class="btn btn-warning text-xs py-1 px-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('staff.destroy', $member) }}" method="POST"
                                      onsubmit="return confirm('Supprimer cet employé ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger text-xs py-1 px-2"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>

                        {{-- Inline edit form --}}
                        <div id="edit-{{ $member->id }}" class="hidden mt-4 pt-4 border-t border-gray-100">
                            <form action="{{ route('staff.update', $member) }}" method="POST" class="grid grid-cols-2 gap-3">
                                @csrf @method('PUT')
                                <div>
                                    <label class="form-label text-xs">Nom</label>
                                    <input type="text" name="name" value="{{ $member->name }}" class="form-input text-sm" required>
                                </div>
                                <div>
                                    <label class="form-label text-xs">Téléphone</label>
                                    <input type="text" name="phone" value="{{ $member->phone }}" class="form-input text-sm">
                                </div>
                                <div>
                                    <label class="form-label text-xs">Rôle</label>
                                    <select name="role" class="form-input text-sm">
                                        <option value="employee" {{ $member->role === 'employee' ? 'selected' : '' }}>Employé</option>
                                        <option value="cashier" {{ $member->role === 'cashier' ? 'selected' : '' }}>Caissier</option>
                                        <option value="supervisor" {{ $member->role === 'supervisor' ? 'selected' : '' }}>Superviseur</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label text-xs">Statut</label>
                                    <select name="status" class="form-input text-sm">
                                        <option value="active" {{ $member->status === 'active' ? 'selected' : '' }}>Actif</option>
                                        <option value="inactive" {{ $member->status === 'inactive' ? 'selected' : '' }}>Inactif</option>
                                    </select>
                                </div>
                                <div class="col-span-2">
                                    <label class="form-label text-xs">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                                    <input type="password" name="password" class="form-input text-sm" placeholder="Nouveau mot de passe">
                                </div>
                                <div class="col-span-2 flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-1 justify-center text-sm">Enregistrer</button>
                                    <button type="button" onclick="toggleEdit({{ $member->id }})" class="btn btn-secondary flex-1 justify-center text-sm">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-users text-4xl text-gray-200 block mb-3"></i>
                        Aucun employé. Ajoutez votre premier membre d'équipe.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.classList.toggle('hidden');
}
</script>
@endpush
@endsection
