<?php

namespace App\Imports;

use App\Models\SigninLog;
use App\Models\User;
use App\Models\System;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SigninLogsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $row = array_change_key_case($row, CASE_LOWER);

        $userId = null;
        $userPrincipalName = strtolower(trim($row['username'] ?? ''));

        // Skip if username is a UUID (e.g., system accounts or service principals)
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $userPrincipalName)) {
            Log::channel('import')->warning("Skipping UUID-only username: {$userPrincipalName}");
            return null;
        }

        // Proceed only if valid email
        if (filter_var($userPrincipalName, FILTER_VALIDATE_EMAIL)) {
            $user = User::whereRaw('LOWER(TRIM(userPrincipalName)) = ?', [$userPrincipalName])->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        if (is_null($userId)) {
            Log::channel('import')->warning("User not found for username: {$userPrincipalName}");
            return null;
        }

        // Map application to system
        $application = $row['application'] ?? null;
        $normalizedApp = strtolower(trim($application));

        // Use the systemmap.php configuration
        $systemMapping = config('systemmap', []);
        $system = $systemMapping[$normalizedApp] ?? null;
        
        // If no mapping found, try to find a partial match
        if (!$system) {
            foreach ($systemMapping as $appKey => $systemName) {
                if (strpos($normalizedApp, $appKey) !== false || strpos($appKey, $normalizedApp) !== false) {
                    $system = $systemName;
                    break;
                }
            }
        }
        
        // Default to 'Unknown' if no mapping found
        $system = $system ?? 'Unknown';
        
        // Auto-create system if it doesn't exist
        if ($system !== 'Unknown') {
            System::firstOrCreate(['name' => $system]);
        }

        return new SigninLog([
            'date_utc'                          => Carbon::parse($row['date (utc)'])->format('Y-m-d H:i:s'),
            'request_id'                        => $row['request id'] ?? null,
            'user_agent'                        => $row['user agent'] ?? null,
            'correlation_id'                    => $row['correlation id'] ?? null,
            'user_id'                           => $userId,
            'user'                              => $row['user'] ?? null,
            'username'                          => $row['username'] ?? null,
            'user_type'                         => $row['user type'] ?? null,
            'cross_tenant_access_type'          => $row['cross tenant access type'] ?? null,
            'incoming_token_type'               => $row['incoming token type'] ?? null,
            'authentication_protocol'           => $row['authentication protocol'] ?? null,
            'unique_token_identifier'           => $row['unique token identifier'] ?? null,
            'original_transfer_method'          => $row['original transfer method'] ?? null,
            'client_credential_type'            => $row['client credential type'] ?? null,
            'token_protection_sign_in_session'  => $row['token protection - sign in session'] ?? null,
            'token_protection_status_code'      => $row['token protection - sign in session statuscode'] ?? null,
            'application'                       => $application,
            'system'                            => $system,
            'application_id'                    => $row['application id'] ?? null,
            'resource'                          => $row['resource'] ?? null,
            'resource_id'                       => $row['resource id'] ?? null,
            'resource_tenant_id'                => $row['resource tenant id'] ?? null,
            'resource_owner_tenant_id'          => $row['resource owner tenant id'] ?? null,
            'home_tenant_id'                    => $row['home tenant id'] ?? null,
            'home_tenant_name'                  => $row['home tenant name'] ?? null,
            'ip_address'                        => $row['ip address'] ?? null,
            'location'                          => $row['location'] ?? null,
            'status'                            => $row['status'] ?? null,
            'sign_in_error_code'                => $row['sign-in error code'] ?? null,
            'failure_reason'                    => $row['failure reason'] ?? null,
            'client_app'                        => $row['client app'] ?? null,
            'device_id'                         => $row['device id'] ?? null,
            'browser'                           => $row['browser'] ?? null,
            'operating_system'                  => $row['operating system'] ?? null,
            'compliant'                         => $row['compliant'] ?? null,
            'managed'                           => $row['managed'] ?? null,
            'join_type'                         => $row['join type'] ?? null,
            'multifactor_auth_result'           => $row['multifactor authentication result'] ?? null,
            'multifactor_auth_method'           => $row['multifactor authentication auth method'] ?? null,
            'multifactor_auth_detail'           => $row['multifactor authentication auth detail'] ?? null,
            'authentication_requirement'        => $row['authentication requirement'] ?? null,
            'sign_in_identifier'                => $row['sign-in identifier'] ?? null,
            'session_id'                        => $row['session id'] ?? null,
            'ip_address_seen_by_resource'       => $row['ip address (seen by resource)'] ?? null,
            'through_global_secure_access'      => $row['through global secure access'] ?? null,
            'global_secure_access_ip'           => $row['global secure access ip address'] ?? null,
            'autonomous_system_number'          => $row['autonomous system  number'] ?? null,
            'flagged_for_review'                => $row['flagged for review'] ?? null,
            'token_issuer_type'                 => $row['token issuer type'] ?? null,
            'token_issuer_name'                 => $row['token issuer name'] ?? null,
            'latency'                           => $row['latency'] ?? null,
            'conditional_access'                => $row['conditional access'] ?? null,
            'managed_identity_type'             => $row['managed identity type'] ?? null,
            'associated_resource_id'            => $row['associated resource id'] ?? null,
            'federated_token_id'                => $row['federated token id'] ?? null,
            'federated_token_issuer'            => $row['federated token issuer'] ?? null,
        ]);
    }
}
