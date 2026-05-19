<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\AppSetting;

class AdminSettingsController extends Controller
{
    private function normalizeBrands(array $brands): array
    {
        return array_map(fn($b) => is_string($b) ? ['name' => $b, 'logo' => null] : $b, $brands);
    }

    public function saveDeliveryFee(Request $request)
    {
        $request->validate(['delivery_fee' => 'required|numeric|min:0']);
        AppSetting::set('delivery_fee', (float) $request->delivery_fee);
        return back()->with('success', 'Frais de livraison mis à jour.');
    }

    public function index()
    {
        $deliveryFee = AppSetting::get('delivery_fee', 0);
        $brands = $this->normalizeBrands(AppSetting::get('brands', ['Total', 'Shell', 'Oryx', 'Sodigaz', 'Petrogaz']));
        $weights = AppSetting::get('weights', [
            ['value' => '6kg',  'code' => 'B6'],
            ['value' => '12kg', 'code' => 'B12'],
            ['value' => '25kg', 'code' => 'B25'],
        ]);
        $terms = AppSetting::get('terms_of_service', '');
        $emailConfig = AppSetting::get('email_config', [
            'host'       => '',
            'port'       => '587',
            'username'   => '',
            'from_email' => '',
            'from_name'  => 'GazManager',
            'encryption' => 'tls',
        ]);

        return view('admin.settings', compact('brands', 'weights', 'terms', 'emailConfig', 'deliveryFee'));
    }

    public function addBrand(Request $request)
    {
        $request->validate(['brand' => 'required|string|max:100']);
        $brands = $this->normalizeBrands(AppSetting::get('brands', []));
        $name   = trim($request->brand);
        if (!collect($brands)->where('name', $name)->count()) {
            $brands[] = ['name' => $name, 'logo' => null];
            AppSetting::set('brands', $brands);
        }
        return back()->with('success', 'Marque ajoutée.');
    }

    public function deleteBrand(Request $request)
    {
        $request->validate(['brand' => 'required|string']);
        $brands = $this->normalizeBrands(AppSetting::get('brands', []));
        $found  = collect($brands)->firstWhere('name', $request->brand);
        if ($found && $found['logo']) {
            @unlink(public_path($found['logo']));
        }
        $brands = array_values(array_filter($brands, fn($b) => $b['name'] !== $request->brand));
        AppSetting::set('brands', $brands);
        return back()->with('success', 'Marque supprimée.');
    }

    public function uploadBrandLogo(Request $request, string $brandName)
    {
        $request->validate(['logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048']);
        $brands = $this->normalizeBrands(AppSetting::get('brands', []));
        $idx    = null;
        foreach ($brands as $i => $b) {
            if ($b['name'] === $brandName) { $idx = $i; break; }
        }
        if ($idx === null) return back()->with('error', 'Marque introuvable.');

        if ($brands[$idx]['logo']) {
            @unlink(public_path($brands[$idx]['logo']));
        }

        $ext      = $request->file('logo')->getClientOriginalExtension();
        $slug     = Str::slug($brandName);
        $filename = $slug . '.' . $ext;
        $request->file('logo')->move(public_path('brands'), $filename);

        $brands[$idx]['logo'] = 'brands/' . $filename;
        AppSetting::set('brands', $brands);

        return back()->with('success', 'Logo uploadé pour ' . $brandName . '.');
    }

    public function addWeight(Request $request)
    {
        $request->validate([
            'weight_value' => 'required|string|max:50',
            'weight_code'  => 'required|string|max:20',
        ]);
        $weights = AppSetting::get('weights', []);
        $weights[] = ['value' => trim($request->weight_value), 'code' => trim($request->weight_code)];
        AppSetting::set('weights', $weights);
        return back()->with('success', 'Poids ajouté.');
    }

    public function deleteWeight(Request $request)
    {
        $request->validate(['weight_value' => 'required|string']);
        $weights = AppSetting::get('weights', []);
        $weights = array_values(array_filter($weights, fn($w) => $w['value'] !== $request->weight_value));
        AppSetting::set('weights', $weights);
        return back()->with('success', 'Poids supprimé.');
    }

    public function saveTerms(Request $request)
    {
        $request->validate(['terms' => 'nullable|string']);
        AppSetting::set('terms_of_service', $request->terms ?? '');
        return back()->with('success', 'Conditions mises à jour.');
    }

    public function saveEmailConfig(Request $request)
    {
        $request->validate([
            'email_host'       => 'nullable|string|max:255',
            'email_port'       => 'nullable|integer',
            'email_username'   => 'nullable|string|max:255',
            'email_password'   => 'nullable|string|max:255',
            'email_from_email' => 'nullable|email|max:255',
            'email_from_name'  => 'nullable|string|max:255',
            'email_encryption' => 'nullable|in:tls,ssl,none',
        ]);

        AppSetting::set('email_config', [
            'host'       => $request->email_host,
            'port'       => $request->email_port ?? 587,
            'username'   => $request->email_username,
            'from_email' => $request->email_from_email,
            'from_name'  => $request->email_from_name ?? 'GazManager',
            'encryption' => $request->email_encryption ?? 'tls',
        ]);

        return back()->with('success', 'Configuration email sauvegardée.');
    }
}
