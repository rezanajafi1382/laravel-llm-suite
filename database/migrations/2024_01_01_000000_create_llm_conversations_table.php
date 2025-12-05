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
        Schema::create('llm_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('conversation_id')->unique();
            $table->text('system_prompt')->nullable();
            $table->json('messages');
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('llm_conversations');
    }
};

