<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    public $timestamps = false;


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
        'logged_in',
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
        return $this->hasMany(SigninLog::class);//, 'user_id', 'id' // Updated to match interactiveSignIns
    }
    
    public function interactiveSignIns()
    {
        return $this->hasMany(SigninLog::class, 'user_id', 'id');
    }


    public function signinLogs()
{
    return $this->hasMany(SigninLog::class, 'user_id', 'id');

}




/**
 * Get the systems associated with this user.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
 */

public function systems(): BelongsToMany
{
    return $this->belongsToMany(System::class, 'application_user', 'user_id', 'application_id')
                ->withPivot('id');
}

}