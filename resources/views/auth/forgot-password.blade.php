<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GazManager — Mot de passe oublié</title>
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
            <i class="fas fa-key text-xl" style="color:#1e3a8a;"></i>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">GazManager</h1>
        <p class="text-blue-300 mt-0.5 text-xs">Réinitialisation du mot de passe</p>
    </div>

    {{-- Alertes flash --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-green-500/20 border border-green-400/30 text-green-200 rounded-xl px-4 py-3 mb-4 text-sm backdrop-blur">
        <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="flex items-center gap-3 bg-red-500/20 border border-red-400/30 text-red-200 rounded-xl px-4 py-3 mb-4 text-sm backdrop-blur">
        <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-7 pt-6 pb-4">
            <h2 class="text-lg font-bold text-gray-900">Mot de passe oublié ?</h2>
            <p class="text-gray-400 text-xs mt-0.5">Entrez votre email, nous vous enverrons un lien de réinitialisation.</p>
        </div>

        <div class="px-7 pb-6">
            <form action="{{ route('password.forgot.send') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Adresse email</label>
                    <div class="relative">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               placeholder="votre@email.com"
                               class="input-field pl-10 @error('email') border-red-400 focus:border-red-500 focus:ring-red-100 @enderror">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1 flex items-center gap-1">
                            <i class="fas fa-circle-exclamation"></i>{{ $message }}
                        </p>
                    @enderror
                </div>

                <button type="submit"
                        class="w-full text-white font-bold py-3 rounded-xl text-sm transition-all duration-200 shadow-lg"
                        style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                    <i class="fas fa-paper-plane mr-2"></i>Envoyer le lien de réinitialisation
                </button>
            </form>

            <p class="mt-4 pt-4 border-t border-gray-100 text-center text-xs text-gray-500">
                <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:text-blue-800 transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Retour à la connexion
                </a>
            </p>
        </div>
    </div>

    @include('partials.contact-footer', ['footerDark' => true])

</div>
</body>
</html>
