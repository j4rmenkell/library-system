<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Uploaded covers are kept in the database, because Azure wipes the
    // container's disk every time the app sleeps or gets redeployed
    public function up(): void
    {
        Schema::create('book_covers', function (Blueprint $table) {
            $table->id();
            // one cover per book, and it goes away with the book
            $table->foreignId('book_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('mime_type');
            // the picture itself, as base64 text
            $table->longText('data');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_covers');
    }
};
