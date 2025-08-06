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
        Schema::create('youtube_channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->unique();
            $table->string('category');
            $table->integer('subscribers_count');
            $table->bigInteger('average_views');
            $table->float('engagement_rate', 2);
            $table->string('language');
            $table->string('region');
            $table->date('last_video_published_at');
            $table->index('last_video_published_at');
            $table->index('category');
            $table->index('region');
            $table->index('language');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('youtube_channels');
    }
};
