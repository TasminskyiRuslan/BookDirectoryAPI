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
            $table->date('birth_date')->index()->nullable()->after('slug');
            $table->date('death_date')->index()->nullable()->after('birth_date');
            $table->text('biography')->nullable()->after('death_date');

            $table->dropFullText('authors_search_index');
        });
    }

    /**
     * Reverse the migrations of the authors table edition.
     */
    public function down(): void
    {
        Schema::table('authors', function (Blueprint $table) {
            $table->fullText(['last_name', 'first_name', 'patronymic'], 'authors_search_index');

            $table->dropIndex(['birth_date']);
            $table->dropIndex(['death_date']);

            $table->dropColumn(['birth_date', 'death_date', 'biography']);
        });
    }
};
