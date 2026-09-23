<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Parça Grupları (Segmentler) Tablosu
        Schema::create('tool_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);             // "Hafif Ticari Araçlar (HTA)"
            $table->string('code', 50)->unique();    // "HTA", "BINEK"
            $table->string('color', 30)->default('gray'); // Filament badge rengi (warning, info, danger, success)
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Varsayılan Grupları Ekle
        DB::table('tool_groups')->insert([
            [
                'id'          => 1,
                'name'        => 'Binek Otomobil',
                'code'        => 'BINEK',
                'color'       => 'info',
                'description' => 'Sedan, hatchback, suv binek araçlar için özel takımlar',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => 2,
                'name'        => 'Hafif Ticari Araçlar (HTA)',
                'code'        => 'HTA',
                'color'       => 'warning',
                'description' => 'Van, panelvan, kamyonet ve hafif ticari araç takımları (Rot çektirme vb.)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => 3,
                'name'        => 'Ağır Vasıta',
                'code'        => 'AGIR_VASITA',
                'color'       => 'danger',
                'description' => 'Kamyon, tır çekici ve otobüs takımları',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'id'          => 4,
                'name'        => 'Genel / Üniversal',
                'code'        => 'GENEL',
                'color'       => 'success',
                'description' => 'Tüm araç gruplarında ortak kullanılan genel el aletleri ve ekipmanlar',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);

        // 3. Tools tablosuna tool_group_id foreign key ekle
        Schema::table('tools', function (Blueprint $table) {
            $table->foreignId('tool_group_id')->nullable()->after('toolroom_id')
                  ->constrained('tool_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropForeign(['tool_group_id']);
            $table->dropColumn('tool_group_id');
        });

        Schema::dropIfExists('tool_groups');
    }
};
