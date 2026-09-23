<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date')->unique();       // Hangi gün
            $table->unsignedInteger('total_loans')->default(0);   // Toplam yeni zimmet
            $table->unsignedInteger('total_returns')->default(0); // Toplam iade
            $table->unsignedInteger('overdue_count')->default(0); // Gecikmiş sayısı
            $table->unsignedInteger('new_tools')->default(0);     // Sisteme eklenen parça
            $table->string('file_path_xlsx', 500)->nullable();    // Excel dosya yolu
            $table->string('file_path_pdf', 500)->nullable();     // PDF dosya yolu
            $table->timestamp('generated_at')->nullable();        // Ne zaman oluşturuldu
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
