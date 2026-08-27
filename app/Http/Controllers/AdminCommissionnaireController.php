<?php

namespace App\Http\Controllers;

use App\Models\Commissionnaire;
use App\Models\CommissionTransaction;

class AdminCommissionnaireController extends Controller
{
    public function index()
    {
        $pending = Commissionnaire::where('status', 'pending')->latest()->get();
        $active  = Commissionnaire::where('status', 'active')->withCount('stores')->latest()->get();
        $rejected = Commissionnaire::where('status', 'rejected')->latest()->get();

        return view('admin.commissionnaires', compact('pending', 'active', 'rejected'));
    }

    public function approve(Commissionnaire $commissionnaire)
    {
        $commissionnaire->update(['status' => 'active']);
        return back()->with('success', "\"{$commissionnaire->name}\" a été approuvé.");
    }

    public function reject(Commissionnaire $commissionnaire)
    {
        $commissionnaire->update(['status' => 'rejected']);
        return back()->with('error', "\"{$commissionnaire->name}\" a été rejeté.");
    }

    public function transactions()
    {
        $transactions = CommissionTransaction::with(['commissionnaire', 'store'])
            ->latest()
            ->paginate(30);

        $commissionnaires = Commissionnaire::orderBy('name')->get(['id', 'name']);

        return view('admin.commission-transactions', compact('transactions', 'commissionnaires'));
    }
}
