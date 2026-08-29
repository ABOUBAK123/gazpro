<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class AdminPaymentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $payments = Payment::with(['store'])
            ->when($status && in_array($status, ['pending', 'completed', 'failed']), fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $counts = [
            'all'       => Payment::count(),
            'completed' => Payment::where('status', 'completed')->count(),
            'failed'    => Payment::where('status', 'failed')->count(),
            'pending'   => Payment::where('status', 'pending')->count(),
        ];

        $totalRevenue = Payment::where('status', 'completed')->sum('amount');

        return view('admin.payments', compact('payments', 'counts', 'status', 'totalRevenue'));
    }
}
