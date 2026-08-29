<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e3a8a">
    <title>Télécharger l'application — {{ $store->store_name }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-b from-blue-50 to-white min-h-screen flex flex-col items-center justify-center p-4">

    <div class="max-w-sm w-full bg-white rounded-3xl shadow-xl p-8 text-center">
        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg">
            <i class="fas fa-fire-flame-simple text-white text-3xl"></i>
        </div>

        <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $store->store_name }}</h1>
        <p class="text-sm text-gray-500 mb-6">
            @if($store->address)
                {{ $store->address }}
            @else
                Application de commande
            @endif
        </p>

        <p class="text-sm text-gray-600 mb-6">
            Téléchargez l'application mobile pour commander directement auprès de <strong>{{ $store->store_name }}</strong>.
        </p>

        <button id="downloadBtn" data-token="{{ $clipboardToken }}" data-apk="{{ $apkUrl }}"
                class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.98] transition-all text-white font-semibold
                       rounded-2xl py-4 px-6 flex items-center justify-center gap-2 shadow-lg shadow-blue-200">
            <i class="fas fa-download"></i>
            Télécharger l'application
        </button>

        <p id="copyStatus" class="text-xs text-gray-400 mt-4 h-4"></p>

        <div class="mt-6 pt-6 border-t border-gray-100 text-left text-xs text-gray-500 space-y-2">
            <p class="font-semibold text-gray-600">Installation :</p>
            <p>1. Téléchargez le fichier APK ci-dessus.</p>
            <p>2. Ouvrez-le et autorisez l'installation depuis une source inconnue si demandé.</p>
            <p>3. Ouvrez l'application — elle sera automatiquement liée à {{ $store->store_name }}.</p>
        </div>
    </div>

    @include('partials.contact-footer', ['footerDark' => false])

    <script>
        document.getElementById('downloadBtn').addEventListener('click', function () {
            var token = this.dataset.token;
            var apk = this.dataset.apk;
            var status = document.getElementById('copyStatus');

            function fallbackCopy(text) {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(ta);
            }

            // Copy the store token in the background — never let this block or
            // delay the download itself. On mobile, chaining the navigation
            // inside the clipboard promise's .then() can lose the click's user
            // gesture (or stall entirely), which is why "Télécharger" appeared
            // to only copy the link without ever starting the APK download.
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(token).catch(function () {
                    fallbackCopy(token);
                });
            } else {
                fallbackCopy(token);
            }

            status.textContent = 'Téléchargement en cours...';
            window.location.href = apk;
        });
    </script>
</body>
</html>
