<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_contents', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('sub_modul_id')->nullable()->change();
            $table->string('title')->nullable()->change();
        });

        DB::table('material_contents')
            ->whereNull('published_at')
            ->update(['published_at' => DB::raw('created_at')]);

        Schema::create('material_content_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_content_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->foreignId('sub_modul_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('youtube_link')->nullable();
            $table->text('article_title')->nullable();
            $table->longText('article_content')->nullable();
            $table->text('article_images')->nullable();
            $table->string('status', 20)->index();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['material_content_id', 'version']);
            $table->index(['status', 'submitted_at']);
        });
    }

    public function down(): void
    {
        if (DB::table('material_contents')->whereNull('published_at')->exists()) {
            throw new RuntimeException(
                'Cannot roll back the publication workflow while unpublished material contents exist.'
            );
        }

        Schema::dropIfExists('material_content_revisions');

        Schema::table('material_contents', function (Blueprint $table) {
            $table->dropIndex(['published_at']);
            $table->dropColumn('published_at');
            $table->foreignId('sub_modul_id')->nullable(false)->change();
            $table->string('title')->nullable(false)->change();
        });
    }
};
