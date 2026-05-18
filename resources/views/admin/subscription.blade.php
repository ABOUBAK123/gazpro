@extends('layouts.app')
@section('title', 'Paramètres abonnement')

@section('content')
@php
    use App\Models\AppSetting;

    $cpApiKey = AppSetting::get('cinetpay_api_key', '');
    $cpSiteId = AppSetting::get('cinetpay_site_id', '');

    $providers = [
        'orange_money' => [
            'label'   => 'Orange Money',
            'color'   => '#FF6600',
            'bg'      => '#FFF3EB',
            'border'  => '#FFBB99',
            'icon'    => 'fas fa-mobile-alt',
            'fields'  => [
                'merchant_key' => ['label' => 'Merchant Key', 'type' => 'text'],
                'api_password' => ['label' => 'API Password', 'type' => 'password'],
                'endpoint'     => ['label' => 'Endpoint URL (optionnel)', 'type' => 'text', 'placeholder' => 'https://api.orange-money.ci/...'],
            ],
        ],
        'mtn_money' => [
            'label'   => 'MTN Mobile Money',
            'color'   => '#FFCC00',
            'bg'      => '#FFFDE6',
            'border'  => '#FFE680',
            'icon'    => 'fas fa-mobile-alt',
            'fields'  => [
                'subscription_key' => ['label' => 'Subscription Key', 'type' => 'text'],
                'api_user'         => ['label' => 'API User (UUID)', 'type' => 'text'],
                'api_key'          => ['label' => 'API Key', 'type' => 'password'],
                'callback_host'    => ['label' => 'Callback Host (optionnel)', 'type' => 'text', 'placeholder' => 'https://gazpro.dyula.ci'],
            ],
        ],
        'wave' => [
            'label'   => 'Wave',
            'color'   => '#1DC8EE',
            'bg'      => '#E8F9FD',
            'border'  => '#99E5F5',
            'icon'    => 'fas fa-wave-square',
            'fields'  => [
                'api_key'        => ['label' => 'API Key', 'type' => 'password'],
                'webhook_secret' => ['label' => 'Webhook Secret', 'type' => 'password'],
            ],
        ],
        'moov_money' => [
            'label'   => 'Moov Money',
            'color'   => '#0055A5',
            'bg'      => '#E8F0FA',
            'border'  => '#99BBEE',
            'icon'    => 'fas fa-mobile-alt',
            'fields'  => [
                'username'    => ['label' => 'Nom d\'utilisateur', 'type' => 'text'],
                'password'    => ['label' => 'Mot de passe', 'type' => 'password'],
                'merchant_id' => ['label' => 'Merchant ID', 'type' => 'text'],
            ],
        ],
        'visa_card' => [
            'label'   => 'Visa / Mastercard',
            'color'   => '#1A1F71',
            'bg'      => '#EEEFFE',
            'border'  => '#AAAADD',
            'icon'    => 'fas fa-credit-card',
            'fields'  => [],
            'note'    => 'Les paiements carte sont gérés automatiquement par CinetPay. Aucun paramètre supplémentaire requis.',
        ],
    ];

    $providerData = [];
    foreach (array_keys($providers) as $key) {
        $data = AppSetting::get("payment_provider_{$key}", []);
        $providerData[$key] = is_array($data) ? $data : [];
    }

    $logoBase = asset('images/payment-logos');
@endphp

