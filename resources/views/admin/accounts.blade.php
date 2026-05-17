@extends('layouts.app')
@section('title', 'Gestion des comptes')
@section('page-title', 'Gestion des comptes')

@section('content')
<div class="space-y-6 pt-4">

    {{-- Search --}}
    <form method="GET" action="{{ route('admin.accounts') }}" class="flex gap-3">
        <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher par nom, email, magasin..."
               class="form-input max-w-md">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
        @if($search)
            <a href="{{ route('admin.accounts') }}" class="btn btn-secondary">Effacer</a>
        @endif
    </form>

    {{-- Tabs --}}
    <div x-data="{ tab: 'stores' }">
        <div class="flex gap-1 border-b border-gray-200 mb-4">
            <button @click="tab='stores'" :class="tab==='stores' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                    class="px-4 py-2 text-sm font-medium">
                <i class="fas fa-store mr-1"></i> Magasins
                <span class="ml-1 badge bg-blue-100 text-blue-800">{{ $stores->count() }}</span>
            </button>
            <button @click="tab='staff'" :class="tab==='staff' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'"
                    class="px-4 py-2 text-sm font-medium">
                <i class="fas fa-user-tie mr-1"></i> Employés
                <span class="ml-1 badge bg-blue-100 text-blue-800">{{ $staff->count() }}</span>
            </button>
        </div>

        {{-- Stores tab --}}
        <div x-show="tab==='stores'">
            <div class="card p-0 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Magasin</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Propriétaire</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Email</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Statut</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Inscrit le</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($stores as $store)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $store->store_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $store->owner_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $store->email }}</td>
                            <td class="px-4 py-3">
                                <span class="badge
                                    {{ $store->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $store->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $store->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                ">
                                    {{ $store->status === 'active' ? 'Actif' : ($store->status === 'pending' ? 'En attente' : 'Rejeté') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $store->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button onclick="openEditStore({{ $store->id }}, '{{ addslashes($store->store_name) }}', '{{ addslashes($store->owner_name) }}', '{{ addslashes($store->email) }}', '{{ $store->phone }}', '{{ $store->status }}')"
                                            class="btn btn-secondary text-xs py-1 px-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.accounts.store.destroy', $store) }}" method="POST"
                                          onsubmit="return confirm('Supprimer ce magasin et toutes ses données ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger text-xs py-1 px-2"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun magasin trouvé</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Staff tab --}}
        <div x-show="tab==='staff'" x-cloak>
            <div class="card p-0 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Nom</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Email</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Magasin</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Rôle</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-medium">Statut</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($staff as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">{{ $employee->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $employee->email }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $employee->store->store_name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="badge bg-purple-100 text-purple-800">{{ ucfirst($employee->role) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $employee->status === 'active' ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button onclick="openEditStaff({{ $employee->id }}, '{{ addslashes($employee->name) }}', '{{ addslashes($employee->email) }}', '{{ $employee->phone }}', '{{ $employee->status }}')"
                                            class="btn btn-secondary text-xs py-1 px-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.accounts.staff.destroy', $employee) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cet employé ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger text-xs py-1 px-2"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Aucun employé trouvé</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit Store Modal --}}
<div id="editStoreModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Modifier le magasin</h3>
            <button onclick="closeModal('editStoreModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="editStoreForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nom du magasin</label>
                    <input type="text" id="es_store_name" class="form-input bg-gray-50" disabled>
                </div>
                <div>
                    <label class="form-label">Propriétaire</label>
                    <input type="text" name="owner_name" id="es_owner_name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="es_email" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" id="es_phone" class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" id="es_status" class="form-input">
                        <option value="active">Actif</option>
                        <option value="pending">En attente</option>
                        <option value="rejected">Rejeté</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn btn-primary flex-1 justify-center">Enregistrer</button>
                <button type="button" onclick="closeModal('editStoreModal')" class="btn btn-secondary flex-1 justify-center">Annuler</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Staff Modal --}}
<div id="editStaffModal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Modifier l'employé</h3>
            <button onclick="closeModal('editStaffModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="editStaffForm" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" id="ef_name" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="ef_email" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" id="ef_phone" class="form-input">
                </div>
                <div>
                    <label class="form-label">Statut</label>
                    <select name="status" id="ef_status" class="form-input">
                        <option value="active">Actif</option>
                        <option value="inactive">Inactif</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn btn-primary flex-1 justify-center">Enregistrer</button>
                <button type="button" onclick="closeModal('editStaffModal')" class="btn btn-secondary flex-1 justify-center">Annuler</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditStore(id, storeName, ownerName, email, phone, status) {
    document.getElementById('editStoreForm').action = '{{ url('/admin/comptes/stores') }}/' + id;
    document.getElementById('es_store_name').value = storeName;
    document.getElementById('es_owner_name').value = ownerName;
    document.getElementById('es_email').value = email;
    document.getElementById('es_phone').value = phone;
    document.getElementById('es_status').value = status;
    document.getElementById('editStoreModal').classList.remove('hidden');
}
function openEditStaff(id, name, email, phone, status) {
    document.getElementById('editStaffForm').action = '{{ url('/admin/comptes/staff') }}/' + id;
    document.getElementById('ef_name').value = name;
    document.getElementById('ef_email').value = email;
    document.getElementById('ef_phone').value = phone;
    document.getElementById('ef_status').value = status;
    document.getElementById('editStaffModal').classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>
@endpush
@endsection
