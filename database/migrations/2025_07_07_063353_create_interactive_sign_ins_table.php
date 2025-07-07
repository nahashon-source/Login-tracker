<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    // Use 'id' as the primary key (string-based UUID)
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    // Mass assignable fields
    protected $fillable = [
        'id',
        'userPrincipalName',
        'displayName',
        'surname1',
        'surname2',
        'mail1',
        'mail2',
        'givenName1',
        'givenName2',
        'userType',
        'jobTitle',
        'department',
        'accountEnabled',
        'usageLocation',
        'streetAddress',
        'state',
        'country',
        'officeLocation',
        'city',
        'postalCode',
        'telephone',
        'mobilePhone',
        'alternateEmailAddress',
        'ageGroup',
        'consentProvidedForMinor',
        'legalAgeGroupClassification',
        'companyName',
        'creationType',
        'directorySynced',
        'invitationState',
        'identityIssuer',
        'createdDateTime',
        'email',
        'password',
    ];

    // Relationship: A user has many sign-ins
    public function signIns()
    {
        return $this->hasMany(InteractiveSignIn::class, 'user_id', 'id');
    }
}
