<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GazManager — Gérez votre dépôt de gaz</title>
    <script>tailwind = { config: {} }</script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">

    {{-- Hero --}}
    <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#1d4ed8 100%);" class="text-white">
        <nav class="max-w-6xl mx-auto flex items-center justify-between px-6 py-5">
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg"
                     style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                    <i class="fas fa-fire text-sm" style="color:#1e3a8a;"></i>
                </div>
                <span class="font-black text-lg">GazManager</span>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm text-blue-100 hover:text-white transition">Se connecter</a>
                <a href="{{ route('register') }}"
                   class="bg-white text-blue-800 text-sm font-semibold px-4 py-2 rounded-xl hover:bg-blue-50 transition">
                    Inscrire mon magasin
                </a>
            </div>
        </nav>

        <div class="max-w-3xl mx-auto text-center px-6 pt-8 pb-20">
            <h1 class="text-3xl sm:text-4xl font-black mb-4">Gérez votre dépôt de gaz, du stock à la livraison</h1>
            <p class="text-blue-100 text-base sm:text-lg mb-8">
                Stock, ventes, livreurs, fidélité et commandes en ligne — tout-en-un pour les dépôts de gaz.
                Testez gratuitement pendant 7 jours.
            </p>
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-2 bg-amber-400 text-blue-900 font-bold px-6 py-3 rounded-2xl shadow-xl hover:bg-amber-300 transition">
                <i class="fas fa-rocket"></i> Commencer l'essai gratuit
            </a>
        </div>
    </div>

    {{-- Plans --}}
    <div class="max-w-5xl mx-auto px-6 -mt-12 pb-20">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Nos formules d'abonnement</h2>
            <p class="text-gray-500 text-sm mt-1">Choisissez la formule adaptée à votre activité</p>
        </div>

        @if($plans->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg p-8 text-center text-gray-500">
                Aucune formule n'est disponible pour le moment.
            </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-{{ min($plans->count(), 3) }} gap-6">
            @foreach($plans as $plan)
            @php $isFree = (float) $plan->price === 0.0; @endphp
            <div class="bg-white rounded-3xl shadow-lg p-6 flex flex-col {{ $isFree ? 'ring-2 ring-amber-400' : '' }}">
                @if($isFree)
                <span class="self-start bg-amber-100 text-amber-700 text-xs font-bold px-3 py-1 rounded-full mb-3">
                    <i class="fas fa-star"></i> Essai gratuit
                </span>
                @endif
                <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $plan->name }}</h3>
                <div class="mb-1">
                    @if($isFree)
                        <span class="text-3xl font-black text-gray-900">Gratuit</span>
                    @else
                        <span class="text-3xl font-black text-gray-900">{{ number_format($plan->price, 0, ',', ' ') }}</span>
                        <span class="text-gray-500 text-sm">{{ $plan->currency }}</span>
                    @endif
                </div>
                <p class="text-gray-500 text-sm mb-6">{{ $plan->duration_days }} jours</p>
                <ul class="text-sm text-gray-600 space-y-2 mb-8 flex-1">
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Gestion du stock</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Ventes & commandes</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Livreurs & suivi</li>
                    <li class="flex items-center gap-2"><i class="fas fa-check text-green-500"></i> Programme de fidélité</li>
                </ul>
                <a href="{{ route('register') }}"
                   class="text-center font-semibold py-3 rounded-xl transition {{ $isFree ? 'bg-amber-400 text-blue-900 hover:bg-amber-300' : 'bg-blue-700 text-white hover:bg-blue-800' }}">
                    {{ $isFree ? "Démarrer l'essai" : "S'inscrire" }}
                </a>
            </div>
            @endforeach
        </div>
        @endif

        <p class="text-center text-xs text-gray-400 mt-8">
            Après votre inscription, votre compte est validé sous 24h par notre équipe.
        </p>
    </div>

    @include('partials.contact-footer', ['footerDark' => false])

</body>
</html>
