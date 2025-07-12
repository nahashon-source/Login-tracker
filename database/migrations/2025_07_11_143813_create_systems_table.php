<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the systems table
        Schema::create('systems', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();  // Ensure system names are unique
            $table->timestamps();
        });

        // Insert predefined system names into the systems table
        $systems = [
            'SCM',
            'Odoo',
            'D365 Live',
            'Fit Express',
            'FIT ERP',
            'Fit Express UAT',
            'FITerp UAT',
            'OPS',
            'OPS UAT',
        ];

        foreach ($systems as $system) {
            DB::table('systems')->insert([
                'name' => $system,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the systems table if the migration is rolled back
        Schema::dropIfExists('systems');
    }
}
