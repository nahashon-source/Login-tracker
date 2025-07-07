<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        if (empty($row['id'])) {
            throw new \Exception('ID is required and cannot be empty');
        }
    
        $user = User::updateOrCreate(
            ['id' => $row['id']],
            [
                'userPrincipalName' => $row['userPrincipalName'],
                'displayName' => $row['displayName'],
                'surname1' => $row['surname'] ?? null,
                'surname2' => $row['surname_2'] ?? null,
                'mail1' => $row['mail'] ?? null,
                'mail2' => $row['mail_2'] ?? null,
                'givenName1' => $row['givenName'] ?? null,
                'givenName2' => $row['givenName_2'] ?? null,
                'userType' => $row['userType'],
                'jobTitle' => $row['jobTitle'],
                'department' => $row['department'],
                'accountEnabled' => $row['accountEnabled'] === 'true',
                'usageLocation' => $row['usageLocation'],
                'streetAddress' => $row['streetAddress'],
                'state' => $row['state'],
                'country' => $row['country'],
                'officeLocation' => $row['officeLocation'],
                'city' => $row['city'],
                'postalCode' => $row['postalCode'],
                'telephone' => $row['telephone'],
                'mobilePhone' => $row['mobilePhone'],
                'alternateEmailAddress' => $row['alternateEmailAddress'],
                'ageGroup' => $row['ageGroup'],
                'consentProvidedForMinor' => $row['consentProvidedForMinor'],
                'legalAgeGroupClassification' => $row['legalAgeGroupClassification'],
                'companyName' => $row['companyName'],
                'creationType' => $row['creationType'],
                'directorySynced' => $row['directorySynced'] === 'true',
                'invitationState' => $row['invitationState'],
                'identityIssuer' => $row['identityIssuer'],
                'createdDateTime' => $row['createdDateTime'],
            ]
        );
    
        return $user;
    }
    
}
