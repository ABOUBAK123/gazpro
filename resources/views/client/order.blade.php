<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GazOrder">
    <title>Commander du gaz — GazManager</title>

    <script>tailwind = { config: {} }</script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <script src="{{ asset('alpine.min.js') }}" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LQ=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <style type="text/tailwindcss">
        * { -webkit-tap-highlight-color: transparent; }

        .store-card {
            @apply flex items-center gap-4 bg-white rounded-2xl p-4 shadow-sm
                   border-2 border-transparent cursor-pointer
                   transition-all duration-150 active:scale-[0.97];
        }
        .store-card.selected { @apply border-blue-500 bg-blue-50 shadow-blue-100; }

        .brand-card {
            @apply flex flex-col items-center gap-2 p-4 rounded-2xl border-2 border-gray-100
                   bg-white cursor-pointer transition-all duration-150 active:scale-95 select-none;
        }
        .brand-card.selected { @apply border-blue-500 bg-blue-50; }

        .weight-btn {
            @apply px-4 py-2.5 rounded-2xl border-2 border-gray-200 text-sm font-bold
                   cursor-pointer transition-all duration-150 active:scale-95 text-gray-700 bg-white select-none;
        }
        .weight-btn.selected { @apply border-blue-500 bg-blue-500 text-white; }
        .weight-btn.sold-out { @apply opacity-40 cursor-not-allowed line-through; }

        .field {
            @apply w-full border-2 border-gray-200 rounded-2xl px-4 py-3.5 text-base
                   outline-none transition bg-white
                   focus:border-blue-500 focus:ring-4 focus:ring-blue-100;
        }

        .step-enter { animation: stepIn .28s ease-out both; }
        @keyframes stepIn {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .success-enter { animation: successPop .45s cubic-bezier(.22,1,.36,1) both; }
        @keyframes successPop {
            from { opacity: 0; transform: scale(.92); }
            to   { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body class="bg-gray-50" style="min-height:100dvh;">

<div x-data="orderApp()" x-init="init()" style="min-height:100dvh;display:flex;flex-direction:column;">

    {{-- ══════ APP BAR ══════ --}}
    <div class="sticky top-0 z-50 shrink-0"
         style="background:linear-gradient(135deg,#1e3a8a,#2563eb);
                padding-top:env(safe-area-inset-top,0);">
        <div class="flex items-center h-14 px-4 gap-3">

            {{-- Back / Logo --}}
            <button x-show="step > 1 && step < 6" @click="prev()"
                    class="w-9 h-9 rounded-xl flex items-center justify-center text-white/80 hover:bg-white/15 transition shrink-0">
                <i class="fas fa-arrow-left text-sm"></i>
            </button>
            <div x-show="step === 1 || step === 6"
                 class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                <i class="fas fa-fire text-sm" style="color:#1e3a8a;"></i>
            </div>

            {{-- Title --}}
            <div class="flex-1 min-w-0">
                <div class="text-white font-bold text-sm leading-tight" x-text="stepTitle"></div>
                <div x-show="step < 6" class="text-blue-200 text-xs" x-text="`Étape ${step} sur 5`"></div>
            </div>

            {{-- Progress pills --}}
            <div x-show="step < 6" class="flex items-center gap-1 shrink-0">
                <template x-for="i in 5" :key="i">
                    <div class="rounded-full transition-all duration-300"
                         :class="i === step ? 'w-5 h-1.5 bg-white' : (i < step ? 'w-1.5 h-1.5 bg-white/60' : 'w-1.5 h-1.5 bg-white/20')">
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ══════ ERROR BANNER ══════ --}}
    <div x-show="errorMsg" x-cloak
         class="mx-4 mt-3 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3 text-sm shrink-0">
        <i class="fas fa-exclamation-circle shrink-0"></i>
        <span x-text="errorMsg"></span>
        <button @click="errorMsg=''" class="ml-auto text-red-400 hover:text-red-600 shrink-0">
            <i class="fas fa-times text-xs"></i>
        </button>
    </div>

    {{-- ══════ SCROLLABLE CONTENT ══════ --}}
    <div class="flex-1 overflow-y-auto" style="padding-bottom:90px;">

        {{-- ═══ STEP 1 — GPS + Welcome ═══ --}}
        <div x-show="step === 1" class="step-enter">

            <div class="text-center pt-8 px-6 pb-5">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl mb-4 shadow-xl"
                     style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                    <i class="fas fa-fire text-3xl" style="color:#1e3a8a;"></i>
                </div>
                <h1 class="text-2xl font-black text-gray-900 leading-tight">Commandez votre gaz</h1>
                <p class="text-gray-500 mt-2 text-sm leading-relaxed max-w-xs mx-auto">
                    Recevez vos bouteilles de gaz directement chez vous,<br>
                    livraison depuis le magasin le plus proche.
                </p>
            </div>

            {{-- GPS Card --}}
            <div class="mx-4 mb-4">

                {{-- idle --}}
                <div x-show="gpsStatus === 'idle'"
                     class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-location-dot text-amber-500 text-2xl"></i>
                    </div>
                    <p class="font-bold text-gray-800 mb-1">Activez votre localisation</p>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">
                        Pour trouver le magasin le plus proche et permettre<br>au livreur de vous retrouver facilement.
                    </p>
                    <button @click="requestGPS()"
                            class="w-full py-3.5 rounded-2xl font-bold text-white text-sm transition-all active:scale-[0.98]"
                            style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="fas fa-location-dot mr-2"></i>Activer ma localisation
                    </button>
                    <button @click="gpsStatus='denied'" class="mt-3 text-xs text-gray-400 underline">
                        Continuer sans GPS
                    </button>
                </div>

                {{-- requesting --}}
                <div x-show="gpsStatus === 'requesting'"
                     class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 text-center">
                    <div class="relative w-16 h-16 mx-auto mb-3">
                        <div class="absolute inset-0 bg-blue-200 rounded-full animate-ping opacity-60"></div>
                        <div class="relative w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-satellite-dish text-white text-xl"></i>
                        </div>
                    </div>
                    <p class="font-bold text-gray-800">Acquisition GPS en cours…</p>
                    <p class="text-xs text-gray-500 mt-1">Veuillez autoriser l'accès à votre position</p>
                </div>

                {{-- success --}}
                <div x-show="gpsStatus === 'success'"
                     class="bg-green-50 border border-green-200 rounded-3xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-500 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fas fa-check text-white"></i>
                    </div>
                    <div>
                        <p class="font-bold text-green-800 text-sm">Position détectée ✓</p>
                        <p class="text-green-600 text-xs font-mono mt-0.5" x-text="locationLabel"></p>
                        <p class="text-green-600 text-xs mt-0.5">Le livreur peut vous retrouver avec le GPS</p>
                    </div>
                </div>

                {{-- denied --}}
                <div x-show="gpsStatus === 'denied'"
                     class="bg-orange-50 border border-orange-200 rounded-3xl p-4 flex items-center gap-4">
                    <div class="w-12 h-12 bg-orange-100 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fas fa-location-slash text-orange-500 text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-orange-800 text-sm">Localisation non activée</p>
                        <p class="text-orange-600 text-xs mt-0.5 leading-relaxed">
                            Vous pouvez quand même commander. Le livreur vous contactera par téléphone.
                        </p>
                    </div>
                </div>
            </div>

            {{-- How it works --}}
            <div class="mx-4 mb-4 bg-white rounded-3xl shadow-sm border border-gray-100 p-5">
                <p class="font-bold text-gray-800 text-sm mb-4 flex items-center gap-2">
                    <i class="fas fa-circle-info text-blue-500"></i>
                    Comment ça marche ?
                </p>
                <div class="space-y-3.5">
                    @foreach([
                        ['Choisissez un magasin près de chez vous'],
                        ['Sélectionnez votre bouteille de gaz'],
                        ['Entrez votre numéro de téléphone'],
                        ['Recevez votre gaz à domicile 🚚'],
                    ] as $i => $howStep)
                    <div class="flex items-center gap-3">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-black text-white shrink-0"
                             style="background:#2563eb;">{{ $i+1 }}</div>
                        <p class="text-sm text-gray-600">{{ $howStep[0] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══ STEP 2 — Magasin ═══ --}}
        <div x-show="step === 2" class="step-enter">

            <div class="px-4 pt-5 pb-3">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i>
                    Magasins disponibles
                    <span x-show="gpsStatus === 'success'" class="text-green-600 normal-case font-normal ml-1">— triés par distance</span>
                </p>
            </div>

            <div x-show="loading" class="text-center py-16">
                <div class="w-10 h-10 border-4 border-blue-100 border-t-blue-500 rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm text-gray-500">Chargement des magasins…</p>
            </div>

            <div class="px-4 space-y-3 pb-4" x-show="!loading">
                <template x-for="store in stores" :key="store.id">
                    <div @click="selectStore(store)"
                         :class="selectedStore?.id === store.id ? 'store-card selected' : 'store-card'">

                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-lg text-white shrink-0"
                             style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                            <span x-text="store.store_name.charAt(0).toUpperCase()"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 text-sm leading-tight" x-text="store.store_name"></p>
                            <p class="text-xs text-gray-500 truncate mt-0.5"
                               x-text="store.address || 'Adresse non renseignée'"></p>
                            <p x-show="store.phone" class="text-xs text-blue-600 mt-0.5 flex items-center gap-1">
                                <i class="fas fa-phone text-xs"></i>
                                <span x-text="store.phone"></span>
                            </p>
                        </div>

                        <div class="flex flex-col items-end gap-1.5 shrink-0">
                            <span x-show="store.distance != null"
                                  class="text-xs font-bold px-2 py-0.5 rounded-full bg-blue-50 text-blue-600"
                                  x-text="store.distance < 1 ? Math.round(store.distance*1000)+' m' : store.distance+' km'">
                            </span>
                            <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                        </div>
                    </div>
                </template>

                <div x-show="stores.length === 0 && !loading" class="text-center py-16">
                    <i class="fas fa-store-slash text-5xl text-gray-200 block mb-3"></i>
                    <p class="text-gray-500 font-medium">Aucun magasin disponible</p>
                    <p class="text-gray-400 text-sm mt-1">Vérifiez votre connexion et réessayez</p>
                    <button @click="loadStores()" class="mt-4 text-sm text-blue-600 font-semibold">
                        <i class="fas fa-rotate-right mr-1"></i>Actualiser
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══ STEP 3 — Produit ═══ --}}
        <div x-show="step === 3" class="step-enter">

            <div x-show="loading" class="text-center py-16">
                <div class="w-10 h-10 border-4 border-blue-100 border-t-blue-500 rounded-full animate-spin mx-auto mb-3"></div>
                <p class="text-sm text-gray-500">Chargement du stock…</p>
            </div>

            <div x-show="!loading" class="px-4 pt-5 space-y-6 pb-4">

                {{-- Brand --}}
                <div>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                        <i class="fas fa-fire text-orange-500 mr-1"></i>Choisissez la marque
                    </p>

                    <div x-show="brands.length === 0"
                         class="text-center py-10 bg-white rounded-2xl border border-gray-100">
                        <i class="fas fa-box-open text-3xl text-gray-200 block mb-2"></i>
                        <p class="text-gray-500 text-sm">Stock épuisé pour ce magasin</p>
                        <button @click="step=2; selectedStore=null"
                                class="mt-3 text-sm text-blue-600 font-semibold underline">
                            Choisir un autre magasin
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <template x-for="brand in brands" :key="brand">
                            <div @click="selectBrand(brand)"
                                 :class="selectedBrand === brand ? 'brand-card selected' : 'brand-card'">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-sm"
                                     :style="brandGradient(brand)">
                                    <span x-text="brand.charAt(0)"></span>
                                </div>
                                <p class="font-bold text-gray-800 text-sm" x-text="brand"></p>
                                <p class="text-xs text-gray-500" x-text="stockCount(brand)+' type(s)'"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Weight --}}
                <div x-show="selectedBrand" x-transition>
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                        <i class="fas fa-weight-hanging text-purple-500 mr-1"></i>Choisissez le type
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="w in weights" :key="w.weight">
                            <button @click="w.qty > 0 && selectWeight(w)"
                                    :class="['weight-btn', selectedWeight===w.weight ? 'selected' : '', w.qty===0 ? 'sold-out' : '']">
                                <span x-text="w.weight"></span>
                                <span x-show="w.qty === 0" class="text-xs ml-1 font-normal">(épuisé)</span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Qty + Price --}}
                <div x-show="selectedWeight && selectedStock" x-transition>
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500">Prix unitaire</p>
                                <p class="text-2xl font-black text-gray-900 mt-0.5">
                                    <span x-text="fmtNum(selectedStock?.unit_price ?? 0)"></span>
                                    <span class="text-sm font-semibold text-gray-500 ml-1">XOF</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Stock dispo</p>
                                <p class="text-xl font-bold text-green-600 mt-0.5">
                                    <span x-text="selectedStock?.quantity ?? 0"></span>
                                    <span class="text-xs font-normal text-gray-400 ml-1">unités</span>
                                </p>
                            </div>
                        </div>

                        <div class="px-5 py-4">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Quantité</p>
                            <div class="flex items-center justify-between">
                                <button @click="decreaseQty()" :disabled="quantity <= 1"
                                        class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-700 font-bold active:bg-gray-200 transition disabled:opacity-30">
                                    <i class="fas fa-minus text-sm"></i>
                                </button>
                                <div class="text-center">
                                    <span class="text-4xl font-black text-gray-900" x-text="quantity"></span>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="quantity>1?'bouteilles':'bouteille'"></p>
                                </div>
                                <button @click="increaseQty()"
                                        class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white font-bold active:bg-blue-700 transition">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                        </div>

                        <div class="px-5 py-4 bg-blue-50 border-t border-blue-100 flex items-center justify-between">
                            <p class="font-bold text-blue-800">Total à payer</p>
                            <p class="text-2xl font-black text-blue-900">
                                <span x-text="fmtNum(total)"></span>
                                <span class="text-sm font-semibold ml-1">XOF</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ STEP 4 — Contact ═══ --}}
        <div x-show="step === 4" class="step-enter">
            <div class="px-4 pt-5 space-y-5 pb-4">

                <div class="flex items-start gap-3 bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3">
                    <i class="fas fa-shield-alt text-blue-500 mt-0.5 shrink-0"></i>
                    <p class="text-xs text-blue-700 leading-relaxed">
                        Vos informations sont uniquement partagées avec le magasin sélectionné pour effectuer votre livraison.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">
                        <i class="fas fa-user text-gray-400 mr-1"></i>Votre nom complet *
                    </label>
                    <input type="text" x-model="customerName"
                           class="field" placeholder="Ex : Jean Dupont"
                           autocomplete="name" inputmode="text">
                    <p x-show="customerName.length > 0 && customerName.trim().length < 2"
                       class="text-xs text-red-500 mt-1">
                        <i class="fas fa-circle-exclamation mr-1"></i>Nom trop court
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">
                        <i class="fas fa-phone text-gray-400 mr-1"></i>Numéro de téléphone *
                    </label>
                    <input type="tel" x-model="customerPhone"
                           class="field" placeholder="Ex : +225 07 00 00 00 00"
                           autocomplete="tel" inputmode="tel">
                    <p x-show="customerPhone.length > 0 && customerPhone.replace(/\D/g,'').length < 8"
                       class="text-xs text-red-500 mt-1">
                        <i class="fas fa-circle-exclamation mr-1"></i>Numéro invalide
                    </p>
                </div>

                {{-- GPS reminder --}}
                <div x-show="gpsStatus === 'success'"
                     class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-2xl p-3.5">
                    <div class="w-9 h-9 bg-green-500 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-map-marker-alt text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-green-800">Localisation GPS activée ✓</p>
                        <p class="text-xs text-green-600 font-mono mt-0.5" x-text="locationLabel"></p>
                    </div>
                </div>

                <div x-show="gpsStatus !== 'success'"
                     class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-2xl p-3.5">
                    <div class="w-9 h-9 bg-yellow-400 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fas fa-location-slash text-white text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-yellow-800">Pas de GPS</p>
                        <p class="text-xs text-yellow-700">Le livreur vous appellera pour se localiser</p>
                    </div>
                    <button @click="requestGPS()" class="text-xs font-bold text-yellow-800 underline shrink-0">
                        Activer
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">
                        <i class="fas fa-sticky-note text-gray-400 mr-1"></i>Notes / Instructions (optionnel)
                    </label>
                    <textarea x-model="notes" class="field" rows="3"
                              placeholder="Ex : 2ème étage appt 3B, quartier Cocody, bâtiment bleu…"></textarea>
                </div>
            </div>
        </div>

        {{-- ═══ STEP 5 — Récapitulatif ═══ --}}
        <div x-show="step === 5" class="step-enter">
            <div class="px-4 pt-5 space-y-4 pb-4">

                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider px-1">
                    <i class="fas fa-clipboard-list text-blue-500 mr-1"></i>Résumé de votre commande
                </p>

                {{-- Store --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 px-5 py-4 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-black shrink-0"
                         style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                        <span x-text="selectedStore?.store_name.charAt(0).toUpperCase()"></span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">Magasin</p>
                        <p class="font-bold text-gray-900" x-text="selectedStore?.store_name"></p>
                        <p class="text-xs text-gray-500" x-text="selectedStore?.address || ''"></p>
                    </div>
                </div>

                {{-- Product --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white font-black shrink-0"
                             :style="brandGradient(selectedBrand)">
                            <span x-text="selectedBrand?.charAt(0)"></span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-900" x-text="(selectedBrand??'')+' '+(selectedWeight??'')"></p>
                            <p class="text-xs text-gray-500">
                                <span x-text="quantity"></span>
                                <span x-text="quantity>1?' bouteilles':' bouteille'"></span>
                                × <span x-text="fmtNum(selectedStock?.unit_price??0)"></span> XOF
                            </p>
                        </div>
                    </div>
                    <p class="font-black text-xl text-gray-900 shrink-0">
                        <span x-text="fmtNum(total)"></span>
                        <span class="text-xs font-normal text-gray-500 ml-0.5">XOF</span>
                    </p>
                </div>

                {{-- Contact --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 px-5 py-4">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide mb-3">Vos coordonnées</p>
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-user text-gray-400 w-4 text-sm text-center shrink-0"></i>
                            <span class="text-sm font-semibold text-gray-800" x-text="customerName"></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-phone text-gray-400 w-4 text-sm text-center shrink-0"></i>
                            <span class="text-sm font-semibold text-gray-800" x-text="customerPhone"></span>
                        </div>
                        <template x-if="notes">
                            <div class="flex items-start gap-3">
                                <i class="fas fa-sticky-note text-gray-400 w-4 text-sm text-center mt-0.5 shrink-0"></i>
                                <span class="text-sm text-gray-600" x-text="notes"></span>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- GPS --}}
                <div x-show="gpsStatus === 'success'"
                     class="bg-green-50 border border-green-200 rounded-3xl px-5 py-4 flex items-center gap-4">
                    <div class="w-11 h-11 bg-green-500 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fas fa-map-marker-alt text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-green-800">Localisation GPS partagée</p>
                        <p class="text-xs text-green-600 font-mono mt-0.5" x-text="locationLabel"></p>
                        <p class="text-xs text-green-600 mt-0.5">Le livreur peut vous trouver avec le GPS</p>
                    </div>
                </div>

                <div x-show="gpsStatus !== 'success'"
                     class="bg-yellow-50 border border-yellow-200 rounded-3xl px-5 py-4 flex items-center gap-4">
                    <div class="w-11 h-11 bg-yellow-400 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fas fa-phone text-white"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-yellow-800">Livraison par téléphone</p>
                        <p class="text-xs text-yellow-700">
                            Le livreur vous appellera au <strong x-text="customerPhone"></strong>
                        </p>
                    </div>
                </div>

                <p class="text-xs text-gray-400 text-center px-4">
                    En validant, vous acceptez que vos informations soient transmises au magasin pour la livraison.
                </p>
            </div>
        </div>

        {{-- ═══ STEP 6 — Succès ═══ --}}
        <div x-show="step === 6" class="success-enter">
            <div class="flex flex-col items-center text-center px-6 pt-10 pb-6">

                <div class="relative mb-6">
                    <div class="absolute bg-green-200 rounded-full animate-ping opacity-60"
                         style="width:80px;height:80px;top:0;left:0;"></div>
                    <div class="relative w-20 h-20 bg-green-500 rounded-full flex items-center justify-center shadow-xl shadow-green-200">
                        <i class="fas fa-check text-white text-3xl"></i>
                    </div>
                </div>

                <h2 class="text-2xl font-black text-gray-900 mb-2">Commande envoyée !</h2>

                <div x-show="orderId"
                     class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 text-gray-600 text-sm font-mono mb-4">
                    <i class="fas fa-hashtag text-xs"></i>
                    Commande n°<span x-text="orderId"></span>
                </div>

                <p class="text-gray-500 text-sm leading-relaxed mb-6 max-w-xs">
                    Votre commande a été transmise au magasin.<br>
                    <strong class="text-gray-700">Vous serez contacté sous peu</strong>
                    au <strong class="text-blue-600" x-text="customerPhone"></strong>
                </p>

                <div class="w-full bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 text-left">
                    <div class="flex items-center gap-3 pb-3 mb-3 border-b border-gray-100">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-bold text-sm shrink-0"
                             :style="brandGradient(selectedBrand)">
                            <span x-text="selectedBrand?.charAt(0)"></span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800 text-sm" x-text="(selectedBrand??'')+' '+(selectedWeight??'')"></p>
                            <p class="text-xs text-gray-500">
                                <span x-text="quantity"></span> bouteille(s) — <span x-text="fmtNum(total)"></span> XOF
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-store text-blue-500 w-5 text-sm text-center shrink-0"></i>
                        <p class="text-sm text-gray-700" x-text="selectedStore?.store_name"></p>
                    </div>
                </div>

                <div class="w-full space-y-2 mb-8">
                    <div class="flex items-center gap-3 bg-blue-50 rounded-2xl px-4 py-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-bell text-blue-600 text-xs"></i>
                        </div>
                        <p class="text-sm text-blue-700 font-medium text-left flex-1">Le magasin a reçu votre commande</p>
                        <i class="fas fa-check text-green-500 shrink-0 text-xs"></i>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50 rounded-2xl px-4 py-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-phone text-gray-400 text-xs"></i>
                        </div>
                        <p class="text-sm text-gray-500 text-left">Un livreur vous contactera bientôt</p>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50 rounded-2xl px-4 py-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center shrink-0">
                            <i class="fas fa-truck text-gray-400 text-xs"></i>
                        </div>
                        <p class="text-sm text-gray-500 text-left">Livraison à votre adresse</p>
                    </div>
                </div>

                <button @click="restart()"
                        class="w-full py-4 rounded-2xl font-bold text-white mb-3 transition-all active:scale-[0.98]"
                        style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                    <i class="fas fa-plus mr-2"></i>Passer une autre commande
                </button>

                <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-gray-600 transition">
                    Vous êtes un magasin ? <span class="underline">Connexion</span>
                </a>
            </div>
        </div>

    </div>{{-- end scrollable --}}

    {{-- ══════ BOTTOM ACTION BAR ══════ --}}
    <div x-show="step < 6"
         class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 shadow-xl z-40"
         style="padding-bottom:env(safe-area-inset-bottom,12px);">
        <div class="flex items-center gap-3 px-4 py-3">

            <button x-show="step > 1" @click="prev()"
                    class="shrink-0 flex items-center gap-2 px-4 py-3 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition active:bg-gray-100">
                <i class="fas fa-arrow-left text-xs"></i>Retour
            </button>

            <div x-show="step === 1" class="flex-1"></div>

            <div x-show="step > 1" class="flex items-center gap-1.5 flex-1 justify-center">
                <template x-for="i in 5" :key="i">
                    <div class="rounded-full transition-all duration-300"
                         :class="i===step ? 'w-5 h-1.5 bg-blue-500' : (i<step ? 'w-1.5 h-1.5 bg-blue-300' : 'w-1.5 h-1.5 bg-gray-200')">
                    </div>
                </template>
            </div>

            <button @click="step===5 ? submit() : next()"
                    :disabled="!canNext || submitting"
                    class="shrink-0 flex items-center gap-2 px-5 py-3 rounded-2xl font-bold text-sm text-white transition-all active:scale-95 disabled:opacity-40"
                    style="background:linear-gradient(135deg,#2563eb,#1d4ed8);">
                <template x-if="submitting">
                    <div class="w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin"></div>
                </template>
                <template x-if="!submitting">
                    <span x-text="step===5 ? 'Commander ✓' : 'Suivant'"></span>
                </template>
                <i x-show="!submitting" class="text-xs fas"
                   :class="step===5 ? 'fa-check' : 'fa-arrow-right'"></i>
            </button>
        </div>
    </div>

</div>

<script>
function orderApp() {
    return {
        step: 1,
        gpsStatus: 'idle',
        lat: null,
        lng: null,
        locationLabel: '',
        stores: [],
        selectedStore: null,
        stocks: [],
        brands: [],
        selectedBrand: null,
        weights: [],
        selectedWeight: null,
        selectedStock: null,
        quantity: 1,
        customerName: '',
        customerPhone: '',
        notes: '',
        loading: false,
        submitting: false,
        errorMsg: '',
        orderId: '',

        get total() {
            return this.selectedStock
                ? parseFloat(this.selectedStock.unit_price) * this.quantity
                : 0;
        },

        get stepTitle() {
            return {
                1: 'Commander du gaz',
                2: 'Choisir un magasin',
                3: 'Choisir votre gaz',
                4: 'Vos coordonnées',
                5: 'Récapitulatif',
                6: 'Commande confirmée',
            }[this.step] || '';
        },

        get canNext() {
            if (this.step === 1) return true;
            if (this.step === 2) return !!this.selectedStore;
            if (this.step === 3) return !!(this.selectedBrand && this.selectedWeight && this.selectedStock);
            if (this.step === 4) {
                return this.customerPhone.replace(/\D/g, '').length >= 8
                    && this.customerName.trim().length >= 2;
            }
            return true;
        },

        async init() {
            await this.loadStores();
        },

        async loadStores() {
            this.loading = true;
            try {
                let url = window.location.origin + '{{ parse_url(url("/api/stores"), PHP_URL_PATH) }}';
                if (this.lat && this.lng) url += `?lat=${this.lat}&lng=${this.lng}`;
                const r = await fetch(url);
                this.stores = await r.json();
            } catch (e) {
                this.stores = [];
            }
            this.loading = false;
        },

        async requestGPS() {
            if (!navigator.geolocation) { this.gpsStatus = 'denied'; return; }
            this.gpsStatus = 'requesting';
            navigator.geolocation.getCurrentPosition(
                async (pos) => {
                    this.lat = pos.coords.latitude;
                    this.lng = pos.coords.longitude;
                    this.gpsStatus = 'success';
                    this.locationLabel = `${this.lat.toFixed(5)}, ${this.lng.toFixed(5)}`;
                    await this.loadStores();
                },
                () => { this.gpsStatus = 'denied'; },
                { enableHighAccuracy: true, timeout: 10000 }
            );
        },

        async selectStore(store) {
            this.selectedStore = store;
            this.loading = true;
            try {
                const r = await fetch(window.location.origin + `{{ parse_url(url("/api/stock"), PHP_URL_PATH) }}?store_id=${store.id}`);
                this.stocks = await r.json();
                this.brands = [...new Set(this.stocks.map(s => s.brand))];
            } catch (e) {
                this.brands = [];
            }
            this.selectedBrand = null;
            this.selectedWeight = null;
            this.selectedStock = null;
            this.weights = [];
            this.quantity = 1;
            this.loading = false;
            this.step = 3;
        },

        selectBrand(brand) {
            this.selectedBrand = brand;
            this.weights = this.stocks
                .filter(s => s.brand === brand)
                .map(s => ({ weight: s.weight, price: s.unit_price, qty: s.quantity }));
            this.selectedWeight = null;
            this.selectedStock = null;
            this.quantity = 1;
        },

        selectWeight(w) {
            this.selectedWeight = w.weight;
            this.selectedStock = this.stocks.find(
                s => s.brand === this.selectedBrand && s.weight === w.weight
            );
            this.quantity = 1;
        },

        increaseQty() {
            if (this.selectedStock && this.quantity < this.selectedStock.quantity) this.quantity++;
        },

        decreaseQty() {
            if (this.quantity > 1) this.quantity--;
        },

        next() {
            if (this.canNext && this.step < 5) this.step++;
        },

        prev() {
            if (this.step > 1) this.step--;
        },

        async submit() {
            if (!this.canNext || this.submitting) return;
            this.submitting = true;
            this.errorMsg = '';

            const fd = new FormData();
            fd.append('_token',       document.querySelector('meta[name=csrf-token]').content);
            fd.append('store_id',     this.selectedStore.id);
            fd.append('client_name',  this.customerName);
            fd.append('client_phone', this.customerPhone);
            fd.append('brand',        this.selectedBrand);
            fd.append('weight',       this.selectedWeight);
            fd.append('quantity',     this.quantity);
            fd.append('currency',     'XOF');
            fd.append('notes',        this.notes);
            if (this.lat) fd.append('latitude',  this.lat);
            if (this.lng) fd.append('longitude', this.lng);

            try {
                const r = await fetch(window.location.origin + '{{ parse_url(route("client.order.store"), PHP_URL_PATH) }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: fd,
                });
                const data = await r.json();
                if (r.ok && data.success) {
                    this.orderId = data.order_id || '';
                    this.step = 6;
                } else {
                    this.errorMsg = data.message || 'Une erreur est survenue. Veuillez réessayer.';
                }
            } catch (e) {
                this.errorMsg = 'Erreur de connexion. Vérifiez votre internet et réessayez.';
            }
            this.submitting = false;
        },

        restart() {
            Object.assign(this, {
                step: 1,
                selectedStore: null,
                selectedBrand: null,
                selectedWeight: null,
                selectedStock: null,
                quantity: 1,
                customerName: '',
                customerPhone: '',
                notes: '',
                errorMsg: '',
                orderId: '',
                gpsStatus: 'idle',
                lat: null,
                lng: null,
                locationLabel: '',
            });
            this.loadStores();
        },

        brandGradient(brand) {
            if (!brand) return 'background:linear-gradient(135deg,#6366f1,#8b5cf6)';
            const map = {
                total:    'background:linear-gradient(135deg,#ef4444,#f97316)',
                shell:    'background:linear-gradient(135deg,#eab308,#d97706)',
                oryx:     'background:linear-gradient(135deg,#3b82f6,#06b6d4)',
                kobil:    'background:linear-gradient(135deg,#22c55e,#10b981)',
                sodigaz:  'background:linear-gradient(135deg,#8b5cf6,#6366f1)',
                butagaz:  'background:linear-gradient(135deg,#f97316,#fb923c)',
                zola:     'background:linear-gradient(135deg,#ec4899,#f43f5e)',
            };
            return map[brand.toLowerCase()] || 'background:linear-gradient(135deg,#64748b,#475569)';
        },

        stockCount(brand) {
            return this.stocks.filter(s => s.brand === brand).length;
        },

        fmtNum(n) {
            return Number(n).toLocaleString('fr-FR');
        },
    };
}
</script>
</body>
</html>
