<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Takımhane İstasyonları tablosu (iade doğrulama için)
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->string('name');                        // "Takımhane A Girişi"
            $table->string('qr_token')->unique();          // "STATION-TAKIM-uuid"
            $table->string('location')->nullable();        // Ek açıklama
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Loans tablosuna iade fotoğrafı ve istasyon kolonları ekle
        Schema::table('loans', function (Blueprint $table) {
            $table->string('return_photo')->nullable()->after('notes');      // fotoğraf yolu
            $table->text('return_condition_notes')->nullable()->after('return_photo'); // iade notu
            $table->foreignId('return_station_id')->nullable()->after('return_condition_notes')
                  ->constrained('stations')->nullOnDelete();                  // hangi istasyondan iade
            $table->foreignId('loaned_by_user_id')->nullable()->after('created_by')
                  ->constrained('users')->nullOnDelete();                     // self-service alan kullanıcı
        });

        // Personnel tablosuna user_id bağlantısı (self-service için)
        Schema::table('personnel', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('personnel', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\User::class, 'user_id');
            $table->dropColumn('user_id');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['return_photo', 'return_condition_notes', 'return_station_id', 'loaned_by_user_id']);
        });

        Schema::dropIfExists('stations');
    }
};
