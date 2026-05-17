@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="pt-4 grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Profile card --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <div class="flex items-start gap-5">
                <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-3xl font-bold shrink-0">
                    @php $n = $isManager ? ($user->owner_name ?? $user->store_name) : $user->name; @endphp
                    {{ strtoupper(substr($n, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-800">{{ $n }}</h2>
                    <span class="badge mt-1 {{ $isManager ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                        {{ $isManager ? 'Manager' : ucfirst($user->role) }}
                    </span>
                    @if($isManager)
                        <div class="mt-3 text-sm text-gray-500">
                            <i class="fas fa-store mr-1"></i> {{ $user->store_name }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Info details --}}
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Informations du compte</h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-envelope text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Email</div>
                        <div class="font-medium text-gray-700">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-phone text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Téléphone</div>
                        <div class="font-medium text-gray-700">{{ $user->phone ?: '—' }}</div>
                    </div>
                </div>
                @if($isManager)
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-map-marker-alt text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Adresse</div>
                        <div class="font-medium text-gray-700">{{ $user->address ?: '—' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-credit-card text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Abonnement</div>
                        <div class="font-medium {{ $user->hasActiveSubscription() ? 'text-green-600' : 'text-red-500' }}">
                            {{ $user->hasActiveSubscription() ? 'Actif' : 'Inactif' }}
                            @if($user->subscription_expiry)
                                <span class="text-gray-400 font-normal text-xs ml-1">jusqu'au {{ \Carbon\Carbon::parse($user->subscription_expiry)->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
                <div class="flex items-center gap-3 py-2">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-calendar text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Membre depuis</div>
                        <div class="font-medium text-gray-700">{{ $user->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions sidebar --}}
    <div class="space-y-4">
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Actions</h3>
            <div class="space-y-3">
                <a href="{{ route('profile.settings') }}" class="btn btn-primary w-full justify-center">
                    <i class="fas fa-cog"></i> Paramètres du profil
                </a>
                @if($isManager)
                <a href="{{ route('profile.delivery') }}" class="btn btn-secondary w-full justify-center">
                    <i class="fas fa-truck"></i> Livraison
                </a>
                @endif
            </div>
        </div>

        @if($isManager)
        <div class="card bg-blue-50 border-blue-100">
            <h4 class="font-medium text-blue-800 mb-2 text-sm"><i class="fas fa-info-circle mr-1"></i> Abonnement</h4>
            <p class="text-blue-700 text-xs">
                Statut: <strong>{{ $user->subscription_status === 'active' ? 'Actif' : 'Inactif' }}</strong><br>
                @if($user->subscription_expiry)
                    Expire le {{ \Carbon\Carbon::parse($user->subscription_expiry)->format('d/m/Y') }}
                @endif
            </p>
        </div>
        @endif
    </div>

</div>
@endsection
