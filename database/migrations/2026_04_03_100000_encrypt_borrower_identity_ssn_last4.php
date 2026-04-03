<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('borrower_identity')->select('id', 'ssn_last4')->get();

        Schema::table('borrower_identity', function (Blueprint $table) {
            $table->dropColumn('ssn_last4');
        });

        Schema::table('borrower_identity', function (Blueprint $table) {
            $table->text('ssn_last4')->nullable();
        });

        foreach ($rows as $row) {
            if ($row->ssn_last4 === null || $row->ssn_last4 === '') {
                continue;
            }
            DB::table('borrower_identity')->where('id', $row->id)->update([
                'ssn_last4' => encrypt($row->ssn_last4),
            ]);
        }
    }

    public function down(): void
    {
        $rows = DB::table('borrower_identity')->select('id', 'ssn_last4')->get();

        Schema::table('borrower_identity', function (Blueprint $table) {
            $table->dropColumn('ssn_last4');
        });

        Schema::table('borrower_identity', function (Blueprint $table) {
            $table->string('ssn_last4', 4)->nullable();
        });

        foreach ($rows as $row) {
            if ($row->ssn_last4 === null || $row->ssn_last4 === '') {
                continue;
            }
            try {
                $plain = decrypt($row->ssn_last4);
            } catch (\Throwable) {
                $plain = null;
            }
            if ($plain === null || $plain === '') {
                continue;
            }
            DB::table('borrower_identity')->where('id', $row->id)->update([
                'ssn_last4' => substr($plain, -4),
            ]);
        }
    }
};
