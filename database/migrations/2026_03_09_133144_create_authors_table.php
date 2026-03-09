<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations of the authors table.
     */
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('last_name');
            $table->string('first_name');
            $table->string('patronymic')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();

            $table->index(['last_name', 'first_name', 'patronymic'], 'authors_sort_index');
            $table->fullText(['last_name', 'first_name', 'patronymic'], 'authors_search_index');
        });
    }

    /**
     * Reverse the migrations of the author table.
     */
    public function down(): void
    {
        Schema::dropIfExists('authors');
    }
};
