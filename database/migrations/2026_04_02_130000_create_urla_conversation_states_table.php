<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urla_conversation_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->unique()->constrained('borrowers')->cascadeOnDelete();
            $table->string('call_sid', 64)->nullable()->index();
            $table->string('current_stage', 32)->default('intake');
            $table->string('current_section', 32)->default('borrower');
            $table->json('clarification_counts')->nullable();
            $table->json('last_tool_results')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urla_conversation_states');
    }
};
