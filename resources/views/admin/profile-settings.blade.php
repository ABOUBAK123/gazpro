@extends('layouts.app')
@section('title', 'Paramètres du profil')
@section('page-title', 'Paramètres du profil')

@section('content')
<div class="pt-4 max-w-2xl">
    <div class="mb-4">
        <a href="{{ route('admin.profile.index') }}" class="text-blue-600 text-sm hover:underline">
            <i class="fas fa-arrow-left mr-1"></i> Retour au profil
        </a>
    </div>

    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-5">Modifier mes informations</h3>
        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data"
              x-data="{ preview: '{{ $admin->avatar ? asset($admin->avatar) : '' }}' }">
            @csrf @method('PUT')

            {{-- Photo de profil --}}
            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 rounded-full overflow-hidden bg-blue-100 flex items-center justify-center shrink-0 border border-gray-200">
                    <template x-if="preview">
                        <img :src="preview" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!preview">
                        <span class="text-blue-600 font-bold text-xl">{{ strtoupper(substr($admin->name, 0, 1)) }}</span>
                    </template>
                </div>
                <label class="cursor-pointer">
                    <input type="file" name="avatar" accept="image/*" class="hidden"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview">
                    <span class="btn btn-secondary text-sm">
                        <i class="fas fa-camera mr-1"></i> Changer la photo
                    </span>
                </label>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $admin->name) }}" required>
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" value="{{ old('email', $admin->email) }}" required>
                </div>
                <div>
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone', $admin->phone) }}" placeholder="+225 00 00 00 00">
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
                <a href="{{ route('admin.profile.index') }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
