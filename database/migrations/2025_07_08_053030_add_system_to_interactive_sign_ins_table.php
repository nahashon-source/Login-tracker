<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('interactive_sign_ins', function (Blueprint $table) {
            $table->string('system')->nullable()->after('date_utc');
        });
    }
    
    public function down()
    {
        Schema::table('interactive_sign_ins', function (Blueprint $table) {
            $table->dropColumn('system');
        });
    }
    
};
