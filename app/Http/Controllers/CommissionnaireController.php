<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CommissionTransaction;
use App\Models\Commissionnaire;
use App\Services\MtnMomoDisbursementService;
use RuntimeException;

class CommissionnaireController extends Controller
{
    private const MIN_WITHDRAWAL = 1000;

    private function current(): Commissionnaire
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

    public function withdraw(Request $request, MtnMomoDisbursementService $disbursement)
    {
        $commissionnaire = $this->current();

        $request->validate([
            'amount' => 'required|numeric|min:' . self::MIN_WITHDRAWAL,
            'phone'  => 'required|string|min:8|max:15',
        ], [
            'amount.min' => 'Le montant minimum de retrait est de ' . self::MIN_WITHDRAWAL . ' XOF.',
        ]);

        if ($request->amount > $commissionnaire->balance) {
            return response()->json(['error' => 'Solde insuffisant.'], 422);
        }

        $transaction = CommissionTransaction::create([
            'commissionnaire_id' => $commissionnaire->id,
            'type'                => 'withdrawal',
            'amount'              => $request->amount,
            'status'              => 'pending',
            'phone'               => $request->phone,
        ]);

        // Debit immediately to prevent double-withdrawal from concurrent requests;
        // refunded automatically if the transfer is later confirmed FAILED.
        $commissionnaire->decrement('balance', $request->amount);

        try {
            $referenceId = $disbursement->transfer(
                amount: (float) $request->amount,
                phone: $request->phone,
                externalId: (string) $transaction->id,
            );
        } catch (RuntimeException $e) {
            $transaction->update(['status' => 'failed']);
            $commissionnaire->increment('balance', $request->amount);
            return response()->json(['error' => $e->getMessage()], 422);
        }

        $transaction->update(['reference' => $referenceId]);

        return response()->json(['transaction_id' => $transaction->id, 'status' => 'pending']);
    }

    public function pollWithdraw(Request $request, CommissionTransaction $transaction, MtnMomoDisbursementService $disbursement)
    {
        $commissionnaire = $this->current();
        abort_unless($transaction->commissionnaire_id === $commissionnaire->id, 403);

        if ($transaction->status !== 'pending') {
            return response()->json(['status' => $transaction->status]);
        }

        try {
            $result = $disbursement->checkStatus($transaction->reference);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'pending']);
        }

        $mtnStatus = $result['status'] ?? 'PENDING';

        if ($mtnStatus === 'SUCCESSFUL') {
            $transaction->update(['status' => 'completed']);
            return response()->json(['status' => 'completed']);
        }

        if ($mtnStatus === 'FAILED') {
            $transaction->update(['status' => 'failed']);
            $commissionnaire->increment('balance', $transaction->amount);
            return response()->json(['status' => 'failed']);
        }

        return response()->json(['status' => 'pending']);
    }
}
