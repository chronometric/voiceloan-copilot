<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voice_call_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('call_sid')->unique();
            $table->uuid('borrower_uuid');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('borrower_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voice_call_sessions');
    }
};
