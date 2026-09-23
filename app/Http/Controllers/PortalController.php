<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Slot;
use App\Models\Station;
use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PortalController extends Controller
{
    // ─── Ana Sayfa ────────────────────────────────────────────────────────────

    public function index()
    {
        $user      = Auth::user();
        $personnel = $user->personnel;

        $activeLoans  = $personnel
            ? Loan::with(['tool', 'tool.slot.shelf.block'])
                  ->where('personnel_id', $personnel->id)
                  ->whereIn('status', ['active', 'overdue'])
                  ->orderByDesc('loaned_at')
                  ->get()
            : collect();

        $overdueCount = $activeLoans->where('status', 'overdue')->count();

        return view('portal.index', compact('user', 'personnel', 'activeLoans', 'overdueCount'));
    }

    // ─── QR Tara / Zimmet Al ──────────────────────────────────────────────────

    public function scan()
    {
        return view('portal.scan');
    }

    /** GET: Göz (slot) detay sayfası — o gözdeki tüm aletler */
    public function slotDetail(Slot $slot)
    {
        $slot->load(['shelf.block', 'tools' => function ($q) {
            $q->with('activeLoan.personnel')->orderBy('name');
        }]);

        $tools       = $slot->tools;
        $available   = $tools->where('status', 'available')->count();
        $loaned      = $tools->where('status', 'loaned')->count();
        $maintenance = $tools->where('status', 'maintenance')->count();

        return view('portal.slot', compact('slot', 'tools', 'available', 'loaned', 'maintenance'));
    }

    /** AJAX: SLOT QR'dan slot bilgisi döner */
    public function findSlot(Request $request)
    {
        $raw  = trim($request->input('qr', ''));
        // Format: "SLOT:{id}|..." veya sadece numara
        $id = null;
        if (preg_match('/^SLOT:(\d+)/i', $raw, $m)) {
            $id = (int) $m[1];
        } elseif (is_numeric($raw)) {
            $id = (int) $raw;
        }

        if (!$id) {
            return response()->json(['error' => 'Geçersiz göz QR kodu.'], 422);
        }

        $slot = Slot::with(['shelf.block'])->find($id);
        if (!$slot) {
            return response()->json(['error' => 'Göz bulunamadı.'], 404);
        }

        return response()->json([
            'id'        => $slot->id,
            'label'     => $slot->full_label,
            'url'       => route('portal.slot', $slot->id),
        ]);
    }

    public function findTool(Request $request)
    {
        $serial = trim($request->input('serial_no', ''));
        $tool   = Tool::where('serial_no', $serial)->with('slot.shelf.block')->first();

        if (! $tool) {
            return response()->json(['error' => 'Alet bulunamadı: ' . $serial], 404);
        }

        if ($tool->status === 'loaned') {
            $activeLoan = Loan::where('tool_id', $tool->id)->whereIn('status', ['active','overdue'])->with('personnel')->first();
            return response()->json([
                'error'   => 'Bu alet şu an ' . ($activeLoan?->personnel?->name ?? 'birisinde') . ' zimmetinde.',
                'loaned'  => true,
            ], 409);
        }

        if ($tool->status !== 'available') {
            return response()->json(['error' => 'Bu alet şu an kullanılabilir değil (' . $tool->status_label . ').'], 409);
        }

        return response()->json([
            'id'        => $tool->id,
            'name'      => $tool->name,
            'serial_no' => $tool->serial_no,
            'category'  => $tool->category_label,
            'location'  => $tool->location_label,
            'status'    => $tool->status_label,
        ]);
    }

    /** POST: zimmet al */
    public function takeLoan(Request $request)
    {
        $request->validate([
            'tool_id'     => 'required|exists:tools,id',
            'loan_days'   => 'required|integer|min:1|max:90',
        ]);

        $user      = Auth::user();
        $personnel = $user->personnel;

        if (! $personnel) {
            return back()->with('error', 'Hesabınıza bağlı personel kaydı bulunamadı. Yöneticinizle iletişime geçin.');
        }

        $tool = Tool::find($request->tool_id);

        if ($tool->status !== 'available') {
            return back()->with('error', 'Bu alet artık müsait değil.');
        }

        Loan::create([
            'tool_id'           => $tool->id,
            'personnel_id'      => $personnel->id,
            'loaned_at'         => now(),
            'planned_return_at' => now()->addDays((int) $request->loan_days),
            'status'            => 'active',
            'notes'             => 'Self-service portal üzerinden alındı.',
            'loaned_by_user_id' => $user->id,
        ]);

        $tool->update(['status' => 'loaned']);

        return redirect()->route('portal.index')
            ->with('success', "✅ '{$tool->name}' başarıyla zimmetinize alındı!");
    }

    // ─── İade Et ─────────────────────────────────────────────────────────────

    public function returnPage()
    {
        return view('portal.return');
    }

    /** AJAX: istasyon QR doğrulama (Haftalık Dinamik QR desteği) */
    public function verifyStation(Request $request)
    {
        $token = trim($request->input('token', ''));

        if (empty($token)) {
            return response()->json(['error' => 'Lütfen istasyon QR kodunu okutun.'], 400);
        }

        $station = null;

        // 1. Dinamik format: STN:{id}:W{week}:{hash}
        if (preg_match('/^STN:(\d+):/i', $token, $m)) {
            $stationId = (int) $m[1];
            $candidate = Station::find($stationId);
            if ($candidate && $candidate->verifyWeeklyToken($token)) {
                $station = $candidate;
            }
        }

        // 2. Arama ve doğrulama
        if (! $station) {
            $candidates = Station::where('is_active', true)->get();
            foreach ($candidates as $cand) {
                if ($cand->verifyWeeklyToken($token)) {
                    $station = $cand;
                    break;
                }
            }
        }

        if (! $station) {
            return response()->json([
                'error' => 'Geçersiz veya süresi dolmuş takımhane QR kodu! Lütfen takımhane girişindeki güncel haftalık QR kodunu okutun.'
            ], 404);
        }

        return response()->json([
            'station_id'   => $station->id,
            'station_name' => $station->name,
        ]);
    }

    /** AJAX: personelin aktif zimmetini bul */
    public function findMyLoan(Request $request)
    {
        $serial    = trim($request->input('serial_no', ''));
        $user      = Auth::user();
        $personnel = $user->personnel;

        if (! $personnel) {
            return response()->json(['error' => 'Personel kaydı bulunamadı.'], 404);
        }

        $tool = Tool::where('serial_no', $serial)->first();
        if (! $tool) {
            return response()->json(['error' => 'Alet bulunamadı: ' . $serial], 404);
        }

        $loan = Loan::where('tool_id', $tool->id)
                    ->where('personnel_id', $personnel->id)
                    ->whereIn('status', ['active', 'overdue'])
                    ->with('tool')
                    ->first();

        if (! $loan) {
            return response()->json(['error' => 'Bu alet sizin zimmetinizde değil.'], 404);
        }

        return response()->json([
            'loan_id'   => $loan->id,
            'tool_name' => $tool->name,
            'serial_no' => $tool->serial_no,
            'loaned_at' => $loan->loaned_at->format('d.m.Y'),
            'overdue'   => $loan->isOverdue(),
        ]);
    }

    /** POST: iade onayla (fotoğraflı) */
    public function confirmReturn(Request $request)
    {
        $request->validate([
            'loan_id'    => 'required|exists:loans,id',
            'station_id' => 'required|exists:stations,id',
            'photo'      => 'required|image|max:8192',
            'notes'      => 'nullable|string|max:500',
        ]);

        $user      = Auth::user();
        $personnel = $user->personnel;
        $loan      = Loan::with('tool')->findOrFail($request->loan_id);

        // Güvenlik: sadece kendi zimmetini iade edebilir
        if ($personnel && $loan->personnel_id !== $personnel->id) {
            return back()->with('error', 'Bu zimmet size ait değil.');
        }

        // Fotoğrafı kaydet
        $photoPath = $request->file('photo')->store('return-photos/' . now()->format('Y/m'), 'public');

        // Loan güncelle
        $loan->update([
            'returned_at'             => now(),
            'status'                  => 'returned',
            'return_photo'            => $photoPath,
            'return_condition_notes'  => $request->notes,
            'return_station_id'       => $request->station_id,
        ]);

        // Aleti müsait yap
        $loan->tool->update(['status' => 'available']);

        return redirect()->route('portal.index')
            ->with('success', "✅ '{$loan->tool->name}' başarıyla iade edildi. Fotoğraf kaydedildi.");
    }

    // ─── Kendi Zimmetlerim ────────────────────────────────────────────────────

    public function myLoans()
    {
        $user      = Auth::user();
        $personnel = $user->personnel;

        $loans = $personnel
            ? Loan::with(['tool'])
                  ->where('personnel_id', $personnel->id)
                  ->orderByDesc('loaned_at')
                  ->paginate(20)
            : Loan::whereRaw('1=0')->paginate(20);

        return view('portal.my-loans', compact('user', 'personnel', 'loans'));
    }

    // ─── Profil / Şifre Değiştir ──────────────────────────────────────────────

    public function profile()
    {
        return view('portal.profile', ['user' => Auth::user()]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|min:8|confirmed',
        ]);

        Auth::user()->update(['password' => bcrypt($request->password)]);

        return back()->with('success', '✅ Şifreniz başarıyla güncellendi.');
    }
}
