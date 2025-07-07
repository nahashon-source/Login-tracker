<?php

namespace App\Imports;

use App\Models\InteractiveSignIn;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InteractiveSignInsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new InteractiveSignIn([
            'date_utc' => $row['date_utc'],
            'request_id' => $row['request_id'],
            'user_agent' => $row['user_agent'],
            'correlation_id' => $row['correlation_id'],
            'user_id' => $row['user_id'],
            'user' => $row['user'],
            'username' => $row['username'],
            'user_type' => $row['user_type'],
            'cross_tenant_access_type' => $row['cross_tenant_access_type'],
            'incoming_token_type' => $row['incoming_token_type'],
            'authentication_protocol' => $row['authentication_protocol'],
            'unique_token_identifier' => $row['unique_token_identifier'],
            'original_transfer_method' => $row['original_transfer_method'],
            'client_credential_type' => $row['client_credential_type'],
            'token_protection_sign_in_session' => $row['token_protection_sign_in_session'] === 'true',
            'application' => $row['application'],
            'application_id' => $row['application_id'],
            'resource' => $row['resource'],
            'resource_id' => $row['resource_id'],
            'resource_tenant_id' => $row['resource_tenant_id'],
            'resource_owner_tenant_id' => $row['resource_owner_tenant_id'],
            'home_tenant_id' => $row['home_tenant_id'],
            'home_tenant_name' => $row['home_tenant_name'],
            'ip_address' => $row['ip_address'],
            'location' => $row['location'],
            'status' => $row['status'],
            'sign_in_error_code' => $row['sign_in_error_code'],
            'failure_reason' => $row['failure_reason'],
            'client_app' => $row['client_app'],
            'device_id' => $row['device_id'],
            'browser' => $row['browser'],
            'operating_system' => $row['operating_system'],
            'compliant' => $row['compliant'] === 'true',
            'managed' => $row['managed'] === 'true',
            'join_type' => $row['join_type'],
            'multifactor_authentication_result' => $row['multifactor_authentication_result'],
            'multifactor_authentication_auth_method' => $row['multifactor_authentication_auth_method'],
            'multifactor_authentication_auth_detail' => $row['multifactor_authentication_auth_detail'],
            'authentication_requirement' => $row['authentication_requirement'],
            'sign_in_identifier' => $row['sign_in_identifier'],
            'session_id' => $row['session_id'],
            'ip_address_seen_by_resource' => $row['ip_address_seen_by_resource'],
            'through_global_secure_access' => $row['through_global_secure_access'] === 'true',
            'global_secure_access_ip_address' => $row['global_secure_access_ip_address'],
            'autonomous_system_number' => $row['autonomous_system_number'],
            'flagged_for_review' => $row['flagged_for_review'] === 'true',
            'token_issuer_type' => $row['token_issuer_type'],
            'incoming_token_type_duplicate' => $row['incoming_token_type_duplicate'],
            'token_issuer_name' => $row['token_issuer_name'],
            'latency' => $row['latency'],
            'conditional_access' => $row['conditional_access'],
            'managed_identity_type' => $row['managed_identity_type'],
            'associated_resource_id' => $row['associated_resource_id'],
            'federated_token_id' => $row['federated_token_id'],
            'federated_token_issuer' => $row['federated_token_issuer'],
        ]);
    }
}