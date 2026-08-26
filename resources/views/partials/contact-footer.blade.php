@php
    $contact = \App\Models\AppSetting::get('company_contact', [
        'phone'    => '+225 01 42 00 46 09',
        'whatsapp' => '+225 01 42 00 46 09',
        'email'    => 'atssarl555@gmail.com',
    ]);
    $waNumber = preg_replace('/\D/', '', $contact['whatsapp'] ?? '');
@endphp
<footer class="mt-auto" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #1d4ed8 100%);">
    <div class="max-w-5xl mx-auto px-6 py-6 flex flex-wrap items-center justify-center gap-x-8 gap-y-3 text-sm">
        <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone'] ?? '') }}"
           class="flex items-center gap-2 font-bold text-white hover:text-amber-300 transition-colors">
            <i class="fas fa-phone"></i> {{ $contact['phone'] ?? '' }}
        </a>
        @if($waNumber)
        <a href="https://wa.me/{{ $waNumber }}" target="_blank" rel="noopener"
           class="flex items-center gap-2 font-bold text-white hover:text-amber-300 transition-colors">
            <i class="fab fa-whatsapp"></i> {{ $contact['whatsapp'] ?? '' }}
        </a>
        @endif
        <a href="mailto:{{ $contact['email'] ?? '' }}"
           class="flex items-center gap-2 font-bold text-white hover:text-amber-300 transition-colors">
            <i class="fas fa-envelope"></i> {{ $contact['email'] ?? '' }}
        </a>
    </div>
</footer>
