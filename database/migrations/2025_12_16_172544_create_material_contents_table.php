<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('material_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_modul_id')->constrained('sub_moduls')->onDelete('cascade');
            $table->string('title');
            $table->text('youtube_link')->nullable();
            $table->text('article_title')->nullable();
            $table->text('article_content')->nullable();
            $table->text('article_images')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_contents');
    }
};
