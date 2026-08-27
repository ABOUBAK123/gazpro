<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CommissionTransaction;

class CommissionnaireController extends Controller
{
    private function current()
    {
        return Auth::guard('commissionnaire')->user();
    }

    public function dashboard()
    {
        $commissionnaire = $this->current();
        $storesCount = $commissionnaire->stores()->count();
        $totalEarned = $commissionnaire->transactions()->where('type', 'credit')->sum('amount');
        $recent = $commissionnaire->transactions()->latest()->limit(5)->get();

        return view('commissionnaire.dashboard', compact('commissionnaire', 'storesCount', 'totalEarned', 'recent'));
    }

    public function stores()
    {
        $commissionnaire = $this->current();
        $stores = $commissionnaire->stores()->latest()->get();

        return view('commissionnaire.stores', compact('commissionnaire', 'stores'));
    }

    public function transactions()
    {
        $commissionnaire = $this->current();
        $transactions = $commissionnaire->transactions()->latest()->paginate(20);

        return view('commissionnaire.transactions', compact('commissionnaire', 'transactions'));
    }
}
