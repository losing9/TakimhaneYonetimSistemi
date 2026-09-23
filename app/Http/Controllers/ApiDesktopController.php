<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Station;
use App\Models\Tool;
use App\Models\Toolroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiDesktopController extends Controller
{
    /** POST /api/desktop/auth/login */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['error' => 'Geçersiz e-posta veya şifre.'], 401);
        }

        $user = Auth::user();

        // Takımhane sorumlusu veya Super Admin kontrolü
        if (! in_array($user->role, ['super_admin', 'takimhane_sor', 'admin'])) {
            return response()->json(['error' => 'Masaüstü paneline sadece yöneticiler ve takımhane sorumluları giriş yapabilir.'], 403);
        }

        $token = $user->createToken('desktop-terminal')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $user->role,
                'toolroom_id'   => $user->toolroom_id,
                'toolroom_name' => $user->toolroom?->name ?? 'Tüm Takımhaneler (Genel Yönetim)',
            ],
        ]);
    }

    /** GET /api/desktop/config */
    public function config(Request $request)
    {
        $toolrooms = Toolroom::where('is_active', true)->get(['id', 'name', 'code']);
        $stations  = Station::where('is_active', true)->get(['id', 'name', 'toolroom_id']);

        return response()->json([
            'toolrooms' => $toolrooms,
            'stations'  => $stations,
        ]);
    }

    /** GET /api/desktop/tools */
    public function tools(Request $request)
    {
        $query = Tool::with(['slot.shelf.block', 'toolroom', 'toolGroup', 'activeLoan.personnel']);

        if ($request->filled('toolroom_id')) {
            $query->where('toolroom_id', $request->toolroom_id);
        } elseif ($request->user() && $request->user()->toolroom_id) {
            $query->where('toolroom_id', $request->user()->toolroom_id);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('serial_no', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tools = $query->limit(50)->get()->map(function ($t) {
            $activeLoan = $t->activeLoan;
            return [
                'id'            => $t->id,
                'name'          => $t->name,
                'serial_no'     => $t->serial_no,
                'barcode'       => $t->barcode,
                'status'        => $t->status,
                'status_label'  => $t->status_label,
                'category'      => $t->category_label,
                'toolroom_id'   => $t->toolroom_id,
                'toolroom_name' => $t->toolroom?->name,
                'tool_group'    => $t->toolGroup?->name,
                'location'      => $t->location_label,
                'image_url'     => $t->image ? asset('storage/' . $t->image) : null,
                'max_loan_days' => $t->max_loan_days ?? 7,
                'is_available'  => $t->status === 'available',
                'active_loan'   => $activeLoan ? [
                    'id'                => $activeLoan->id,
                    'personnel_id'      => $activeLoan->personnel_id,
                    'personnel_name'    => $activeLoan->personnel?->name,
                    'badge_number'      => $activeLoan->personnel?->badge_number,
                    'loaned_at'         => $activeLoan->loaned_at?->format('d.m.Y H:i'),
                    'planned_return_at' => $activeLoan->planned_return_at?->format('d.m.Y'),
                    'is_overdue'        => $activeLoan->isOverdue(),
                ] : null,
            ];
        });

        return response()->json(['tools' => $tools]);
    }

    /** GET /api/desktop/personnel */
    public function personnel(Request $request)
    {
        $query = Personnel::where('is_active', true)->withCount(['activeLoans']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('badge_number', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('department', 'like', "%{$s}%");
            });
        }

        $list = $query->limit(50)->get()->map(function ($p) {
            return [
                'id'                 => $p->id,
                'name'               => $p->name,
                'badge_number'       => $p->badge_number,
                'department'         => $p->department,
                'phone'              => $p->phone,
                'active_loans_count' => $p->active_loans_count,
            ];
        });

        return response()->json(['personnel' => $list]);
    }

    /** POST /api/desktop/loan/checkout */
    public function checkout(Request $request)
    {
        $request->validate([
            'tool_identifier'      => 'required|string',
            'personnel_identifier' => 'required|string',
            'loan_days'            => 'nullable|integer|min:1|max:90',
            'notes'                => 'nullable|string|max:500',
            'station_id'           => 'nullable|exists:stations,id',
        ]);

        $tool = Tool::where('id', $request->tool_identifier)
            ->orWhere('serial_no', $request->tool_identifier)
            ->orWhere('barcode', $request->tool_identifier)
            ->first();

        if (! $tool) {
            return response()->json(['error' => 'Takım bulunamadı.'], 404);
        }

        if ($tool->status !== 'available') {
            return response()->json(['error' => 'Bu takım şu anda müsait değil (Durum: ' . $tool->status_label . ').'], 409);
        }

        $personnel = Personnel::where('id', $request->personnel_identifier)
            ->orWhere('badge_number', $request->personnel_identifier)
            ->first();

        if (! $personnel || ! $personnel->is_active) {
            return response()->json(['error' => 'Geçerli veya aktif bir personel bulunamadı.'], 404);
        }

        $days = (int) ($request->loan_days ?: ($tool->max_loan_days ?: 7));

        $loan = Loan::create([
            'toolroom_id'       => $tool->toolroom_id,
            'tool_id'           => $tool->id,
            'personnel_id'      => $personnel->id,
            'loaned_at'         => now(),
            'planned_return_at' => now()->addDays($days),
            'status'            => 'active',
            'notes'             => $request->notes ?: 'Masaüstü terminalinden zimmetlendi.',
            'loaned_by_user_id' => $request->user()->id,
        ]);

        $tool->update(['status' => 'loaned']);

        return response()->json([
            'success'     => true,
            'message'     => $tool->name . ' başarıyla ' . $personnel->name . ' adlı personele zimmetlendi.',
            'loan_id'     => $loan->id,
            'tool_name'   => $tool->name,
            'personnel'   => $personnel->name,
            'return_date' => $loan->planned_return_at->format('d.m.Y'),
        ]);
    }

    /** POST /api/desktop/loan/checkin */
    public function checkin(Request $request)
    {
        $request->validate([
            'tool_identifier'  => 'required|string',
            'station_id'       => 'nullable|exists:stations,id',
            'condition_notes'  => 'nullable|string|max:500',
        ]);

        $tool = Tool::with(['slot.shelf.block'])
            ->where('id', $request->tool_identifier)
            ->orWhere('serial_no', $request->tool_identifier)
            ->orWhere('barcode', $request->tool_identifier)
            ->first();

        if (! $tool) {
            return response()->json(['error' => 'Takım bulunamadı.'], 404);
        }

        $loan = Loan::where('tool_id', $tool->id)
            ->whereIn('status', ['active', 'overdue'])
            ->latest('loaned_at')
            ->first();

        if (! $loan) {
            if ($tool->status === 'loaned') {
                $tool->update(['status' => 'available']);
                return response()->json([
                    'success'  => true,
                    'message'  => $tool->name . ' durumu müsait olarak güncellendi.',
                    'location' => $tool->location_label,
                ]);
            }
            return response()->json(['error' => 'Bu takım için aktif bir zimmet kaydı bulunamadı.'], 404);
        }

        $loan->update([
            'returned_at'            => now(),
            'status'                 => 'returned',
            'return_condition_notes' => $request->condition_notes,
            'return_station_id'      => $request->station_id,
        ]);

        $tool->update(['status' => 'available']);

        return response()->json([
            'success'   => true,
            'message'   => $tool->name . ' başarıyla iade alındı.',
            'tool_name' => $tool->name,
            'location'  => $tool->location_label,
        ]);
    }
}
