<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Plan;
use App\Models\Payment;
use App\Models\Store;

class AdminPlanController extends Controller
{
    public function index()
    {
        $plans = Plan::orderBy('sort_order')->get();
        return view('admin.plans', compact('plans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'price'         => 'required|numeric|min:0',
            'currency'      => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
        ]);

        Plan::create([
            'name'          => $request->name,
            'slug'          => $this->uniqueSlug($request->name),
            'price'         => $request->price,
            'currency'      => $request->currency,
            'duration_days' => $request->duration_days,
            'is_active'     => true,
            'sort_order'    => (int) Plan::max('sort_order') + 1,
        ]);

        return back()->with('success', 'Formule ajoutée avec succès.');
    }

    public function update(Request $request, Plan $plan)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'price'         => 'required|numeric|min:0',
            'currency'      => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
        ]);

        $plan->update([
            'name'          => $request->name,
            'price'         => $request->price,
            'currency'      => $request->currency,
            'duration_days' => $request->duration_days,
        ]);

        return back()->with('success', 'Formule mise à jour.');
    }

    public function toggle(Plan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);
        return back()->with('success', $plan->is_active ? 'Formule activée.' : 'Formule désactivée.');
    }

    public function destroy(Plan $plan)
    {
        if (Payment::where('plan_id', $plan->id)->exists() || Store::where('plan_id', $plan->id)->exists()) {
            return back()->with('error', "Cette formule est déjà utilisée et ne peut pas être supprimée. Désactivez-la à la place.");
        }

        $plan->delete();
        return back()->with('success', 'Formule supprimée.');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;
        while (Plan::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
