<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GazManager — Nouveau mot de passe</title>
    <script>tailwind = { config: {} }</script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style type="text/tailwindcss">
        .input-field {
            @apply w-full border border-gray-200 rounded-xl px-4 py-3 text-sm text-gray-800
                   outline-none transition-all duration-200
                   focus:border-blue-500 focus:ring-4 focus:ring-blue-100 bg-white;
        }
        .input-icon {
            @apply absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none;
        }
    </style>
</head>
<body style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1d4ed8 100%); min-height:100vh;"
      class="flex items-center justify-center p-4">

<div class="w-full max-w-md relative z-10">

    {{-- Logo --}}
    <div class="text-center mb-5">
        <div class="inline-flex items-center justify-center rounded-2xl mb-3 shadow-xl"
             style="width:54px;height:54px;background:linear-gradient(135deg,#fbbf24,#f59e0b);">
            <i class="fas fa-lock text-xl" style="color:#1e3a8a;"></i>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">GazManager</h1>
        <p class="text-blue-300 mt-0.5 text-xs">Choisissez un nouveau mot de passe</p>
    </div>

    @if($errors->any())
    <div class="bg-red-500/20 border border-red-400/30 text-red-200 rounded-xl px-4 py-3 mb-4 text-sm backdrop-blur">
        <div class="font-semibold mb-1 flex items-center gap-2">
            <i class="fas fa-circle-exclamation"></i>Veuillez corriger les erreurs suivantes :
        </div>
        <ul class="list-disc pl-5 space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-7 pt-6 pb-4">
            <h2 class="text-lg font-bold text-gray-900">Nouveau mot de passe</h2>
            <p class="text-gray-400 text-xs mt-0.5">Choisissez un mot de passe pour votre compte.</p>
        </div>

        <div class="px-7 pb-6">
            <form action="{{ route('password.reset') }}" method="POST" class="space-y-3">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Adresse email</label>
                    <div class="relative">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ old('email', $email) }}" required
                               class="input-field pl-10">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nouveau mot de passe</label>
                    <div class="relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" required autocomplete="new-password"
                               placeholder="Min. 6 caractères"
                               class="input-field pl-10">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <div class="relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password_confirmation" required autocomplete="new-password"
                               placeholder="Répéter"
                               class="input-field pl-10">
                    </div>
                </div>

                <button type="submit"
                        class="w-full text-white font-bold py-3 rounded-xl text-sm transition-all duration-200 shadow-lg"
                        style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                    <i class="fas fa-check mr-2"></i>Réinitialiser le mot de passe
                </button>
            </form>
        </div>
    </div>

    @include('partials.contact-footer', ['footerDark' => true])

</div>
</body>
</html>
