@extends('layouts.app')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('content')
<div class="pt-4 grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <div class="flex items-start gap-5">
                <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center text-white text-3xl font-bold shrink-0 overflow-hidden">
                    @if($admin->avatar)
                        <img src="{{ asset($admin->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-800">{{ $admin->name }}</h2>
                    <span class="badge mt-1 bg-blue-100 text-blue-800">Administrateur</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Informations du compte</h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-envelope text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Email</div>
                        <div class="font-medium text-gray-700">{{ $admin->email }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 py-2 border-b border-gray-50">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-phone text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Téléphone</div>
                        <div class="font-medium text-gray-700">{{ $admin->phone ?: '—' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3 py-2">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center shrink-0">
                        <i class="fas fa-calendar text-gray-500 text-xs"></i>
                    </div>
                    <div>
                        <div class="text-gray-400 text-xs">Membre depuis</div>
                        <div class="font-medium text-gray-700">{{ $admin->created_at->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="card">
            <h3 class="font-semibold text-gray-800 mb-4">Actions</h3>
            <a href="{{ route('admin.profile.settings') }}" class="btn btn-primary w-full justify-center">
                <i class="fas fa-cog"></i> Paramètres du profil
            </a>
        </div>
    </div>

</div>
@endsection
