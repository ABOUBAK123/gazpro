@extends('layouts.app')
@section('title', 'Dépenses')
@section('page-title', 'Gestion des dépenses')

@section('content')
<div class="space-y-6 pt-4">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Add form --}}
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-plus text-red-500 mr-2"></i>Ajouter une dépense</h3>
            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Type *</label>
                    <select name="type" class="form-input" required>
                        <option value="electricity">Électricité</option>
                        <option value="water">Eau</option>
                        <option value="rent">Loyer</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="salary">Salaire</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Montant *</label>
                    <input type="number" name="amount" min="0" step="100" class="form-input" required placeholder="0">
                </div>
                <div>
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-input">
                        <option value="XOF">XOF</option><option value="EUR">EUR</option><option value="USD">USD</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Date *</label>
                    <input type="date" name="expense_date" class="form-input" required value="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-input" placeholder="Détails...">
                </div>
                <button type="submit" class="btn btn-danger w-full justify-center">
                    <i class="fas fa-plus"></i> Ajouter
                </button>
            </form>
        </div>

        {{-- List + Filters --}}
        <div class="lg:col-span-2 space-y-4">

            <form class="card flex gap-3 items-end" method="GET">
                <div class="flex-1">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-input" onchange="this.form.submit()">
                        <option value="">Tous</option>
                        <option value="electricity" {{ request('type') === 'electricity' ? 'selected' : '' }}>Électricité</option>
                        <option value="water" {{ request('type') === 'water' ? 'selected' : '' }}>Eau</option>
                        <option value="rent" {{ request('type') === 'rent' ? 'selected' : '' }}>Loyer</option>
                        <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        <option value="salary" {{ request('type') === 'salary' ? 'selected' : '' }}>Salaire</option>
                        <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Autre</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Du</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
                </div>
                <div>
                    <label class="form-label">Au</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
                <a href="{{ route('expenses.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i></a>
            </form>

            {{-- Total --}}
            <div class="bg-red-50 border border-red-100 rounded-xl p-4 flex justify-between items-center">
                <span class="text-gray-700 font-medium">Total dépenses</span>
                <span class="text-xl font-bold text-red-700">{{ number_format($total, 0, ',', ' ') }} XOF</span>
            </div>

            <div class="card p-0 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr class="text-left">
                            <th class="px-5 py-3 font-semibold text-gray-600">Date</th>
                            <th class="px-5 py-3 font-semibold text-gray-600">Type</th>
                            <th class="px-5 py-3 font-semibold text-gray-600">Description</th>
                            <th class="px-5 py-3 font-semibold text-gray-600">Montant</th>
                            <th class="px-5 py-3 font-semibold text-gray-600"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($expenses as $expense)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-gray-600">{{ $expense->expense_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3">
                                    <span class="badge bg-gray-100 text-gray-700">{{ $expense->type_label }}</span>
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $expense->description ?? '-' }}</td>
                                <td class="px-5 py-3 font-semibold text-red-700">{{ number_format($expense->amount, 0, ',', ' ') }} {{ $expense->currency }}</td>
                                <td class="px-5 py-3">
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cette dépense ?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger text-xs py-1 px-2"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-gray-500">
                                <i class="fas fa-wallet text-4xl text-gray-200 block mb-3"></i>
                                Aucune dépense trouvée
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-5 py-3 border-t">{{ $expenses->withQueryString()->links() }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
