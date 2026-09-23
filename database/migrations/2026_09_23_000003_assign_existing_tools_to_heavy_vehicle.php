<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ağır Vasıta Takımhanesi ve Grubu ID'lerini belirle
        $heavyToolroom = DB::table('toolrooms')->where('code', 'TKM-AGIR-VASITA')->orWhere('id', 2)->first();
        $pkwToolroom   = DB::table('toolrooms')->where('code', 'TKM-OTO-HTA')->orWhere('id', 1)->first();
        $heavyGroup    = DB::table('tool_groups')->where('code', 'AGIR_VASITA')->orWhere('id', 3)->first();

        $heavyRoomId  = $heavyToolroom ? $heavyToolroom->id : 2;
        $pkwRoomId    = $pkwToolroom ? $pkwToolroom->id : 1;
        $heavyGroupId = $heavyGroup ? $heavyGroup->id : 3;

        // 2. Mevcut tüm parçaları Ağır Vasıta takımhanesine ve Ağır Vasıta grubuna ata
        DB::table('tools')->update([
            'toolroom_id'   => $heavyRoomId,
            'tool_group_id' => $heavyGroupId,
        ]);

        // 3. Mevcut blokları (Blok A, B, C, Depo) Ağır Vasıta takımhanesine bağla
        DB::table('blocks')->update([
            'toolroom_id' => $heavyRoomId,
        ]);

        // 4. Mevcut tüm zimmetleri Ağır Vasıta takımhanesine bağla
        DB::table('loans')->update([
            'toolroom_id' => $heavyRoomId,
        ]);

        // 5. İstasyonları takımhanelere ata:
        // ATA Takımhane (ID 1) -> Ağır Vasıta
        // PKW Takımhane (ID 2) -> Otomobil & HTA
        DB::table('stations')->where('id', 1)->orWhere('name', 'like', '%ATA%')->update([
            'toolroom_id' => $heavyRoomId,
        ]);

        DB::table('stations')->where('id', 2)->orWhere('name', 'like', '%PKW%')->update([
            'toolroom_id' => $pkwRoomId,
        ]);
    }

    public function down(): void
    {
        DB::table('tools')->update(['toolroom_id' => 1, 'tool_group_id' => null]);
        DB::table('blocks')->update(['toolroom_id' => 1]);
        DB::table('loans')->update(['toolroom_id' => 1]);
        DB::table('stations')->update(['toolroom_id' => 1]);
    }
};
