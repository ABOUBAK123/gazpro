@extends('layouts.app')
@section('title', 'Paramètres')
@section('page-title', 'Paramètres système')

@section('content')
<div class="pt-4" x-data="{ tab: '{{ request('tab', 'brands') }}' }">

    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-gray-200 mb-6">
        <button @click="tab='brands'" :class="tab==='brands' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50' : 'text-gray-500 hover:text-gray-700'"
                class="px-5 py-3 text-sm font-medium rounded-t-lg transition-colors">
            <i class="fas fa-tags mr-2"></i>Marques &amp; Poids
        </button>
        <button @click="tab='terms'" :class="tab==='terms' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50' : 'text-gray-500 hover:text-gray-700'"
                class="px-5 py-3 text-sm font-medium rounded-t-lg transition-colors">
            <i class="fas fa-file-contract mr-2"></i>Conditions d'utilisation
        </button>
        <button @click="tab='email'" :class="tab==='email' ? 'border-b-2 border-blue-600 text-blue-600 bg-blue-50' : 'text-gray-500 hover:text-gray-700'"
                class="px-5 py-3 text-sm font-medium rounded-t-lg transition-colors">
            <i class="fas fa-envelope mr-2"></i>Config. Email
        </button>
    </div>

    {{-- Brands & Weights --}}
    <div x-show="tab==='brands'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Brands --}}
            <div class="card">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-fire text-orange-500 mr-2"></i>Marques de gaz</h3>

                <form action="{{ route('admin.settings.brand.add') }}" method="POST" class="flex gap-3 mb-4">
                    @csrf
                    <input type="text" name="brand" placeholder="Nouvelle marque..." class="form-input" required>
                    <button type="submit" class="btn btn-primary whitespace-nowrap"><i class="fas fa-plus"></i></button>
                </form>

                <div class="space-y-2">
                    @foreach($brands as $brand)
                    <div class="flex items-center justify-between bg-gray-50 px-4 py-2.5 rounded-lg">
                        <span class="font-medium text-gray-700">{{ $brand }}</span>
                        <form action="{{ route('admin.settings.brand.delete') }}" method="POST"
                              onsubmit="return confirm('Supprimer cette marque ?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="brand" value="{{ $brand }}">
                            <button class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                    @endforeach
                    @if(empty($brands))
                        <p class="text-gray-400 text-sm text-center py-4">Aucune marque définie</p>
                    @endif
                </div>
            </div>

            {{-- Weights --}}
            <div class="card">
                <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-weight-hanging text-blue-500 mr-2"></i>Formats / Poids</h3>

                <form action="{{ route('admin.settings.weight.add') }}" method="POST" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="weight_value" placeholder="Ex: 6kg" class="form-input" required>
                    <input type="text" name="weight_code" placeholder="Code (ex: B6)" class="form-input" required>
                    <button type="submit" class="btn btn-primary whitespace-nowrap"><i class="fas fa-plus"></i></button>
                </form>

                <div class="space-y-2">
                    @foreach($weights as $w)
                    <div class="flex items-center justify-between bg-gray-50 px-4 py-2.5 rounded-lg">
                        <div>
                            <span class="font-medium text-gray-700">{{ $w['value'] }}</span>
                            <span class="text-gray-400 text-xs ml-2">Code: {{ $w['code'] }}</span>
                        </div>
                        <form action="{{ route('admin.settings.weight.delete') }}" method="POST"
                              onsubmit="return confirm('Supprimer ce poids ?')">
                            @csrf @method('DELETE')
                            <input type="hidden" name="weight_value" value="{{ $w['value'] }}">
                            <button class="text-red-400 hover:text-red-600 text-sm"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                    @endforeach
                    @if(empty($weights))
                        <p class="text-gray-400 text-sm text-center py-4">Aucun format défini</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Terms of Service --}}
    <div x-show="tab==='terms'" x-cloak>
        <div class="card max-w-3xl">
            <h3 class="font-semibold text-gray-800 mb-4"><i class="fas fa-file-contract text-blue-500 mr-2"></i>Conditions d'utilisation</h3>
            <form action="{{ route('admin.settings.terms') }}" method="POST">
                @csrf
                <textarea name="terms" rows="16" class="form-input font-mono text-sm"
                          placeholder="Rédigez les conditions d'utilisation de la plateforme...">{{ $terms }}</textarea>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Email config --}}
    <div x-show="tab==='email'" x-cloak>
        <div class="card max-w-2xl">
            <h3 class="font-semibold text-gray-800 mb-1"><i class="fas fa-envelope text-blue-500 mr-2"></i>Configuration Email (SMTP)</h3>
            <p class="text-sm text-gray-500 mb-5">Paramètres du serveur email pour les notifications automatiques.</p>
            <form action="{{ route('admin.settings.email') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Serveur SMTP (Host)</label>
                        <input type="text" name="email_host" value="{{ $emailConfig['host'] ?? '' }}"
                               placeholder="smtp.gmail.com" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Port</label>
                        <input type="number" name="email_port" value="{{ $emailConfig['port'] ?? 587 }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Nom d'utilisateur SMTP</label>
                        <input type="text" name="email_username" value="{{ $emailConfig['username'] ?? '' }}"
                               placeholder="votre@email.com" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Mot de passe SMTP</label>
                        <input type="password" name="email_password" placeholder="••••••••" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email expéditeur</label>
                        <input type="email" name="email_from_email" value="{{ $emailConfig['from_email'] ?? '' }}"
                               placeholder="noreply@gazmanager.com" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Nom expéditeur</label>
                        <input type="text" name="email_from_name" value="{{ $emailConfig['from_name'] ?? 'GazManager' }}"
                               class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Chiffrement</label>
                        <select name="email_encryption" class="form-input">
                            <option value="tls" {{ ($emailConfig['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ ($emailConfig['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ ($emailConfig['encryption'] ?? '') === 'none' ? 'selected' : '' }}>Aucun</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Sauvegarder la configuration
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

