<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('publication_date');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->smallInteger('publication_year')->nullable()->after('isbn');
            $table->string('cover_path')->nullable()->after('availability_status');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['publication_year', 'cover_path']);
        });

        Schema::table('books', function (Blueprint $table) {
            $table->date('publication_date')->nullable()->after('isbn');
        });
    }
};
