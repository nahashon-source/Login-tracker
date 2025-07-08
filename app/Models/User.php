<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * App\Models\User
 *
 * Represents a user entity within the system, based on Azure AD or manual entry.
 * Handles user authentication and relationship to sign-in activity.
 */
class User extends Authenticatable
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     * We're using a string-based GUID/UUID here.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable via create() or fill().
     *
     * @var array
     */
    protected $fillable = [
        // Required core attributes
        'id',
        'userPrincipalName',
        'displayName',

        // Personal details
        'surname1',
        'surname2',
        'givenName1',
        'givenName2',

        // Email addresses
        'mail1',
        'mail2',
        'email',

        // Account details
        'userType',
        'jobTitle',
        'department',
        'accountEnabled',
        'usageLocation',

        // Address and contact info
        'streetAddress',
        'state',
        'country',
        'officeLocation',
        'city',
        'postalCode',
        'telephone',
        'mobilePhone',
        'alternateEmailAddress',

        // Demographics
        'ageGroup',
        'consentProvidedForMinor',
        'legalAgeGroupClassification',

        // Directory and identity metadata
        'companyName',
        'creationType',
        'directorySynced',
        'invitationState',
        'identityIssuer',

        // Timestamps
        'createdDateTime',

        // Authentication
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'accountEnabled' => 'boolean',
        'directorySynced' => 'boolean',
        'createdDateTime' => 'datetime',
    ];

    /**
     * Attributes hidden from serialization (e.g. API responses).
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the sign-in records associated with this user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function signIns()
    {
        // User can have many related sign-ins
        return $this->hasMany(InteractiveSignIn::class, 'user_id', 'id');
    }
}
