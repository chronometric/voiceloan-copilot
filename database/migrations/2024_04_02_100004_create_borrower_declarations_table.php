<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrower_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->unique()->constrained('borrowers')->cascadeOnDelete();
            $table->boolean('outstanding_judgments')->nullable();
            $table->boolean('bankruptcy_past_seven_years')->nullable();
            $table->boolean('foreclosure_past_seven_years')->nullable();
            $table->boolean('party_to_lawsuit')->nullable();
            $table->boolean('obligated_on_loan_resulting_foreclosure')->nullable();
            $table->boolean('delinquent_on_federal_debt')->nullable();
            $table->json('additional_answers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrower_declarations');
    }
};
