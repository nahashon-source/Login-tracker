<?php 


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

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

    protected $casts = [
        'accountEnabled' => 'boolean',
        'directorySynced' => 'boolean',
        'createdDateTime' => 'datetime',
    ];

    protected $hidden = [
        'password',
    ];

    public function signIns()
    {
        return $this->hasMany(InteractiveSignIn::class, 'user_id', 'id');
    }
}
