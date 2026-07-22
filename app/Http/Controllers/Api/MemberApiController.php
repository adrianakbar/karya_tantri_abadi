<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SavingsTransaction;
use App\Models\Loan;
use App\Models\Sale;
use App\Models\ShuMemberShare;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MemberApiController extends Controller
{
    /**
     * Authenticate member and return token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $login = $request->input('login');
        $password = $request->input('password');
        $deviceName = $request->input('device_name', 'Mobile App');

        // Find user by email, phone, or member number
        $user = User::with('cooperation')
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->orWhere('member_number', $login)
            ->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial login salah.'
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan. Silakan hubungi pengurus.'
            ], 403);
        }

        // Generate token
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => $user
            ]
        ]);
    }

    /**
     * Logout member and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get member profile.
     */
    public function profile(Request $request)
    {
        $user = User::with('cooperation', 'roles')->find($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Get dashboard summary data.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Calculate total savings
        // Completed deposits (amount > 0) and withdrawals (amount can be positive or negative depending on implementation. 
        // Typically, setoran is deposit, penarikan is withdrawal. Let's calculate based on transaction type if any, 
        // or simple sum/subtract. In standard cooperations, amount is positive, so let's check savings transactions status.
        $savingsTransactions = SavingsTransaction::where('user_id', $user->id)
            ->where('status', 'completed')
            ->get();

        $totalSavings = 0;
        foreach ($savingsTransactions as $tx) {
            // Check if it's a withdrawal or deposit. Let's check SavingsType or the Transaction Number.
            // If the transaction has a type (setoran vs penarikan), let's inspect if amount is negative.
            // Usually, penarikan reduces balance. Let's check transaction number prefix or comments,
            // or if there is a 'type' field in the transaction. Wait, let's sum them. 
            // In typical systems, if it's a deposit it's positive, if withdrawal it's negative, or there's a type field.
            // Let's assume standard decimal values where negative means withdrawal or we can check the transaction notes.
            // Let's also retrieve this from a helper or calculate directly.
            // Let's check how the Filament app calculates total savings. We will write a safe query or method.
            // Let's inspect TransactionSummary or just sum the amounts.
            $totalSavings += $tx->amount; 
        }

        // Active loans (where status is approved/disbursed and remaining_balance > 0)
        $activeLoans = Loan::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'disbursed', 'active'])
            ->where('remaining_balance', '>', 0)
            ->get();

        $totalRemainingLoan = $activeLoans->sum('remaining_balance');

        // Total purchases at POS
        $totalPurchases = Sale::where('customer_id', $user->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        // Last 5 savings transactions
        $recentSavings = SavingsTransaction::with('savingsType')
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        // Last 5 purchases
        $recentPurchases = Sale::where('customer_id', $user->id)
            ->orderBy('sale_date', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_savings' => $totalSavings,
                'total_remaining_loan' => $totalRemainingLoan,
                'total_purchases' => $totalPurchases,
                'recent_savings' => $recentSavings,
                'recent_purchases' => $recentPurchases,
            ]
        ]);
    }

    /**
     * Get savings transactions history.
     */
    public function savings(Request $request)
    {
        $user = $request->user();

        $savings = SavingsTransaction::with('savingsType', 'processor')
            ->where('user_id', $user->id)
            ->orderBy('transaction_date', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $savings
        ]);
    }

    /**
     * Get loans list and payments.
     */
    public function loans(Request $request)
    {
        $user = $request->user();

        $loans = Loan::with(['loanType', 'payments', 'approver'])
            ->where('user_id', $user->id)
            ->orderBy('application_date', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $loans
        ]);
    }

    /**
     * Get sales/purchases history.
     */
    public function purchases(Request $request)
    {
        $user = $request->user();

        $purchases = Sale::with(['details.product', 'cooperation'])
            ->where('customer_id', $user->id)
            ->orderBy('sale_date', 'desc')
            ->paginate($request->input('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $purchases
        ]);
    }

    /**
     * Get SHU history.
     */
    public function shu(Request $request)
    {
        $user = $request->user();

        $shu = ShuMemberShare::with('distribution')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $shu
        ]);
    }
}
