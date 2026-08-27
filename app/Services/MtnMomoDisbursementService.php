<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MtnMomoDisbursementService
{
    private array $config;

    public function __construct()
    {
        $this->config = AppSetting::get('payment_provider_mtn_disbursement', []);
    }

    private function isSandbox(): bool
    {
        return ($this->config['target_environment'] ?? 'sandbox') === 'sandbox';
    }

    private function baseUrl(): string
    {
        if (!empty($this->config['base_url'])) {
            return rtrim($this->config['base_url'], '/');
        }

        return $this->isSandbox()
            ? 'https://sandbox.momodeveloper.mtn.com'
            : 'https://proxy.momoapi.mtn.com';
    }

    private function currency(string $planCurrency): string
    {
        return $this->isSandbox() ? 'EUR' : $planCurrency;
    }

    public function getToken(): string
    {
        $apiUser = $this->config['api_user'] ?? null;
        $apiKey  = $this->config['api_key'] ?? null;
        $subKey  = $this->config['subscription_key'] ?? null;

        if (!$apiUser || !$apiKey || !$subKey) {
            throw new RuntimeException("MTN MoMo Disbursement n'est pas configuré.");
        }

        return Cache::remember('mtn_momo_disbursement_token_' . md5($apiUser), 3300, function () use ($apiUser, $apiKey, $subKey) {
            $response = Http::withBasicAuth($apiUser, $apiKey)
                ->withHeaders(['Ocp-Apim-Subscription-Key' => $subKey])
                ->timeout(15)
                ->post($this->baseUrl() . '/disbursement/token/');

            if (!$response->successful()) {
                throw new RuntimeException("Impossible d'obtenir un jeton MTN MoMo Disbursement.");
            }

            return $response->json()['access_token'];
        });
    }

    public function transfer(float $amount, string $phone, string $externalId, string $payerMessage = 'Commission GazManager', string $currency = 'XOF'): string
    {
        $referenceId = (string) Str::uuid();
        $token = $this->getToken();
        $subKey = $this->config['subscription_key'] ?? null;

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Reference-Id'            => $referenceId,
                'X-Target-Environment'      => $this->config['target_environment'] ?? 'sandbox',
                'Ocp-Apim-Subscription-Key' => $subKey,
                'Content-Type'              => 'application/json',
            ])
            ->timeout(20)
            ->post($this->baseUrl() . '/disbursement/v1_0/transfer', [
                'amount'       => (string) (int) $amount,
                'currency'     => $this->currency($currency),
                'externalId'   => $externalId,
                'payee'        => ['partyIdType' => 'MSISDN', 'partyId' => $phone],
                'payerMessage' => $payerMessage,
                'payeeNote'    => $payerMessage,
            ]);

        if ($response->status() !== 202) {
            throw new RuntimeException('MTN MoMo a refusé la demande de retrait.');
        }

        return $referenceId;
    }

    public function checkStatus(string $referenceId): array
    {
        $token = $this->getToken();

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Target-Environment'      => $this->config['target_environment'] ?? 'sandbox',
                'Ocp-Apim-Subscription-Key' => $this->config['subscription_key'] ?? null,
            ])
            ->timeout(15)
            ->get($this->baseUrl() . "/disbursement/v1_0/transfer/{$referenceId}");

        if (!$response->successful()) {
            throw new RuntimeException('Impossible de vérifier le statut du retrait MTN.');
        }

        return $response->json();
    }
}
