<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations of the authors table edition.
     */
    public function up(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('biography');
        });
    }

    /**
     * Reverse the migrations of the authors table edition.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
