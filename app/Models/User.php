<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'userPrincipalName', 'displayName', 'surname', 'mail', 'givenName',
        'id', // Added new ID field
        'userType', 'jobTitle', 'department', 'accountEnabled', 'usageLocation',
        'streetAddress', 'state', 'country', 'officeLocation', 'city', 'postalCode',
        'telephone', 'mobilePhone', 'alternateEmailAddress', 'ageGroup',
        'consentProvidedForMinor', 'legalAgeGroupClassification', 'companyName',
        'creationType', 'directorySynced', 'invitationState', 'identityIssuer',
        'createdDateTime',
    ];

    public function signIns()
    {
        return $this->hasMany(InteractiveSignIn::class);
    }
}