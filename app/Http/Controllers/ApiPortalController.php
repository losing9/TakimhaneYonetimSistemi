<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Station;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiPortalController extends Controller
{
    /** POST /api/auth/login */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['error' => 'E-posta veya şifre hatalı.'], 401);
        }

        $user  = Auth::user();
        $token = $user->createToken('portal-app')->plainTextToken;

        return response()->json([
            'token'      => $token,
            'user'       => ['id' => $user->id, 'name' => $user->name, 'role' => $user->role],
            'personnelId' => $user->personnel?->id,
        ]);
    }

    /** POST /api/auth/logout */
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Çıkış yapıldı.']);
    }

    /** GET /api/portal/tool/{serial} */
    public function findTool(string $serial)
    {
        $tool = Tool::where('serial_no', $serial)->with('slot.shelf.block')->first();

        if (! $tool) {
            return response()->json(['error' => 'Alet bulunamadı.'], 404);
        }

        return response()->json([
            'id'        => $tool->id,
            'name'      => $tool->name,
            'serial_no' => $tool->serial_no,
            'status'    => $tool->status,
            'status_label' => $tool->status_label,
            'category'  => $tool->category_label,
            'location'  => $tool->location_label,
            'available' => $tool->status === 'available',
        ]);
    }

    /** POST /api/portal/loan */
    public function takeLoan(Request $request)
    {
        $request->validate([
            'tool_id'   => 'required|exists:tools,id',
            'loan_days' => 'required|integer|min:1|max:90',
        ]);

        $personnel = $request->user()->personnel;
        if (! $personnel) {
            return response()->json(['error' => 'Personel kaydı bulunamadı.'], 403);
        }

        $tool = Tool::find($request->tool_id);
        if ($tool->status !== 'available') {
            return response()->json(['error' => 'Bu alet müsait değil.'], 409);
        }

        $loan = Loan::create([
            'tool_id'           => $tool->id,
            'personnel_id'      => $personnel->id,
            'loaned_at'         => now(),
            'planned_return_at' => now()->addDays((int) $request->loan_days),
            'status'            => 'active',
            'notes'             => 'Mobil uygulama üzerinden alındı.',
            'loaned_by_user_id' => $request->user()->id,
        ]);

        $tool->update(['status' => 'loaned']);

        return response()->json([
            'message' => 'Zimmet başarıyla oluşturuldu.',
            'loan_id' => $loan->id,
            'tool'    => $tool->name,
            'return_date' => $loan->planned_return_at->format('d.m.Y'),
        ]);
    }

    /** POST /api/portal/verify-station */
    public function verifyStation(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $station = Station::where('qr_token', $request->token)->where('is_active', true)->first();

        if (! $station) {
            return response()->json(['error' => 'Geçersiz istasyon QR kodu.'], 404);
        }

        return response()->json([
            'station_id'   => $station->id,
            'station_name' => $station->name,
        ]);
    }

    /** POST /api/portal/return */
    public function returnLoan(Request $request)
    {
        $request->validate([
            'loan_id'    => 'required|exists:loans,id',
            'station_id' => 'required|exists:stations,id',
            'photo'      => 'required|image|max:8192',
            'notes'      => 'nullable|string|max:500',
        ]);

        $personnel = $request->user()->personnel;
        $loan      = Loan::with('tool')->findOrFail($request->loan_id);

        if ($personnel && $loan->personnel_id !== $personnel->id) {
            return response()->json(['error' => 'Bu zimmet size ait değil.'], 403);
        }

        $photoPath = $request->file('photo')->store('return-photos/' . now()->format('Y/m'), 'public');

        $loan->update([
            'returned_at'            => now(),
            'status'                 => 'returned',
            'return_photo'           => $photoPath,
            'return_condition_notes' => $request->notes,
            'return_station_id'      => $request->station_id,
        ]);

        $loan->tool->update(['status' => 'available']);

        return response()->json([
            'message' => 'İade başarıyla kaydedildi.',
            'tool'    => $loan->tool->name,
        ]);
    }

    /** GET /api/portal/my-loans */
    public function myLoans(Request $request)
    {
        $personnel = $request->user()->personnel;

        if (! $personnel) {
            return response()->json(['loans' => []]);
        }

        $loans = Loan::with('tool')
            ->where('personnel_id', $personnel->id)
            ->whereIn('status', ['active', 'overdue'])
            ->orderByDesc('loaned_at')
            ->get()
            ->map(fn ($l) => [
                'id'           => $l->id,
                'tool_name'    => $l->tool->name,
                'serial_no'    => $l->tool->serial_no,
                'loaned_at'    => $l->loaned_at->format('d.m.Y'),
                'return_date'  => $l->planned_return_at->format('d.m.Y'),
                'overdue'      => $l->isOverdue(),
                'overdue_days' => $l->overdue_days,
                'status'       => $l->status_label,
            ]);

        return response()->json(['loans' => $loans]);
    }
}
