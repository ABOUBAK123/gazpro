@extends('layouts.app')
@section('title', 'Gestion du stock')
@section('page-title', 'Gestion du stock')

@section('content')
<div class="space-y-6 pt-4">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Add stock form --}}
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">
                <i class="fas fa-plus text-blue-600 mr-2"></i>Ajouter / Réapprovisionner
            </h3>
            <form action="{{ route('stock.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Marque *</label>
                    <input type="text" name="brand" list="brands" class="form-input" placeholder="Total, Shell, Oryx..." required value="{{ old('brand') }}">
                    <datalist id="brands">
                        @foreach($stocks->pluck('brand')->unique() as $b)
                            <option value="{{ $b }}">
                        @endforeach
                        <option value="Total"><option value="Shell"><option value="Oryx"><option value="Kobil">
                    </datalist>
                </div>
                <div>
                    <label class="form-label">Poids / Type *</label>
                    <input type="text" name="weight" list="weights" class="form-input" placeholder="6kg, 12kg, 25kg..." required value="{{ old('weight') }}">
                    <datalist id="weights">
                        @foreach($stocks->pluck('weight')->unique() as $w)
                            <option value="{{ $w }}">
                        @endforeach
                        <option value="3kg"><option value="6kg"><option value="12kg"><option value="25kg"><option value="38kg">
                    </datalist>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Quantité *</label>
                        <input type="number" name="quantity" min="0" class="form-input" placeholder="0" required value="{{ old('quantity', 0) }}">
                    </div>
                    <div>
                        <label class="form-label">Prix unitaire *</label>
                        <input type="number" name="unit_price" min="0" step="50" class="form-input" placeholder="0" required value="{{ old('unit_price', 0) }}">
                    </div>
                </div>
                <div>
                    <label class="form-label">Seuil d'alerte</label>
                    <input type="number" name="alert_threshold" min="0" class="form-input" placeholder="5" value="{{ old('alert_threshold', 5) }}">
                    <p class="text-xs text-gray-400 mt-1">Alerte quand le stock descend sous ce seuil</p>
                </div>
                <button type="submit" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-plus"></i> Ajouter au stock
                </button>
            </form>
        </div>

        {{-- Stock list --}}
        <div class="lg:col-span-2 card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Inventaire actuel</h3>
                <div class="flex gap-3 text-xs">
                    <span class="badge bg-green-100 text-green-800">Normal</span>
                    <span class="badge bg-orange-100 text-orange-800">Faible</span>
                    <span class="badge bg-red-100 text-red-800">Critique</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200">
                            <th class="pb-3 font-semibold text-gray-600">Marque</th>
                            <th class="pb-3 font-semibold text-gray-600">Poids</th>
                            <th class="pb-3 font-semibold text-gray-600">Qté</th>
                            <th class="pb-3 font-semibold text-gray-600">Prix unit.</th>
                            <th class="pb-3 font-semibold text-gray-600">Valeur</th>
                            <th class="pb-3 font-semibold text-gray-600">Statut</th>
                            <th class="pb-3 font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($stocks as $item)
                            @php $status = $item->getStatus(); @endphp
                            <tr class="{{ $status === 'critical' ? 'bg-red-50' : ($status === 'low' ? 'bg-orange-50' : '') }}">
                                <td class="py-3 font-medium text-gray-800">{{ $item->brand }}</td>
                                <td class="py-3 text-gray-600">{{ $item->weight }}</td>
                                <td class="py-3 font-bold {{ $status === 'critical' ? 'text-red-600' : ($status === 'low' ? 'text-orange-600' : 'text-green-600') }}">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-3 text-gray-600">{{ number_format($item->unit_price, 0, ',', ' ') }}</td>
                                <td class="py-3 text-gray-600">{{ number_format($item->total_value, 0, ',', ' ') }}</td>
                                <td class="py-3">
                                    @if($status === 'out')
                                        <span class="badge bg-gray-100 text-gray-600">Épuisé</span>
                                    @elseif($status === 'critical')
                                        <span class="badge bg-red-100 text-red-800"><i class="fas fa-exclamation-triangle text-xs mr-1"></i>Critique</span>
                                    @elseif($status === 'low')
                                        <span class="badge bg-orange-100 text-orange-800">Faible</span>
                                    @else
                                        <span class="badge bg-green-100 text-green-800">Normal</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <div class="flex gap-2">
                                        <button onclick="editStock({{ $item->id }}, {{ $item->quantity }}, {{ $item->unit_price }}, {{ $item->alert_threshold }})"
                                                class="btn btn-warning text-xs py-1 px-2"><i class="fas fa-edit"></i></button>
                                        <form action="{{ route('stock.destroy', $item) }}" method="POST"
                                              onsubmit="return confirm('Supprimer cet article ?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger text-xs py-1 px-2"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-12 text-center text-gray-500">
                                <i class="fas fa-boxes text-4xl text-gray-200 block mb-3"></i>
                                Aucun article en stock. Commencez par ajouter des articles.
                            </td></tr>
                        @endforelse
                    </tbody>
                    @if($stocks->count() > 0)
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 bg-gray-50">
                                <td colspan="4" class="py-3 px-0 font-semibold text-gray-700">Total stock</td>
                                <td class="py-3 font-bold text-gray-800">{{ number_format($stocks->sum('total_value'), 0, ',', ' ') }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Edit modal --}}
<div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm shadow-2xl">
        <h3 class="font-semibold text-gray-800 mb-4">Modifier le stock</h3>
        <form id="edit-form" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Quantité</label>
                <input type="number" name="quantity" id="edit-qty" min="0" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Prix unitaire</label>
                <input type="number" name="unit_price" id="edit-price" min="0" step="50" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Seuil d'alerte</label>
                <input type="number" name="alert_threshold" id="edit-threshold" min="0" class="form-input" required>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary flex-1 justify-center">Enregistrer</button>
                <button type="button" onclick="closeModal()" class="btn btn-secondary flex-1 justify-center">Annuler</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editStock(id, qty, price, threshold) {
    document.getElementById('edit-form').action = `{{ url('/stock') }}/${id}`;
    document.getElementById('edit-qty').value = qty;
    document.getElementById('edit-price').value = price;
    document.getElementById('edit-threshold').value = threshold;
    document.getElementById('edit-modal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('edit-modal').classList.add('hidden');
}
</script>
@endpush
@endsection
