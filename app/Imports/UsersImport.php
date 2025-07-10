<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $row = array_change_key_case($row, CASE_LOWER);

        // Skip if no userPrincipalName
        if (empty($row['userprincipalname'])) {
            return null;
        }

        // Check for existing user
        if (User::where('userPrincipalName', $row['userprincipalname'])->exists()) {
            return null;
        }

        // Map and transform
        return new User([
            'id'                        => $row['id'] ?? null,
            'userPrincipalName'         => $row['userprincipalname'],
            'displayName'               => $row['displayname'] ?? null,
            'surname1'                  => $row['surname'] ?? null,   // CSV 'surname' to DB 'surname1'
            'mail1'                     => $row['mail'] ?? null,
            'givenName1'                => $row['givenname'] ?? null,
            'userType'                  => $row['usertype'] ?? null,
            'jobTitle'                  => $row['jobtitle'] ?? null,
            'department'                => $row['department'] ?? null,
            'accountEnabled'            => filter_var($row['accountenabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
            'usageLocation'             => $row['usagelocation'] ?? null,
            'streetAddress'             => $row['streetaddress'] ?? null,
            'state'                     => $row['state'] ?? null,
            'country'                   => $row['country'] ?? null,
            'officeLocation'            => $row['officelocation'] ?? null,
            'city'                      => $row['city'] ?? null,
            'postalCode'                => $row['postalcode'] ?? null,
            'telephone'                 => $row['telephonenumber'] ?? null,
            'mobilePhone'               => $row['mobilephone'] ?? null,
            'alternateEmailAddress'     => $row['alternateemailaddress'] ?? null,
            'ageGroup'                  => $row['agegroup'] ?? null,
            'consentProvidedForMinor'   => $row['consentprovidedforminor'] ?? null,
            'legalAgeGroupClassification'=> $row['legalagegroupclassification'] ?? null,
            'companyName'               => $row['companyname'] ?? null,
            'creationType'              => $row['creationtype'] ?? null,
            'directorySynced'           => filter_var($row['directorysynced'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'invitationState'           => $row['invitationstate'] ?? null,
            'identityIssuer'            => $row['identityissuer'] ?? null,
            'createdDateTime'           => !empty($row['createddatetime']) ? Carbon::parse($row['createddatetime']) : null,
        ]);
    }
}
