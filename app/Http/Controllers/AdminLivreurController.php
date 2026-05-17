<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Livreur;
use Illuminate\Support\Str;

class AdminLivreurController extends Controller
{
    public function index()
    {
        $livreurs = Livreur::withCount([
            'orders as active_count'    => fn($q) => $q->whereIn('status', ['confirmed', 'en_route']),
            'orders as delivered_count' => fn($q) => $q->where('status', 'delivered'),
        ])->latest()->get();

        return view('admin.livreurs.index', compact('livreurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'vehicle_type'  => 'required|in:moto,tricycle,voiture',
            'vehicle_plate' => 'nullable|string|max:20',
        ]);

        Livreur::create($request->only('name', 'phone', 'vehicle_type', 'vehicle_plate'));

        return back()->with('success', 'Livreur ajouté avec succès.');
    }

    public function update(Request $request, Livreur $livreur)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'phone'         => 'required|string|max:20',
            'vehicle_type'  => 'required|in:moto,tricycle,voiture',
            'vehicle_plate' => 'nullable|string|max:20',
            'status'        => 'required|in:active,inactive',
        ]);

        $livreur->update($request->only('name', 'phone', 'vehicle_type', 'vehicle_plate', 'status'));

        return back()->with('success', 'Livreur mis à jour.');
    }

    public function destroy(Livreur $livreur)
    {
        $livreur->delete();
        return back()->with('success', 'Livreur supprimé.');
    }

    public function regenerateToken(Livreur $livreur)
    {
        $livreur->update(['access_token' => Str::random(48)]);
        return back()->with('success', "Lien d'accès régénéré.");
    }
}
