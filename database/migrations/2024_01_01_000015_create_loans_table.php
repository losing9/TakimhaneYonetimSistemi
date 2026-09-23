<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->restrictOnDelete();
            $table->foreignId('personnel_id')->constrained('personnel')->restrictOnDelete();
            $table->timestamp('loaned_at');                     // Ödünç alma tarihi
            $table->timestamp('planned_return_at');             // Planlanan iade tarihi
            $table->timestamp('returned_at')->nullable();       // Gerçek iade tarihi (NULL = hâlâ ödünçte)
            // İade sırasında konumun doğrulandığı göz
            $table->foreignId('returned_slot_id')
                  ->nullable()
                  ->constrained('slots')
                  ->nullOnDelete();
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            $table->text('notes')->nullable();
            // İşlemi yapan sistem kullanıcısı (panel kullanıcısı)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
