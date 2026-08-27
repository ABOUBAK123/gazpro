<?php

namespace App\Services;

use App\Models\AppSetting;

class MailConfigService
{
    /**
     * Applies the admin-configured SMTP settings (AppSetting 'email_config')
     * to the runtime Laravel mail config. Falls back to .env defaults
     * (log driver in dev) if no SMTP host has been configured.
     */
    public static function apply(): void
    {
        $config = AppSetting::get('email_config', []);
        if (empty($config['host'])) {
            return;
        }

        config([
            'mail.default'                  => 'smtp',
            'mail.mailers.smtp.host'        => $config['host'],
            'mail.mailers.smtp.port'        => $config['port'] ?? 587,
            'mail.mailers.smtp.username'    => $config['username'] ?? null,
            'mail.mailers.smtp.password'    => $config['password'] ?? null,
            'mail.mailers.smtp.encryption'  => ($config['encryption'] ?? 'tls') === 'none' ? null : $config['encryption'],
            'mail.from.address'             => $config['from_email'] ?? config('mail.from.address'),
            'mail.from.name'                => $config['from_name'] ?? config('mail.from.name'),
        ]);
    }
}
