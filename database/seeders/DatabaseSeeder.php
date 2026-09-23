<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Loan;
use App\Models\Personnel;
use App\Models\Shelf;
use App\Models\Slot;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---------------------------------------------------------------
        // 1. Kullanıcılar
        // ---------------------------------------------------------------
        User::firstOrCreate(['email' => 'sorumlu@takimhane.local'], [
            'name'     => 'Ahmet Yılmaz',
            'password' => bcrypt('sorumlu123'),
            'role'     => 'takimhane_sor',
        ]);

        // ---------------------------------------------------------------
        // 2. Konum Hiyerarşisi: Blok → Raf → Göz
        // ---------------------------------------------------------------
        $blokA = Block::create(['name' => 'Blok A', 'description' => 'Ana takımhane bloku']);
        $blokB = Block::create(['name' => 'Blok B', 'description' => 'Yedek parça deposu']);

        $rafA1 = Shelf::create(['block_id' => $blokA->id, 'name' => 'Raf 1', 'description' => 'El aletleri']);
        $rafA2 = Shelf::create(['block_id' => $blokA->id, 'name' => 'Raf 2', 'description' => 'Ölçüm cihazları']);
        $rafB1 = Shelf::create(['block_id' => $blokB->id, 'name' => 'Raf 1', 'description' => 'Yedek aletler']);

        $gozA1_1 = Slot::create(['shelf_id' => $rafA1->id, 'name' => 'Göz 1', 'capacity' => 5]);
        $gozA1_2 = Slot::create(['shelf_id' => $rafA1->id, 'name' => 'Göz 2', 'capacity' => 5]);
        $gozA2_1 = Slot::create(['shelf_id' => $rafA2->id, 'name' => 'Göz 1', 'capacity' => 3]);
        $gozB1_1 = Slot::create(['shelf_id' => $rafB1->id, 'name' => 'Göz 1', 'capacity' => 10]);

        // ---------------------------------------------------------------
        // 3. Parçalar / Takımlar
        // ---------------------------------------------------------------
        $tornavida = Tool::create([
            'name'          => 'Phillips Tornavida Set',
            'serial_no'     => 'SN-001',
            'barcode'       => 'TRN-001',
            'status'        => 'available',
            'slot_id'       => $gozA1_1->id,
            'max_loan_days' => 1,
            'description'   => '6 parça tornavida seti',
        ]);

        $multimetre = Tool::create([
            'name'          => 'Dijital Multimetre',
            'serial_no'     => 'SN-002',
            'barcode'       => 'MLT-001',
            'status'        => 'available',
            'slot_id'       => $gozA2_1->id,
            'max_loan_days' => 3,
            'description'   => 'Fluke 117 dijital multimetre',
        ]);

        $kumpas = Tool::create([
            'name'          => 'Kumpas 150mm',
            'serial_no'     => 'SN-003',
            'barcode'       => 'KMP-001',
            'status'        => 'available',
            'slot_id'       => $gozA2_1->id,
            'max_loan_days' => 2,
        ]);

        $matkapmakine = Tool::create([
            'name'          => 'Matkap Makinesi',
            'serial_no'     => 'SN-004',
            'barcode'       => 'MTK-001',
            'status'        => 'loaned',   // Bu parça ödünçte
            'slot_id'       => null,
            'max_loan_days' => 1,
            'description'   => 'Bosch GSB 185-LI Akülü Matkap',
        ]);

        $anahtarset = Tool::create([
            'name'          => 'Kombine Anahtar Seti',
            'serial_no'     => 'SN-005',
            'barcode'       => 'ANH-001',
            'status'        => 'loaned',   // Bu da ödünçte — gecikmiş!
            'slot_id'       => null,
            'max_loan_days' => 1,
        ]);

        // ---------------------------------------------------------------
        // 4. Personel
        // ---------------------------------------------------------------
        $personel1 = Personnel::create([
            'name'         => 'Mehmet Demir',
            'badge_number' => 'P-1001',
            'department'   => 'Üretim',
            'email'        => 'mehmet.demir@fabrika.com',
            'phone'        => '555-001',
        ]);

        $personel2 = Personnel::create([
            'name'         => 'Fatma Çelik',
            'badge_number' => 'P-1002',
            'department'   => 'Kalite Kontrol',
            'email'        => 'fatma.celik@fabrika.com',
            'phone'        => '555-002',
        ]);

        $personel3 = Personnel::create([
            'name'         => 'Ali Kaya',
            'badge_number' => 'P-1003',
            'department'   => 'Bakım-Onarım',
            'email'        => 'ali.kaya@fabrika.com',
            'phone'        => '555-003',
        ]);

        // ---------------------------------------------------------------
        // 5. Zimmetler
        // ---------------------------------------------------------------

        // Aktif zimmet — bugün ödünç alındı, yarın iade edilmeli
        Loan::withoutEvents(function () use ($matkapmakine, $personel1) {
            Loan::create([
                'tool_id'          => $matkapmakine->id,
                'personnel_id'     => $personel1->id,
                'loaned_at'        => now(),
                'planned_return_at'=> now()->addDay(),
                'returned_at'      => null,
                'status'           => 'active',
                'notes'            => 'Üretim hattı için acil talep',
            ]);
        });

        // GECİKMİŞ zimmet — 3 gün önce ödünç alındı, dün iade edilmeliydi
        Loan::withoutEvents(function () use ($anahtarset, $personel2) {
            Loan::create([
                'tool_id'          => $anahtarset->id,
                'personnel_id'     => $personel2->id,
                'loaned_at'        => now()->subDays(3),
                'planned_return_at'=> now()->subDay(),
                'returned_at'      => null,
                'status'           => 'overdue',
                'notes'            => 'Kalibrasyon çalışması',
            ]);
        });

        // Geçmiş (iade edilmiş) zimmet
        Loan::withoutEvents(function () use ($multimetre, $personel3, $gozA2_1) {
            Loan::create([
                'tool_id'            => $multimetre->id,
                'personnel_id'       => $personel3->id,
                'loaned_at'          => now()->subWeek(),
                'planned_return_at'  => now()->subDays(5),
                'returned_at'        => now()->subDays(5),
                'returned_slot_id'   => $gozA2_1->id,
                'status'             => 'returned',
            ]);
        });
    }
}
