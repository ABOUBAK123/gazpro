<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GazManager — Connexion</title>
    <script>tailwind = { config: {} }</script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
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

{{-- Cercles décoratifs --}}
<div class="fixed inset-0 overflow-hidden pointer-events-none">
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full opacity-10"
         style="background:radial-gradient(circle, #60a5fa, transparent);"></div>
    <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full opacity-10"
         style="background:radial-gradient(circle, #818cf8, transparent);"></div>
</div>

<div class="w-full max-w-md relative z-10">

    {{-- Logo --}}
    <div class="text-center mb-5">
        <div class="inline-flex items-center justify-center rounded-2xl mb-3 shadow-xl"
             style="width:54px;height:54px;background:linear-gradient(135deg,#fbbf24,#f59e0b);">
            <i class="fas fa-fire text-xl" style="color:#1e3a8a;"></i>
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">GazManager</h1>
        <p class="text-blue-300 mt-0.5 text-xs">Plateforme de gestion de magasins de gaz</p>
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

    {{-- Carte formulaire --}}
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

        {{-- Header carte --}}
        <div class="px-7 pt-6 pb-4">
            <h2 class="text-lg font-bold text-gray-900">Connexion à votre espace</h2>
            <p class="text-gray-400 text-xs mt-0.5">Entrez vos identifiants pour continuer</p>
        </div>

        <div class="px-7 pb-6">
            <form action="{{ route('login') }}" method="POST" class="space-y-3">
                @csrf

                {{-- Email --}}
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

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Mot de passe</label>
                    <div class="relative">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="pwd" required autocomplete="current-password"
                               placeholder="••••••••"
                               class="input-field pl-10 pr-10">
                        <button type="button" onclick="togglePwd()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye text-sm" id="pwd-icon"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-3.5 h-3.5 rounded border-gray-300 text-blue-600 cursor-pointer">
                    <label for="remember" class="text-xs text-gray-500 cursor-pointer">Se souvenir de moi</label>
                </div>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full text-white font-bold py-3 rounded-xl text-sm transition-all duration-200 shadow-lg"
                        style="background:linear-gradient(135deg,#2563eb,#1d4ed8);"
                        onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 25px rgba(37,99,235,0.4)';"
                        onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">
                    <i class="fas fa-arrow-right-to-bracket mr-2"></i>Se connecter
                </button>
            </form>

            {{-- Liens --}}
            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-xs">
                <p class="text-gray-500">
                    Pas de compte ?
                    <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:text-blue-800 transition-colors">
                        S'inscrire
                    </a>
                </p>
                <a href="{{ route('client.order') }}"
                   class="text-gray-400 hover:text-blue-600 transition-colors">
                    <i class="fas fa-shopping-cart mr-1"></i>Commander (client)
                </a>
            </div>
        </div>
    </div>

    {{-- Comptes demo --}}
    <div class="mt-4 rounded-xl px-4 py-3 text-xs" style="background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.1);">
        <p class="font-semibold text-white/80 mb-1.5 flex items-center gap-1.5">
            <i class="fas fa-circle-info text-blue-300 text-xs"></i>Comptes démo
        </p>
        <div class="space-y-1 text-blue-200/80">
            <div class="flex justify-between"><span class="text-white/50">Admin</span><code>admin@gazmanager.com / admin123</code></div>
            <div class="flex justify-between"><span class="text-white/50">Manager</span><code>manager@test.com / manager123</code></div>
            <div class="flex justify-between"><span class="text-white/50">Caissier</span><code>employee@test.com / employee123</code></div>
        </div>
    </div>

</div>

<script>
function togglePwd() {
    const f = document.getElementById('pwd');
    const i = document.getElementById('pwd-icon');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>
