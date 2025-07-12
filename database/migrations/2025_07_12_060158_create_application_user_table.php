<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApplicationUserTable extends Migration
{
    public function up()
    {
        Schema::create('application_user', function (Blueprint $table) {
            $table->id(); // Auto-incrementing BIGINT UNSIGNED for the pivot table's own ID
            $table->char('user_id', 36)->index(); // Match users.id as char(36)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('application_id'); // Assume systems.id is BIGINT UNSIGNED
            $table->foreign('application_id')->references('id')->on('systems')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('application_user');
    }
}