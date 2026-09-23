<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('serial_no', 100)->unique()->nullable();  // Seri numarası
            $table->string('barcode', 100)->unique()->nullable();    // Barkod / QR değeri
            $table->enum('status', ['available', 'loaned', 'maintenance', 'scrapped'])
                  ->default('available');
            // Şu anki fiziksel konum — NULL ise konumsuz veya ödünçte
            $table->foreignId('slot_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('max_loan_days')->default(1); // Varsayılan maks. ödünç gün sayısı
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
