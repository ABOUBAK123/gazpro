<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GazManager — Conditions d'utilisation</title>
    <script>tailwind = { config: {} }</script>
    <script src="{{ asset('tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-50">

    <div style="background:linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#1d4ed8 100%);" class="text-white">
        <nav class="max-w-3xl mx-auto flex items-center gap-2.5 px-6 py-5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg"
                 style="background:linear-gradient(135deg,#fbbf24,#f59e0b);">
                <i class="fas fa-fire text-sm" style="color:#1e3a8a;"></i>
            </div>
            <span class="font-black text-lg">GazManager</span>
        </nav>
        <div class="max-w-3xl mx-auto px-6 pb-10">
            <h1 class="text-2xl sm:text-3xl font-black">Conditions d'utilisation</h1>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-6 -mt-6 pb-16">
        <div class="bg-white rounded-3xl shadow-lg p-8">
            @if(trim($terms) !== '')
                <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line">{{ $terms }}</div>
            @else
                <p class="text-gray-400 text-sm">Les conditions d'utilisation n'ont pas encore été renseignées par l'administrateur.</p>
            @endif
        </div>

        <p class="text-center mt-6">
            <a href="javascript:history.back()" class="text-blue-600 text-sm hover:underline">
                <i class="fas fa-arrow-left mr-1"></i>Retour
            </a>
        </p>
    </div>

</body>
</html>
