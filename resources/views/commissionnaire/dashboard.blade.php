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

    {{-- Retrait --}}
    <div class="card" x-data="{
        amount: '',
        phone: '',
        state: 'idle',
        error: '',
        txId: null,
        pollTimer: null,
        elapsed: 0,
        balance: {{ (float) $commissionnaire->balance }},

        async submit() {
            this.state = 'submitting'; this.error = '';
            const res = await fetch('{{ route('commissionnaire.withdraw') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ amount: this.amount, phone: this.phone }),
            });
            const data = await res.json();
            if (!res.ok) { this.state = 'error'; this.error = data.error || 'Erreur lors de la demande.'; return; }
            this.txId = data.transaction_id;
            this.balance -= parseFloat(this.amount);
            this.state = 'waiting'; this.elapsed = 0;
            this.pollTimer = setInterval(() => this.poll(), 4000);
        },

        async poll() {
            this.elapsed += 4;
            if (this.elapsed >= 120) {
                clearInterval(this.pollTimer);
                this.state = 'error';
                this.error = 'Délai dépassé. Vérifiez l\'historique des transactions.';
                return;
            }
            const res = await fetch(`/commissionnaire/retrait/statut/${this.txId}`);
            const data = await res.json();
            if (data.status === 'completed') {
                clearInterval(this.pollTimer);
                this.state = 'success';
                setTimeout(() => location.reload(), 1500);
            } else if (data.status === 'failed') {
                clearInterval(this.pollTimer);
                this.state = 'error';
                this.error = 'Retrait refusé ou échoué. Votre solde a été recrédité.';
            }
        },
    }">
        <h3 class="font-semibold text-gray-800 mb-4">Retirer mes gains</h3>

        <template x-if="state==='idle' || state==='error'">
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Montant (XOF)</label>
                        <input type="number" x-model="amount" min="1000" :max="balance"
                               placeholder="Min. 1000" class="form-input mt-1">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Numéro Mobile Money (MTN)</label>
                        <input type="tel" x-model="phone" placeholder="0500000000" class="form-input mt-1">
                    </div>
                </div>
                <p x-show="error" x-text="error" class="text-sm text-red-600 mb-2"></p>
                <button type="button" @click="submit()" :disabled="!amount || !phone || amount > balance"
                        class="btn btn-primary px-8 py-3 disabled:opacity-50">
                    <i class="fas fa-money-bill-wave mr-1"></i> Retirer
                </button>
            </div>
        </template>
        <template x-if="state==='submitting'">
            <p class="text-sm text-gray-600"><i class="fas fa-spinner fa-spin mr-1"></i> Envoi de la demande...</p>
        </template>
        <template x-if="state==='waiting'">
            <p class="text-sm text-gray-600"><i class="fas fa-spinner fa-spin mr-1"></i> Transfert en cours...</p>
        </template>
        <template x-if="state==='success'">
            <p class="text-sm text-green-600"><i class="fas fa-check-circle mr-1"></i> Retrait confirmé !</p>
        </template>
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
