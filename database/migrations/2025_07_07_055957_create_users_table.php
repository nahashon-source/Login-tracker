<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('userPrincipalName')->unique();
            $table->string('displayName')->nullable();
            $table->string('surname')->nullable();
            $table->string('mail')->nullable();
            $table->string('givenName')->nullable();
            $table->string('id')->nullable(); // Non-auto-incrementing ID field as requested
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