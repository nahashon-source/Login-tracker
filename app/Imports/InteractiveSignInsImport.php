<?php

namespace App\Imports;

use App\Models\InteractiveSignIn;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class InteractiveSignInsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Normalize headers
        $row = array_change_key_case($row, CASE_LOWER);

        // Sanitize user_id string
        $userId = isset($row['user_id']) 
            ? preg_replace('/[^a-fA-F0-9\-]/', '', $row['user_id']) 
            : null;

        // Check user_id is valid
        if (!$userId) {
            Log::channel('import')->warning("Missing or invalid user_id in row: " . json_encode($row));
            return null;
        }

        // Confirm user exists
        if (!User::find($userId)) {
            Log::channel('import')->warning("User ID '{$userId}' not found in users table");
            return null;
        }

        // Validate required fields
        $validator = Validator::make([
            'date_utc' => $row['date_utc'] ?? null,
            'status'   => $row['status'] ?? null,
        ], [
            'date_utc' => 'required|date',
            'status'   => 'required',
        ]);

        if ($validator->fails()) {
            Log::channel('import')->warning("Row validation failed for user_id {$userId}", $validator->errors()->toArray());
            return null;
        }

        // Parse date safely
        $dateUTC = null;
        try {
            $dateUTC = Carbon::parse($row['date_utc'])->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::channel('import')->warning("Invalid date format for user_id {$userId}: '{$row['date_utc']}'");
        }

        // Prepare new model instance
        return new InteractiveSignIn([
            'date_utc'  => $dateUTC,
            'request_id' => $row['request_id'] ?? null,
            'user_agent' => $row['user_agent'] ?? null,
            'correlation_id' => $row['correlation_id'] ?? null,
            'user_id' => $userId,
            'user' => $row['user'] ?? null,
            'username' => $row['username'] ?? null,
            'user_type' => $row['user_type'] ?? null,
            'cross_tenant_access_type' => $row['cross_tenant_access_type'] ?? null,
            'incoming_token_type' => $row['incoming_token_type'] ?? null,
            'authentication_protocol' => $row['authentication_protocol'] ?? null,
            'unique_token_identifier' => $row['unique_token_identifier'] ?? null,
            'original_transfer_method' => $row['original_transfer_method'] ?? null,
            'client_credential_type' => $row['client_credential_type'] ?? null,
            'token_protection_sign_in_session' => filter_var($row['token_protection_sign_in_session'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'application' => $row['application'] ?? null,
            'application_id' => $row['application_id'] ?? null,
            'resource' => $row['resource'] ?? null,
            'resource_id' => $row['resource_id'] ?? null,
            'resource_tenant_id' => $row['resource_tenant_id'] ?? null,
            'resource_owner_tenant_id' => $row['resource_owner_tenant_id'] ?? null,
            'home_tenant_id' => $row['home_tenant_id'] ?? null,
            'home_tenant_name' => $row['home_tenant_name'] ?? null,
            'ip_address' => $row['ip_address'] ?? null,
            'location' => $row['location'] ?? null,
            'status' => $row['status'],
            'sign_in_error_code' => $row['sign_in_error_code'] ?? null,
            'failure_reason' => $row['failure_reason'] ?? null,
            'client_app' => $row['client_app'] ?? null,
            'device_id' => $row['device_id'] ?? null,
            'browser' => $row['browser'] ?? null,
            'operating_system' => $row['operating_system'] ?? null,
            'compliant' => filter_var($row['compliant'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'managed' => filter_var($row['managed'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'join_type' => $row['join_type'] ?? null,
            'multifactor_authentication_result' => isset($row['multifactor_authentication_result']) 
                ? Str::limit($row['multifactor_authentication_result'], 250) 
                : null,
            'multifactor_authentication_auth_method' => $row['multifactor_authentication_auth_method'] ?? null,
            'multifactor_authentication_auth_detail' => $row['multifactor_authentication_auth_detail'] ?? null,
            'authentication_requirement' => $row['authentication_requirement'] ?? null,
            'sign_in_identifier' => $row['sign_in_identifier'] ?? null,
            'session_id' => $row['session_id'] ?? null,
            'ip_address_seen_by_resource' => $row['ip_address_seen_by_resource'] ?? null,
            'through_global_secure_access' => filter_var($row['through_global_secure_access'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'global_secure_access_ip_address' => $row['global_secure_access_ip_address'] ?? null,
            'autonomous_system_number' => $row['autonomous_system_number'] ?? null,
            'flagged_for_review' => filter_var($row['flagged_for_review'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'token_issuer_type' => $row['token_issuer_type'] ?? null,
            'incoming_token_type_duplicate' => $row['incoming_token_type_duplicate'] ?? null,
            'token_issuer_name' => $row['token_issuer_name'] ?? null,
            'latency' => is_numeric($row['latency'] ?? null) ? $row['latency'] : null,
            'conditional_access' => $row['conditional_access'] ?? null,
            'managed_identity_type' => $row['managed_identity_type'] ?? null,
            'associated_resource_id' => $row['associated_resource_id'] ?? null,
            'federated_token_id' => $row['federated_token_id'] ?? null,
            'federated_token_issuer' => $row['federated_token_issuer'] ?? null,
        ]);
    }
}
