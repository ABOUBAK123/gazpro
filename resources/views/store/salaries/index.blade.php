@extends('layouts.app')
@section('title', 'Salaires')
@section('page-title', 'Gestion des salaires')

@section('content')
<div class="space-y-6 pt-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Add form --}}
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-money-bill-wave text-green-600 mr-2"></i>Ajouter un salaire</h3>
            <form action="{{ route('salaries.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Employé *</label>
                    <select name="staff_id" class="form-input" onchange="fillName(this)">
                        <option value="">-- Choisir --</option>
                        @foreach($staff as $member)
                            <option value="{{ $member->id }}" data-name="{{ $member->name }}">{{ $member->name }}</option>
                        @endforeach
                        <option value="">Autre (saisir manuellement)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Nom de l'employé *</label>
                    <input type="text" name="employee_name" id="emp-name" class="form-input" required placeholder="Nom complet">
                </div>
                <div>
                    <label class="form-label">Mois *</label>
                    <input type="month" name="month" class="form-input" required value="{{ date('Y-m') }}">
                </div>
                <div>
                    <label class="form-label">Salaire de base *</label>
                    <input type="number" name="base_amount" min="0" step="100" class="form-input" required placeholder="0" onchange="calcSalaryTotal()">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Prime</label>
                        <input type="number" name="bonus" id="bonus" min="0" step="100" class="form-input" placeholder="0" value="0" onchange="calcSalaryTotal()">
                    </div>
                    <div>
                        <label class="form-label">Retenues</label>
                        <input type="number" name="deductions" id="deductions" min="0" step="100" class="form-input" placeholder="0" value="0" onchange="calcSalaryTotal()">
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-3 flex justify-between">
                    <span class="text-sm text-gray-700">Total net</span>
                    <span id="salary-total" class="font-bold text-green-700">0 XOF</span>
                </div>
                <div>
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-input">
                        <option value="XOF">XOF</option><option value="EUR">EUR</option><option value="USD">USD</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-plus"></i> Enregistrer
                </button>
            </form>
        </div>

        {{-- Salaries list --}}
        <div class="lg:col-span-2 card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Historique des salaires</h3>
                <form method="GET" class="flex gap-2">
                    <input type="month" name="month" value="{{ request('month') }}" class="form-input text-sm">
                    <button type="submit" class="btn btn-secondary text-sm"><i class="fas fa-filter"></i></button>
                </form>
            </div>
            <div class="space-y-3">
                @forelse($salaries as $salary)
                    <div class="border border-gray-100 rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <div class="font-medium text-gray-800">{{ $salary->employee_name }}</div>
                            <div class="text-xs text-gray-500">{{ $salary->month }}</div>
                            <div class="text-xs text-gray-400">
                                Base: {{ number_format($salary->base_amount, 0, ',', ' ') }}
                                @if($salary->bonus > 0) + Prime: {{ number_format($salary->bonus, 0, ',', ' ') }} @endif
                                @if($salary->deductions > 0) - Ret: {{ number_format($salary->deductions, 0, ',', ' ') }} @endif
                            </div>
                        </div>
                        <div class="text-right flex items-center gap-3">
                            <div>
                                <div class="font-bold text-gray-800">{{ number_format($salary->total_amount, 0, ',', ' ') }} {{ $salary->currency }}</div>
                                <span class="badge {{ $salary->status === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $salary->status_label }}
                                </span>
                            </div>
                            @if($salary->status === 'pending')
                                <form action="{{ route('salaries.markPaid', $salary) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-success text-xs py-1"><i class="fas fa-check"></i> Payé</button>
                                </form>
                            @endif
                            <form action="{{ route('salaries.destroy', $salary) }}" method="POST"
                                  onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger text-xs py-1 px-2"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <i class="fas fa-money-bill-wave text-4xl text-gray-200 block mb-3"></i>
                        Aucun salaire enregistré
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function fillName(sel) {
    const opt = sel.selectedOptions[0];
    document.getElementById('emp-name').value = opt.dataset.name || '';
}
function calcSalaryTotal() {
    const base = parseFloat(document.querySelector('[name=base_amount]')?.value) || 0;
    const bonus = parseFloat(document.getElementById('bonus').value) || 0;
    const ded = parseFloat(document.getElementById('deductions').value) || 0;
    document.getElementById('salary-total').textContent = new Intl.NumberFormat('fr-FR').format(base + bonus - ded) + ' XOF';
}
</script>
@endpush
@endsection
