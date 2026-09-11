<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_goal');
            $table->string('target_audience');
            $table->string('product_promotion');
            $table->json('platforms');
            $table->string('tone', 30);
            $table->date('start_date');
            $table->date('end_date');
            $table->text('additional_instructions')->nullable();
            $table->boolean('is_demo')->default(false);
            $table->timestamps();
        });
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->string('platform', 30);
            $table->string('content_type', 100);
            $table->string('post_idea');
            $table->text('hook')->nullable();
            $table->text('caption');
            $table->string('call_to_action');
            $table->json('hashtags');
            $table->text('ai_reason');
            $table->date('planned_date');
            $table->time('suggested_time');
            $table->date('scheduled_date')->nullable();
            $table->time('scheduled_time')->nullable();
            $table->string('status', 20)->default('Draft')->index();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->index(['scheduled_date', 'scheduled_time']);
            $table->index('planned_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('campaigns');
    }
};
