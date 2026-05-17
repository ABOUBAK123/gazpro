@extends('layouts.app')
@section('title', 'Paramètres de livraison')
@section('page-title', 'Paramètres de livraison')

@section('content')
<div class="pt-4 max-w-xl">
    <div class="mb-4">
        <a href="{{ route('profile.index') }}" class="text-blue-600 text-sm hover:underline">
            <i class="fas fa-arrow-left mr-1"></i> Retour au profil
        </a>
    </div>

    <div class="card">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-truck text-blue-600"></i>
            </div>
            <div>
                <h3 class="font-semibold text-gray-800">Livraison à domicile</h3>
                <p class="text-gray-400 text-xs">Configurez les tarifs de livraison pour votre magasin</p>
            </div>
        </div>

        <form action="{{ route('profile.delivery.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="form-label">Prix de livraison par bouteille (XOF)</label>
                    <input type="number" name="delivery_price" class="form-input"
                           value="{{ old('delivery_price', $delivery['price'] ?? 0) }}" min="0" required>
                    <p class="text-xs text-gray-400 mt-1">Montant facturé par bouteille livrée</p>
                </div>
                <div>
                    <label class="form-label">Seuil de livraison gratuite (nb de bouteilles)</label>
                    <input type="number" name="free_threshold" class="form-input"
                           value="{{ old('free_threshold', $delivery['threshold'] ?? 0) }}" min="0" required>
                    <p class="text-xs text-gray-400 mt-1">Au-delà de ce nombre, la livraison est offerte (0 = jamais gratuite)</p>
                </div>
            </div>

            {{-- Example calculation --}}
            <div class="mt-5 bg-gray-50 rounded-xl p-4 text-sm" id="delivery-example">
                <div class="font-medium text-gray-700 mb-2"><i class="fas fa-calculator mr-1"></i> Exemple de calcul</div>
                <div class="text-gray-600 space-y-1" id="example-lines">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Enregistrer
                </button>
                <a href="{{ route('profile.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function updateExample() {
    const price = parseFloat(document.querySelector('[name=delivery_price]').value) || 0;
    const threshold = parseInt(document.querySelector('[name=free_threshold]').value) || 0;
    const el = document.getElementById('example-lines');

    const examples = [2, 5, threshold > 0 ? threshold + 1 : 10];
    let html = '';
    examples.forEach(qty => {
        const isFree = threshold > 0 && qty > threshold;
        const cost = isFree ? 0 : qty * price;
        html += `<div class="flex justify-between">
            <span>${qty} bouteilles</span>
            <span class="${isFree ? 'text-green-600 font-medium' : 'text-gray-700'}">
                ${isFree ? 'Livraison offerte ✓' : cost.toLocaleString('fr-FR') + ' XOF'}
            </span>
        </div>`;
    });
    el.innerHTML = html;
}

document.querySelector('[name=delivery_price]').addEventListener('input', updateExample);
document.querySelector('[name=free_threshold]').addEventListener('input', updateExample);
updateExample();
</script>
@endpush
@endsection
