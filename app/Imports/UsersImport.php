<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

/**
 * Handles the import of user records from an Excel/CSV file into the users table.
 *
 * Implements the Maatwebsite Excel package interfaces:
 *  - ToModel: transforms each row into a User model instance.
 *  - WithHeadingRow: treats the first row of the sheet as the column headings.
 *
 * Expects the following minimum fields in the import:
 *  - id (unique, required)
 *  - userPrincipalName (email format, required)
 *
 * Optionally processes additional fields and handles boolean parsing,
 * date parsing, and logs validation failures to the 'import' log channel.
 */
class UsersImport implements ToModel, WithHeadingRow
{
    /**
     * Transforms a single row of data into a User model.
     *
     * - Validates required fields.
     * - Logs and skips invalid rows.
     * - Parses booleans and dates safely.
     * - Constructs a new User instance with mapped attributes.
     *
     * @param array $row The row of data as an associative array (lowercased keys).
     * @return \App\Models\User|null Returns a new User instance or null on failure.
     */
    public function model(array $row)
    {
        // Normalize all keys to lowercase for consistent access
        $row = array_change_key_case($row, CASE_LOWER);

        // Define validation rules
        $validator = Validator::make($row, [
            'id' => 'required|unique:users,id',
            'userprincipalname' => 'required|email',
        ]);

        // If validation fails, log warning and skip row
        if ($validator->fails()) {
            Log::channel('import')->warning('Validation failed', $validator->errors()->toArray());
            return null;
        }

        // Handle booleans: safely cast 'true'/'false' strings to boolean values
        $accountEnabled = filter_var($row['accountenabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN);
        $directorySynced = filter_var($row['directorysynced'] ?? 'false', FILTER_VALIDATE_BOOLEAN);

        // Parse created date safely using Carbon (if available)
        $createdDateTime = null;
        if (!empty($row['createddatetime'])) {
            try {
                $createdDateTime = Carbon::parse($row['createddatetime'])->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                Log::channel('import')->warning("Invalid date format for user ID {$row['id']}");
            }
        }

        // Return new User instance with mapped attributes
        return new User([
            'id' => $row['id'],
            'userPrincipalName' => $row['userprincipalname'],
            'displayName' => $row['displayname'] ?? null,
            'surname1' => $row['surname'] ?? null,
            'surname2' => $row['surname_2'] ?? null,
            'mail1' => $row['mail'] ?? null,
            'mail2' => $row['mail_2'] ?? null,
            'givenName1' => $row['givenname'] ?? null,
            'givenName2' => $row['givenname_2'] ?? null,
            'userType' => $row['usertype'] ?? null,
            'jobTitle' => $row['jobtitle'] ?? null,
            'department' => $row['department'] ?? null,
            'accountEnabled' => $accountEnabled,
            'usageLocation' => $row['usagelocation'] ?? null,
            'streetAddress' => $row['streetaddress'] ?? null,
            'state' => $row['state'] ?? null,
            'country' => $row['country'] ?? null,
            'officeLocation' => $row['officelocation'] ?? null,
            'city' => $row['city'] ?? null,
            'postalCode' => $row['postalcode'] ?? null,
            'telephone' => $row['telephonenumber'] ?? null,
            'mobilePhone' => $row['mobilephone'] ?? null,
            'alternateEmailAddress' => $row['alternateemailaddress'] ?? null,
            'ageGroup' => $row['agegroup'] ?? null,
            'consentProvidedForMinor' => $row['consentprovidedforminor'] ?? null,
            'legalAgeGroupClassification' => $row['legalagegroupclassification'] ?? null,
            'companyName' => $row['companyname'] ?? null,
            'creationType' => $row['creationtype'] ?? null,
            'directorySynced' => $directorySynced,
            'invitationState' => $row['invitationstate'] ?? null,
            'identityIssuer' => $row['identityissuer'] ?? null,
            'createdDateTime' => $createdDateTime,
        ]);
    }
}
