<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Values are stored encrypted at rest (Crypt::encryptString wrapping the
     * JSON payload), since this table holds live payment-gateway credentials
     * (CinetPay, MTN MoMo) alongside harmless config. get() transparently
     * falls back to reading legacy plaintext-JSON rows (written before this
     * encryption was introduced) so existing settings keep working — the
     * next set() call on that key re-saves it encrypted.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) return $default;

        $raw = $setting->value;
        try {
            $raw = Crypt::decryptString($setting->value);
        } catch (DecryptException $e) {
            // Legacy unencrypted row — decode as-is below.
        }

        $decoded = json_decode($raw, true);
        return $decoded !== null ? $decoded : $raw;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => Crypt::encryptString(json_encode($value))]
        );
    }
}
