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
        Schema::table('signin_logs', function (Blueprint $table) {
            //
            $table->string('system')->default('SCM')->after('user_id'); // or any other column

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signin_logs', function (Blueprint $table) {
            //
            $table->dropColumn('system');

        });
    }
};
