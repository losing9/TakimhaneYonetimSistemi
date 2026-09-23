<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('toolroom_id')->nullable()->after('role')
                  ->constrained('toolrooms')->nullOnDelete();
        });

        // 1. Mevcut takimhane_sor kullanıcısını (Ahmet Yılmaz) 2. Takımhane (Ağır Vasıta)'ye ata
        DB::table('users')->where('role', 'takimhane_sor')->update([
            'toolroom_id' => 2,
        ]);

        // 2. 1. Takımhane (Otomobil & HTA) için örnek sorumlu oluştur (eğer yoksa)
        $otoSorumluExists = DB::table('users')->where('email', 'sorumlu.oto@takimhane.local')->exists();
        if (!$otoSorumluExists) {
            DB::table('users')->insert([
                'name'              => 'Mehmet Demir (Otomobil & HTA)',
                'email'             => 'sorumlu.oto@takimhane.local',
                'password'          => Hash::make('takimhane123'),
                'role'              => 'takimhane_sor',
                'toolroom_id'       => 1,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['toolroom_id']);
            $table->dropColumn('toolroom_id');
        });

        DB::table('users')->where('email', 'sorumlu.oto@takimhane.local')->delete();
    }
};
