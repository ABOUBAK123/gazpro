@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Mon tableau de bord commissionnaire')

@section('content')
<div class="pt-4 space-y-6 max-w-5xl">

    {{-- Code de parrainage --}}
    <div class="card bg-blue-50 border border-blue-100">
        <p class="text-xs font-semibold text-blue-700 uppercase tracking-wider mb-1">Votre code de parrainage</p>
        <div class="flex items-center gap-3">
            <span class="text-2xl font-black text-blue-900 tracking-widest">{{ $commissionnaire->code }}</span>
            <button type="button" onclick="navigator.clipboard.writeText('{{ $commissionnaire->code }}')"
                    class="text-blue-500 hover:text-blue-700" title="Copier">
                <i class="fas fa-copy"></i>
            </button>
        </div>
        <p class="text-xs text-blue-600 mt-2">Partagez ce code : les magasins qui l'utilisent à l'inscription vous rapportent 3% de leurs abonnements.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Solde disponible</p>
            <p class="text-2xl font-black text-green-600">{{ number_format($commissionnaire->balance, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">XOF</span></p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Magasins parrainés</p>
            <p class="text-2xl font-black text-gray-800">{{ $storesCount }}</p>
        </div>
        <div class="card">
            <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total gagné</p>
            <p class="text-2xl font-black text-gray-800">{{ number_format($totalEarned, 0, ',', ' ') }} <span class="text-sm font-normal text-gray-400">XOF</span></p>
        </div>
    </div>

    {{-- Dernières transactions --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-4">Dernières transactions</h3>
        @if($recent->isEmpty())
            <p class="text-sm text-gray-400">Aucune transaction pour l'instant.</p>
        @else
        <div class="space-y-2">
            @foreach($recent as $tx)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 text-sm">
                <div>
                    <span class="font-medium {{ $tx->type === 'credit' ? 'text-green-700' : 'text-amber-700' }}">
                        {{ $tx->type === 'credit' ? 'Commission' : 'Retrait' }}
                    </span>
                    <span class="text-gray-400 text-xs ml-2">{{ $tx->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <span class="font-semibold {{ $tx->type === 'credit' ? 'text-green-700' : 'text-gray-700' }}">
                    {{ $tx->type === 'credit' ? '+' : '-' }}{{ number_format($tx->amount, 0, ',', ' ') }} XOF
                </span>
            </div>
            @endforeach
        </div>
        <a href="{{ route('commissionnaire.transactions') }}" class="text-blue-600 text-sm hover:underline mt-3 inline-block">
            Voir tout l'historique →
        </a>
        @endif
    </div>

</div>
@endsection