<div class="pt-4 max-w-3xl space-y-6" x-data="subscriptionAdmin()">

    {{-- ── 1. Tarifs ─────────────────────────────────────────────── --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-5 flex items-center gap-2">
            <i class="fas fa-tags text-blue-500"></i> Tarifs abonnement
        </h3>
        <form action="{{ route('admin.subscription.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Prix mensuel</label>
                    <input type="number" name="monthly_price" value="{{ $settings->monthly_price }}"
                           min="0" step="100" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Prix annuel</label>
                    <input type="number" name="yearly_price" value="{{ $settings->yearly_price }}"
                           min="0" step="100" class="form-input" required>
                </div>
            </div>
            <div>
                <label class="form-label">Devise</label>
                <select name="currency" class="form-input">
                    <option value="XOF" {{ $settings->currency === 'XOF' ? 'selected' : '' }}>XOF — Franc CFA</option>
                    <option value="EUR" {{ $settings->currency === 'EUR' ? 'selected' : '' }}>EUR — Euro</option>
                    <option value="USD" {{ $settings->currency === 'USD' ? 'selected' : '' }}>USD — Dollar</option>
                </select>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 flex gap-6 text-sm">
                <div>
                    <div class="text-gray-500 text-xs mb-0.5">Mensuel</div>
                    <div class="font-bold text-gray-800">{{ number_format($settings->monthly_price,0,',',' ') }} {{ $settings->currency }}</div>
                </div>
                <div class="w-px bg-blue-200"></div>
                <div>
                    <div class="text-gray-500 text-xs mb-0.5">Annuel</div>
                    <div class="font-bold text-gray-800">{{ number_format($settings->yearly_price,0,',',' ') }} {{ $settings->currency }}</div>
                </div>
                <div class="w-px bg-blue-200"></div>
                <div>
                    <div class="text-gray-500 text-xs mb-0.5">Économie annuel</div>
                    <div class="font-bold text-green-600">-{{ number_format(($settings->monthly_price*12)-$settings->yearly_price,0,',',' ') }} {{ $settings->currency }}</div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer les tarifs
            </button>
        </form>
    </div>

    {{-- ── 2. CinetPay gateway ────────────────────────────────────── --}}
    <div class="card">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shadow-sm"
                 style="background:linear-gradient(135deg,#ff6b00,#ff9a00);">
                <i class="fas fa-plug text-white"></i>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-gray-800">Passerelle CinetPay</h3>
                <p class="text-xs text-gray-500">Agrégateur · Orange · MTN · Wave · Moov · Visa</p>
            </div>
            @if($cpApiKey && $cpSiteId)
                <span class="badge bg-green-100 text-green-700"><i class="fas fa-circle text-xs mr-1"></i>Actif</span>
            @else
                <span class="badge bg-amber-100 text-amber-700"><i class="fas fa-exclamation-circle text-xs mr-1"></i>Non configuré</span>
            @endif
        </div>
        <form action="{{ route('admin.subscription.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            {{-- hidden required fields --}}
            <input type="hidden" name="monthly_price" value="{{ $settings->monthly_price }}">
            <input type="hidden" name="yearly_price"  value="{{ $settings->yearly_price }}">
            <input type="hidden" name="currency"      value="{{ $settings->currency }}">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">API Key</label>
                    <div class="relative">
                        <input :type="showApiKey ? 'text' : 'password'" name="cinetpay_api_key"
                               value="{{ $cpApiKey }}" class="form-input pr-10 font-mono text-sm"
                               placeholder="Votre API Key CinetPay">
                        <button type="button" @click="showApiKey=!showApiKey"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i :class="showApiKey ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                        </button>
                    </div>
                </div>
                <div>
                    <label class="form-label">Site ID</label>
                    <input type="text" name="cinetpay_site_id"
                           value="{{ $cpSiteId }}" class="form-input font-mono text-sm"
                           placeholder="Votre Site ID CinetPay">
                </div>
            </div>

            <div class="bg-gray-50 rounded-xl p-3 space-y-2 text-xs text-gray-500">
                <div class="flex items-start gap-2">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                    <span>Clés disponibles sur <strong>cinetpay.com</strong> → Mon compte → API</span>
                </div>
                <div class="flex items-start gap-2">
                    <i class="fas fa-link text-blue-400 mt-0.5"></i>
                    <div>
                        URL IPN à renseigner dans CinetPay :
                        <div class="mt-1 flex items-center gap-2">
                            <span class="font-mono bg-white border border-gray-200 rounded px-2 py-1 select-all text-gray-700">{{ route('subscription.notify') }}</span>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ route('subscription.notify') }}')"
                                    class="text-blue-500 hover:text-blue-700" title="Copier">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer CinetPay
            </button>
        </form>
    </div>

    {{-- ── 3. Méthodes de paiement ────────────────────────────────── --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <i class="fas fa-wallet text-purple-500"></i> Méthodes de paiement
        </h3>
        <p class="text-xs text-gray-500 mb-5">Activez/désactivez chaque méthode et configurez ses identifiants API. Les logos sont affichés sur la page de paiement du manager.</p>

        <form action="{{ route('admin.subscription.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="monthly_price" value="{{ $settings->monthly_price }}">
            <input type="hidden" name="yearly_price"  value="{{ $settings->yearly_price }}">
            <input type="hidden" name="currency"      value="{{ $settings->currency }}">

            @foreach($providers as $key => $prov)
            @php
                $pd      = $providerData[$key];
                $isActive = $pd['active'] ?? false;
                $logoFile = AppSetting::get("payment_logo_{$key}", '');
                $logoUrl  = $logoFile ? $logoBase . '/' . $logoFile : null;
            @endphp

            <div class="border-2 rounded-2xl overflow-hidden transition-all"
                 style="border-color: {{ $isActive ? $prov['border'] : '#E5E7EB' }}; background: {{ $isActive ? $prov['bg'] : '#FAFAFA' }}">

                {{-- Header --}}
                <div class="flex items-center gap-3 px-4 py-3">
                    {{-- Logo ou icône --}}
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center overflow-hidden border-2 border-white shadow-sm bg-white shrink-0">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}?v={{ time() }}" alt="{{ $prov['label'] }}"
                                 class="w-full h-full object-contain p-0.5">
                        @else
                            <i class="{{ $prov['icon'] }} text-xl" style="color:{{ $prov['color'] }}"></i>
                        @endif
                    </div>

                    {{-- Nom + statut --}}
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-800 text-sm">{{ $prov['label'] }}</div>
                        <div class="text-xs mt-0.5">
                            @if($isActive)
                                <span class="text-green-600 font-medium"><i class="fas fa-circle text-xs mr-1"></i>Actif</span>
                            @else
                                <span class="text-gray-400"><i class="fas fa-circle text-xs mr-1"></i>Inactif</span>
                            @endif
                        </div>
                    </div>

                    {{-- Toggle actif --}}
                    <label class="relative inline-flex items-center cursor-pointer shrink-0">
                        <input type="checkbox" name="provider_{{ $key }}_active" value="1"
                               {{ $isActive ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-checked:bg-green-500 rounded-full transition-colors
                                    after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                    after:bg-white after:rounded-full after:w-5 after:h-5 after:transition-all
                                    peer-checked:after:translate-x-5"></div>
                    </label>

                    {{-- Expand API fields --}}
                    @if(!empty($prov['fields']))
                    <button type="button"
                            @click="expanded['{{ $key }}']=!expanded['{{ $key }}']"
                            class="shrink-0 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white/60 transition-colors text-gray-500">
                        <i class="fas fa-chevron-down text-xs transition-transform"
                           :class="expanded['{{ $key }}'] ? 'rotate-180' : ''"></i>
                    </button>
                    @endif
                </div>

                {{-- Note (visa_card) --}}
                @if(isset($prov['note']))
                <div class="px-4 pb-3 text-xs text-gray-500 flex items-start gap-2">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                    {{ $prov['note'] }}
                </div>
                @endif

                {{-- API credentials (expandable) --}}
                @if(!empty($prov['fields']))
                <div x-show="expanded['{{ $key }}']"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="border-t border-gray-100">
                    <div class="px-4 py-4 space-y-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Identifiants API</p>
                        @foreach($prov['fields'] as $field => $fieldDef)
                        <div>
                            <label class="form-label text-xs">{{ $fieldDef['label'] }}</label>
                            <div class="relative">
                                <input type="{{ $fieldDef['type'] }}"
                                       name="provider_{{ $key }}[{{ $field }}]"
                                       value="{{ $pd[$field] ?? '' }}"
                                       @if(isset($fieldDef['placeholder'])) placeholder="{{ $fieldDef['placeholder'] }}" @endif
                                       class="form-input text-sm font-mono {{ $fieldDef['type'] === 'password' ? 'pr-10' : '' }}">
                                @if($fieldDef['type'] === 'password')
                                <button type="button"
                                        @click="$el.closest('.relative').querySelector('input').type = $el.closest('.relative').querySelector('input').type === 'password' ? 'text' : 'password'"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye text-xs"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach

            <button type="submit" class="btn btn-primary mt-2">
                <i class="fas fa-save"></i> Enregistrer les méthodes
            </button>
        </form>
    </div>

    {{-- ── 4. Logos ───────────────────────────────────────────────── --}}
    <div class="card">
        <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <i class="fas fa-image text-pink-500"></i> Logos des méthodes de paiement
        </h3>
        <p class="text-xs text-gray-500 mb-5">PNG, JPG, SVG · max 512 Ko · Taille recommandée : 200×80 px</p>

        <form action="{{ route('admin.subscription.logos') }}" method="POST"
              enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($providers as $key => $prov)
                @php
                    $logoFile = AppSetting::get("payment_logo_{$key}", '');
                    $logoUrl  = $logoFile ? $logoBase . '/' . $logoFile : null;
                @endphp

                <div class="border-2 border-gray-100 rounded-2xl p-4 hover:border-gray-200 transition-colors"
                     x-data="{ preview: '{{ $logoUrl ?? '' }}' }">

                    {{-- Provider header --}}
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-6 h-6 rounded-lg flex items-center justify-center"
                             style="background:{{ $prov['color'] }}20;">
                            <i class="{{ $prov['icon'] }} text-xs" style="color:{{ $prov['color'] }}"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ $prov['label'] }}</span>
                    </div>

                    {{-- Logo preview --}}
                    <div class="w-full h-20 rounded-xl border-2 border-dashed border-gray-200 flex items-center justify-center mb-3 overflow-hidden bg-white"
                         style="{{ $logoUrl ? '' : '' }}">
                        <template x-if="preview">
                            <img :src="preview" class="max-h-16 max-w-full object-contain">
                        </template>
                        <template x-if="!preview">
                            <div class="text-center text-gray-300">
                                <i class="fas fa-image text-2xl mb-1 block"></i>
                                <span class="text-xs">Aucun logo</span>
                            </div>
                        </template>
                    </div>

                    {{-- File input --}}
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="file" name="logos[{{ $key }}]" accept="image/*" class="hidden"
                               @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview">
                        <div class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-xs text-gray-500
                                    group-hover:border-blue-300 group-hover:text-blue-500 transition-colors flex items-center gap-2">
                            <i class="fas fa-upload"></i>
                            <span x-text="preview ? 'Changer le logo' : 'Choisir un logo'"></span>
                        </div>
                    </label>
                </div>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-cloud-upload-alt"></i> Enregistrer les logos
            </button>
        </form>
    </div>

</div>

@push('scripts')
<script>
function subscriptionAdmin() {
    return {
        showApiKey: false,
        expanded: {
            orange_money: false,
            mtn_money:    false,
            wave:         false,
            moov_money:   false,
            visa_card:    false,
        },
    };
}
</script>
@endpush
@endsection
