<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key

            $table->string('userPrincipalName', 255);
            $table->string('displayName', 255)->nullable();
            $table->string('surname', 255)->nullable();
            $table->string('mail', 255)->nullable();
            $table->string('givenName', 255)->nullable();
            $table->string('userType', 255)->nullable();
            $table->string('jobTitle', 255)->nullable();
            $table->string('department', 255)->nullable();
            $table->boolean('accountEnabled')->default(1);
            $table->string('usageLocation', 255)->nullable();
            $table->string('streetAddress', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('country', 255)->nullable();
            $table->string('officeLocation', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('postalCode', 255)->nullable();
            $table->string('telephoneNumber', 255)->nullable();
            $table->string('mobilePhone', 255)->nullable();
            $table->string('alternateEmailAddress', 255)->nullable();
            $table->string('ageGroup', 255)->nullable();
            $table->string('consentProvidedForMinor', 255)->nullable();
            $table->string('legalAgeGroupClassification', 255)->nullable();
            $table->string('companyName', 255)->nullable();
            $table->string('creationType', 255)->nullable();
            $table->boolean('directorySynced')->default(0);
            $table->string('invitationState', 255)->nullable();
            $table->string('identityIssuer', 255)->nullable();
            $table->dateTime('createdDateTime')->nullable();

            $table->timestamps();
            $table->softDeletes(); // adds deleted_at column
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
