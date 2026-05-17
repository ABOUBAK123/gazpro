@extends('layouts.app')
@section('title', 'Nouvelle commande')
@section('page-title', 'Créer une commande')

@section('content')
<div class="pt-4 max-w-2xl">
    <div class="card">
        <form action="{{ route('orders.store') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nom du client *</label>
                    <input type="text" name="client_name" class="form-input" required value="{{ old('client_name') }}" placeholder="Nom complet">
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="client_phone" class="form-input" value="{{ old('client_phone') }}" placeholder="+225...">
                </div>
            </div>

            <div>
                <label class="form-label">Adresse de livraison</label>
                <input type="text" name="client_address" class="form-input" value="{{ old('client_address') }}" placeholder="Adresse...">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Marque *</label>
                    <select name="brand" id="brand-select" class="form-input" required onchange="updatePrice()">
                        <option value="">-- Sélectionner --</option>
                        @foreach($stocks->pluck('brand')->unique() as $brand)
                            <option value="{{ $brand }}" {{ old('brand') === $brand ? 'selected' : '' }}>{{ $brand }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Poids *</label>
                    <select name="weight" id="weight-select" class="form-input" required onchange="updatePrice()">
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
                    <input type="number" name="quantity" id="qty" min="1" class="form-input" required value="{{ old('quantity', 1) }}" onchange="calcTotal()">
                </div>
                <div>
                    <label class="form-label">Prix unitaire *</label>
                    <input type="number" name="unit_price" id="unit-price" min="0" step="50" class="form-input" required value="{{ old('unit_price', 0) }}" onchange="calcTotal()">
                </div>
                <div>
                    <label class="form-label">Devise</label>
                    <select name="currency" class="form-input">
                        <option value="XOF" {{ old('currency', 'XOF') === 'XOF' ? 'selected' : '' }}>XOF</option>
                        <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                    </select>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4 flex justify-between items-center">
                <span class="text-gray-700 font-medium">Total</span>
                <span id="total-display" class="text-2xl font-bold text-blue-700">0 XOF</span>
            </div>

            <div>
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Instructions spéciales...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary flex-1 justify-center">
                    <i class="fas fa-shopping-cart"></i> Créer la commande
                </button>
                <a href="{{ route('orders.index') }}" class="btn btn-secondary flex-1 justify-center">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const stockData = @json($stocks);

function updatePrice() {
    const brand = document.getElementById('brand-select').value;
    const weight = document.getElementById('weight-select').value;
    if (brand && weight) {
        const item = stockData.find(s => s.brand === brand && s.weight === weight);
        if (item) {
            document.getElementById('unit-price').value = item.unit_price;
            calcTotal();
        }
    }
}

function calcTotal() {
    const qty = parseInt(document.getElementById('qty').value) || 0;
    const price = parseFloat(document.getElementById('unit-price').value) || 0;
    const total = qty * price;
    document.getElementById('total-display').textContent = new Intl.NumberFormat('fr-FR').format(total) + ' XOF';
}
</script>
@endpush
@endsection
