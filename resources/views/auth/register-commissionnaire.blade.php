<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GazManager — Devenir commissionnaire</title>
    <script>tailwind = { config: {} }</script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style type="text/tailwindcss">
        .field {
            @apply w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800
                   outline-none transition-all focus:border-blue-500 focus:ring-4 focus:ring-blue-100 bg-white;
        }
        .field-label { @apply block text-sm font-semibold text-gray-700 mb-1.5; }
    </style>
</head>
<body style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#1d4ed8 100%);min-height:100vh;"
      class="flex items-center justify-center p-4">

<div class="w-full max-w-lg relative z-10 py-6">

    {{-- Logo --}}
    <div class="text-center mb-6">
        <div class="inline-flex items-center justify-center rounded-2xl mb-3 shadow-xl"
             style="width:60px;height:60px;background:linear-gradient(135deg,#fbbf24,#f59e0b);">
            <i class="fas fa-handshake text-2xl" style="color:#1e3a8a;"></i>
        </div>
        <h1 class="text-2xl font-black text-white">GazManager</h1>
        <p class="text-blue-300 text-sm mt-0.5">Devenir commissionnaire</p>
    </div>

    {{-- Erreurs --}}
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

    {{-- Formulaire --}}
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
        <div class="px-8 pt-7 pb-2">
            <h2 class="text-lg font-bold text-gray-900">Créer un compte commissionnaire</h2>
            <p class="text-gray-400 text-sm mt-0.5">Parrainez des magasins et touchez 3% de commission sur chaque abonnement</p>
        </div>

        <div class="px-8 pb-8 pt-4">
            <form action="{{ route('register.commissionnaire') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="field-label">Nom complet <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="field" placeholder="Jean Dupont">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Email <span class="text-red-400">*</span></label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                               class="field" placeholder="jean@email.com">
                    </div>
                    <div>
                        <label class="field-label">Téléphone <span class="text-red-400">*</span></label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               class="field" placeholder="+225 07 00 00 00">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Mot de passe <span class="text-red-400">*</span></label>
                        <input type="password" name="password" required
                               class="field" placeholder="Min. 6 caractères">
                    </div>
                    <div>
                        <label class="field-label">Confirmation <span class="text-red-400">*</span></label>
                        <input type="password" name="password_confirmation" required
                               class="field" placeholder="Répéter">
                    </div>
                </div>

                <div class="flex items-start gap-3 bg-blue-50 rounded-xl p-3.5 text-sm text-blue-700">
                    <i class="fas fa-clock mt-0.5 shrink-0"></i>
                    <span>Votre demande sera examinée par l'administrateur. Un code de parrainage unique vous sera attribué après validation.</span>
                </div>

                <div class="flex items-start gap-2.5">
                    <input type="checkbox" name="terms" id="terms" value="1" required
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer shrink-0">
                    <label for="terms" class="text-sm text-gray-600 cursor-pointer">
                        J'accepte les <a href="{{ route('terms') }}" target="_blank" class="text-blue-600 font-semibold hover:text-blue-800 underline">conditions d'utilisation</a> de l'application <span class="text-red-400">*</span>
                    </label>
                </div>
                @error('terms')
                    <p class="text-red-500 text-xs -mt-2 flex items-center gap-1">
                        <i class="fas fa-circle-exclamation"></i>{{ $message }}
                    </p>
                @enderror

                <button type="submit"
                        class="w-full text-white font-bold py-3.5 rounded-xl text-sm transition-all duration-200 shadow-lg"
                        style="background:linear-gradient(135deg,#2563eb,#1d4ed8);"
                        onmouseover="this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 25px rgba(37,99,235,0.4)';"
                        onmouseout="this.style.transform='none';this.style.boxShadow='';">
                    <i class="fas fa-handshake mr-2"></i>Devenir commissionnaire
                </button>
            </form>

            <p class="mt-5 text-center text-sm text-gray-500">
                Déjà un compte ?
                <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:text-blue-800 transition-colors">
                    Se connecter
                </a>
            </p>
        </div>
    </div>

    @include('partials.contact-footer', ['footerDark' => true])

</div>
</body>
</html>
