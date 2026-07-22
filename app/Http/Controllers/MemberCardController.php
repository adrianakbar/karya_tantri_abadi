<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class MemberCardController extends Controller
{
    public function printSingle(User $user)
    {
        $user->load('cooperation');

        $pdf = Pdf::loadView('components.member-card.single', compact('user'));

        $filename = "kartu-anggota-{$user->member_number}-" . now()->format('Y-m-d') . ".pdf";
        return $pdf->download($filename);
    }

    public function printMultiple(Request $request)
    {
        $ids = explode(',', $request->get('ids'));
        $users = User::whereIn('id', $ids)
            ->with('cooperation')
            ->where('is_active', true)
            ->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada anggota yang dipilih atau anggota tidak aktif.');
        }

        $pdf = Pdf::loadView('components.member-card.multiple', ['users' => $users])
            ->setPaper([0, 0, 226.77, 153.07], 'landscape'); // Ukuran kartu kredit

        $filename = 'kartu-anggota-batch-' . now()->format('Y-m-d-H-i-s') . '.pdf';
        return $pdf->download($filename);
    }

}