<?php

namespace App\Services;

use App\Models\Store;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Facades\Storage;

class StoreQrService
{
    public function generate(Store $store): void
    {
        if ($store->qr_code_path && Storage::disk('public')->exists($store->qr_code_path)) {
            return;
        }

        $url = route('app.download', $store->qr_token);

        $result = (new Builder(
            writer: new SvgWriter(),
            data: $url,
            size: 400,
            margin: 10,
        ))->build();

        $path = "qrcodes/{$store->qr_token}.svg";
        Storage::disk('public')->put($path, $result->getString());

        $store->update([
            'qr_code_path'    => $path,
            'qr_generated_at' => now(),
        ]);
    }
}
