<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Takımhaneler (Toolrooms) Tablosu
        Schema::create('toolrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Varsayılan Takımhaneleri Ekle (Mevcut sistem 1. Takımhane olacak)
        DB::table('toolrooms')->insert([
            [
                'id'          => 1,
                'name'        => '1. Takımhane (Otomobil & Hafif Ticari Araçlar)',
                'code'        => 'TKM-OTO-HTA',
                'description' => 'Binek otomobiller ve hafif ticari araçlar bakım-onarım takımları',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => 2,
                'name'        => '2. Takımhane (Ağır Vasıta)',
                'code'        => 'TKM-AGIR-VASITA',
                'description' => 'Kamyon, çekici, otobüs ve ağır vasıta bakım-onarım takımları',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // 3. Bloklar tablosuna toolroom_id ekle ve mevcut verileri 1. Takımhaneye bağla
        Schema::table('blocks', function (Blueprint $table) {
            $table->foreignId('toolroom_id')->default(1)->after('id')
                  ->constrained('toolrooms')->cascadeOnDelete();
        });

        // 4. Parçalar (Tools) tablosuna toolroom_id ekle
        Schema::table('tools', function (Blueprint $table) {
            $table->foreignId('toolroom_id')->default(1)->after('id')
                  ->constrained('toolrooms')->cascadeOnDelete();
        });

        // 5. İstasyonlar tablosuna toolroom_id ekle
        if (Schema::hasTable('stations')) {
            Schema::table('stations', function (Blueprint $table) {
                $table->foreignId('toolroom_id')->default(1)->after('id')
                      ->constrained('toolrooms')->cascadeOnDelete();
            });
        }

        // 6. Zimmetler (Loans) tablosuna toolroom_id ekle
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('toolroom_id')->default(1)->after('id')
                  ->constrained('toolrooms')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['toolroom_id']);
            $table->dropColumn('toolroom_id');
        });

        if (Schema::hasTable('stations')) {
            Schema::table('stations', function (Blueprint $table) {
                $table->dropForeign(['toolroom_id']);
                $table->dropColumn('toolroom_id');
            });
        }

        Schema::table('tools', function (Blueprint $table) {
            $table->dropForeign(['toolroom_id']);
            $table->dropColumn('toolroom_id');
        });

        Schema::table('blocks', function (Blueprint $table) {
            $table->dropForeign(['toolroom_id']);
            $table->dropColumn('toolroom_id');
        });

        Schema::dropIfExists('toolrooms');
    }
};
