@extends('layouts.app')
@section('title', 'Paramètres du profil')
@section('page-title', 'Paramètres du profil')

@section('content')
<div class="pt-4 max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('profile.index') }}" class="text-blue-600 text-sm hover:underline">
            <i class="fas fa-arrow-left mr-1"></i> Retour au profil
        </a>
    </div>

    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-5">Modifier mes informations</h3>
        <form action="{{ route('profile.update') }}" method="POST">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="form-label">{{ $isManager ? 'Nom du propriétaire' : 'Nom complet' }}</label>
                    <input type="text" name="name" class="form-input"
                           value="{{ old('name', $isManager ? $user->owner_name : $user->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input"
                           value="{{ old('email', $user->email) }}" required>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" class="form-input"
                           value="{{ old('phone', $user->phone) }}" placeholder="+225 00 00 00 00">
                </div>
                <hr class="border-gray-100">
                <div>
                    <label class="form-label">Nouveau mot de passe <span class="text-gray-400 font-normal">(laisser vide pour ne pas changer)</span></label>
                    <input type="password" name="password" class="form-input" placeholder="••••••••" autocomplete="new-password">
                </div>
                <div>
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="••••••••">
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
@endsection
