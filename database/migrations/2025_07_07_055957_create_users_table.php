<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary(); // Primary key as string
            $table->string('userPrincipalName')->unique();
            $table->string('displayName')->nullable();
            $table->string('surname1')->nullable();
            $table->string('surname2')->nullable();
            $table->string('mail1')->nullable();
            $table->string('mail2')->nullable();
            $table->string('givenName1')->nullable();
            $table->string('givenName2')->nullable();
            $table->string('userType')->nullable();
            $table->string('jobTitle')->nullable();
            $table->string('department')->nullable();
            $table->boolean('accountEnabled')->default(true);
            $table->string('usageLocation')->nullable();
            $table->string('streetAddress')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('officeLocation')->nullable();
            $table->string('city')->nullable();
            $table->string('postalCode')->nullable();
            $table->string('telephone')->nullable();
            $table->string('mobilePhone')->nullable();
            $table->string('alternateEmailAddress')->nullable();
            $table->string('ageGroup')->nullable();
            $table->string('consentProvidedForMinor')->nullable();
            $table->string('legalAgeGroupClassification')->nullable();
            $table->string('companyName')->nullable();
            $table->string('creationType')->nullable();
            $table->boolean('directorySynced')->default(false);
            $table->string('invitationState')->nullable();
            $table->string('identityIssuer')->nullable();
            $table->timestamp('createdDateTime')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};