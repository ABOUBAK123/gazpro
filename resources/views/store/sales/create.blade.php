@extends('layouts.app')
@section('title', 'Nouvelle vente')
@section('page-title', 'Enregistrer une vente')

@section('content')
<div class="pt-4 max-w-2xl">
    <div class="card">
        <form action="{{ route('sales.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom du client *</label>
                    <input type="text" name="client_name" class="form-input" required value="{{ old('client_name') }}" list="clients-list">
                    <datalist id="clients-list">
                        @foreach($clients as $client)
                            <option value="{{ $client->name }}">
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="client_phone" class="form-input" value="{{ old('client_phone') }}" placeholder="+225...">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Marque *</label>
                    <select name="brand" id="brand-sel" class="form-input" required onchange="updatePriceSale()">
                        <option value="">-- Sélectionner --</option>
                        @foreach($stocks->pluck('brand')->unique() as $brand)
                            <option value="{{ $brand }}" {{ old('brand') === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Poids *</label>
                    <select name="weight" id="weight-sel" class="form-input" required onchange="updatePriceSale()">
                        <option value="">-- Sélectionner --</option>
                        @foreach($stocks->pluck('weight')->unique() as $weight)
                            <option value="{{ $weight }}" {{ old('weight') === $weight ? 'selected' : '' }}>{{ $weight }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Quantité *</label>
                    <input type="number" name="quantity" id="s-qty" min="1" class="form-input" required value="{{ old('quantity', 1) }}" onchange="calcSaleTotal()">
                </div>
                <div>
                    <label class="form-label">Prix unitaire *</label>
                    <input type="number" name="unit_price" id="s-price" min="0" step="50" class="form-input" required value="{{ old('unit_price', 0) }}" onchange="calcSaleTotal()">
                </div>
                <div>
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-input">
                        <option value="XOF">XOF</option><option value="EUR">EUR</option><option value="USD">USD</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Date de vente *</label>
                <input type="date" name="sale_date" class="form-input" required value="{{ old('sale_date', date('Y-m-d')) }}">
            </div>

            <div class="bg-green-50 rounded-lg p-4 flex justify-between items-center">
                <span class="text-gray-700 font-medium">Montant total</span>
                <span id="s-total" class="text-2xl font-bold text-green-700">0 XOF</span>
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea name="description" class="form-input" rows="2" placeholder="Remarques...">{{ old('description') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-success flex-1 justify-center">
                    <i class="fas fa-receipt"></i> Enregistrer la vente
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary flex-1 justify-center">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const sStock = @json($stocks);
function updatePriceSale() {
    const b = document.getElementById('brand-sel').value;
    const w = document.getElementById('weight-sel').value;
    const item = sStock.find(s => s.brand === b && s.weight === w);
    if (item) { document.getElementById('s-price').value = item.unit_price; calcSaleTotal(); }
}
function calcSaleTotal() {
    const qty = parseInt(document.getElementById('s-qty').value) || 0;
    const price = parseFloat(document.getElementById('s-price').value) || 0;
    document.getElementById('s-total').textContent = new Intl.NumberFormat('fr-FR').format(qty * price) + ' XOF';
}
</script>
@endpush
@endsection
