<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            // Kategori — el aleti, elektrikli, ölçüm vb.
            $table->string('category', 100)->nullable()->after('name');

            // Fotoğraf — storage/app/public/tools/ altında saklanır
            $table->string('image', 500)->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropColumn(['category', 'image']);
        });
    }
};
