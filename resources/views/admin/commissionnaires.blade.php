@extends('layouts.app')
@section('title', 'Commissionnaires')
@section('page-title', 'Gestion des commissionnaires')

@section('content')
<div class="space-y-6 pt-4">

    <div class="flex justify-end">
        <a href="{{ route('admin.commissions') }}" class="text-blue-600 text-sm hover:underline">
            <i class="fas fa-list mr-1"></i> Voir l'historique des transferts
        </a>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-gray-100 rounded-lg p-1 w-fit">
        <button onclick="showTab('pending')" id="tab-pending" class="px-4 py-2 rounded-md text-sm font-medium transition tab-btn active-tab">
            En attente <span class="ml-1 bg-yellow-400 text-yellow-900 rounded-full px-1.5 text-xs">{{ $pending->count() }}</span>
        </button>
        <button onclick="showTab('active')" id="tab-active" class="px-4 py-2 rounded-md text-sm font-medium transition tab-btn">
            Actifs <span class="ml-1 bg-green-400 text-white rounded-full px-1.5 text-xs">{{ $active->count() }}</span>
        </button>
        <button onclick="showTab('rejected')" id="tab-rejected" class="px-4 py-2 rounded-md text-sm font-medium transition tab-btn">
            Rejetés <span class="ml-1 bg-red-400 text-white rounded-full px-1.5 text-xs">{{ $rejected->count() }}</span>
        </button>
    </div>

    {{-- Pending --}}
    <div id="panel-pending" class="tab-panel">
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Inscriptions en attente de validation</h3>
            @forelse($pending as $c)
                <div class="border border-yellow-200 rounded-lg p-4 mb-3 bg-yellow-50">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <div class="font-semibold text-gray-800">{{ $c->name }}</div>
                            <div class="text-sm text-gray-600"><i class="fas fa-envelope w-4"></i> {{ $c->email }}</div>
                            <div class="text-sm text-gray-600"><i class="fas fa-phone w-4"></i> {{ $c->phone }}</div>
                            <div class="text-xs text-gray-400">Inscrit le {{ $c->created_at->format('d/m/Y à H:i') }}</div>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('admin.commissionnaires.approve', $c) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="btn btn-success"><i class="fas fa-check"></i> Approuver</button>
                            </form>
                            <form action="{{ route('admin.commissionnaires.reject', $c) }}" method="POST"
                                  onsubmit="return confirm('Rejeter ce commissionnaire ?')">
                                @csrf @method('PATCH')
                                <button class="btn btn-danger"><i class="fas fa-times"></i> Rejeter</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-check-circle text-4xl text-green-300 mb-3 block"></i>
                    Aucune inscription en attente
                </div>
            @endforelse
        </div>
    </div>

    {{-- Active --}}
    <div id="panel-active" class="tab-panel hidden">
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Commissionnaires actifs</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200">
                            <th class="pb-3 font-semibold text-gray-600">Nom</th>
                            <th class="pb-3 font-semibold text-gray-600">Contact</th>
                            <th class="pb-3 font-semibold text-gray-600">Code</th>
                            <th class="pb-3 font-semibold text-gray-600">Filleuls</th>
                            <th class="pb-3 font-semibold text-gray-600">Solde</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($active as $c)
                            <tr>
                                <td class="py-3 font-medium text-gray-800">{{ $c->name }}</td>
                                <td class="py-3 text-gray-600">{{ $c->phone }}<br><span class="text-xs text-gray-400">{{ $c->email }}</span></td>
                                <td class="py-3"><span class="font-mono text-xs bg-gray-100 rounded px-2 py-1">{{ $c->code }}</span></td>
                                <td class="py-3 text-gray-600">{{ $c->stores_count }}</td>
                                <td class="py-3 font-semibold text-green-700">{{ number_format($c->balance, 0, ',', ' ') }} XOF</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-gray-500">Aucun commissionnaire actif</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Rejected --}}
    <div id="panel-rejected" class="tab-panel hidden">
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Inscriptions rejetées</h3>
            @forelse($rejected as $c)
                <div class="border border-red-200 rounded-lg p-4 mb-3 bg-red-50">
                    <div class="font-semibold text-gray-800">{{ $c->name }}</div>
                    <div class="text-sm text-gray-600 mt-1">{{ $c->phone }} · {{ $c->email }}</div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-xs text-gray-400">Rejeté</span>
                        <form action="{{ route('admin.commissionnaires.approve', $c) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-success text-xs py-1"><i class="fas fa-undo"></i> Réactiver</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-8">Aucune inscription rejetée</p>
            @endforelse
        </div>
    </div>

</div>

@push('scripts')
<script>
function showTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('bg-white', 'shadow-sm', 'text-blue-700');
        b.classList.add('text-gray-600');
    });
    document.getElementById('panel-' + name).classList.remove('hidden');
    const btn = document.getElementById('tab-' + name);
    btn.classList.add('bg-white', 'shadow-sm', 'text-blue-700');
    btn.classList.remove('text-gray-600');
}
showTab('pending');
</script>
@endpush
@endsection
