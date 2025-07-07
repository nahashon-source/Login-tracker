<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Defensive check for mandatory ID
        if (empty($row['id'])) {
            throw new \Exception('User ID is required and cannot be empty.');
        }

        // Defensive boolean conversions
        $accountEnabled = isset($row['accountEnabled']) ? filter_var($row['accountEnabled'], FILTER_VALIDATE_BOOLEAN) : true;
        $directorySynced = isset($row['directorySynced']) ? filter_var($row['directorySynced'], FILTER_VALIDATE_BOOLEAN) : false;

        return new User([
            'id'                             => $row['id'],
            'userPrincipalName'              => $row['userPrincipalName'],
            'displayName'                    => $row['displayName'],
            'surname1'                       => $row['surname'] ?? null,
            'surname2'                       => $row['surname_2'] ?? null,
            'mail1'                          => $row['mail'] ?? null,
            'mail2'                          => $row['mail_2'] ?? null,
            'givenName1'                     => $row['givenName'] ?? null,
            'givenName2'                     => $row['givenName_2'] ?? null,
            'userType'                       => $row['userType'],
            'jobTitle'                       => $row['jobTitle'],
            'department'                     => $row['department'],
            'accountEnabled'                 => $accountEnabled,
            'usageLocation'                  => $row['usageLocation'],
            'streetAddress'                  => $row['streetAddress'],
            'state'                          => $row['state'],
            'country'                        => $row['country'],
            'officeLocation'                 => $row['officeLocation'],
            'city'                           => $row['city'],
            'postalCode'                     => $row['postalCode'],
            'telephone'                      => $row['telephone'],
            'mobilePhone'                    => $row['mobilePhone'],
            'alternateEmailAddress'          => $row['alternateEmailAddress'],
            'ageGroup'                       => $row['ageGroup'],
            'consentProvidedForMinor'        => $row['consentProvidedForMinor'],
            'legalAgeGroupClassification'    => $row['legalAgeGroupClassification'],
            'companyName'                    => $row['companyName'],
            'creationType'                   => $row['creationType'],
            'directorySynced'                => $directorySynced,
            'invitationState'                => $row['invitationState'],
            'identityIssuer'                 => $row['identityIssuer'],
            'createdDateTime'                => $row['createdDateTime'] ?? now(),
            'email'                          => $row['email'] ?? $row['mail'] ?? null,
            'password'                       => bcrypt($row['password'] ?? 'password123'), // Avoid default weak passwords in prod
        ]);
    }
}
