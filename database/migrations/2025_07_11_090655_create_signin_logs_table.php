<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSigninLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('signin_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_utc')->nullable();
            $table->string('request_id')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('correlation_id')->nullable();
            $table->string('user_id')->nullable();
            $table->string('user')->nullable();
            $table->string('username')->nullable();
            $table->string('user_type')->nullable();
            $table->string('cross_tenant_access_type')->nullable();
            $table->string('incoming_token_type')->nullable();
            $table->string('authentication_protocol')->nullable();
            $table->string('unique_token_identifier')->nullable();
            $table->string('original_transfer_method')->nullable();
            $table->string('client_credential_type')->nullable();
            $table->string('token_protection_sign_in_session')->nullable();
            $table->string('token_protection_sign_in_session_status_code')->nullable();
            $table->string('application')->nullable();
            $table->string('application_id')->nullable();
            $table->string('app_owner_tenant_id')->nullable();
            $table->string('resource')->nullable();
            $table->string('resource_id')->nullable();
            $table->string('resource_tenant_id')->nullable();
            $table->string('resource_owner_tenant_id')->nullable();
            $table->string('home_tenant_id')->nullable();
            $table->string('home_tenant_name')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->nullable();
            $table->string('sign_in_error_code')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('client_app')->nullable();
            $table->string('device_id')->nullable();
            $table->string('browser')->nullable();
            $table->string('operating_system')->nullable();
            $table->boolean('compliant')->nullable();
            $table->boolean('managed')->nullable();
            $table->string('join_type')->nullable();
            $table->string('multifactor_authentication_result')->nullable();
            $table->string('multifactor_authentication_auth_method')->nullable();
            $table->string('multifactor_authentication_auth_detail')->nullable();
            $table->string('authentication_requirement')->nullable();
            $table->string('sign_in_identifier')->nullable();
            $table->string('session_id')->nullable();
            $table->ipAddress('ip_address_seen_by_resource')->nullable();
            $table->boolean('through_global_secure_access')->nullable();
            $table->ipAddress('global_secure_access_ip_address')->nullable();
            $table->string('autonomous_system_number')->nullable();
            $table->boolean('flagged_for_review')->nullable();
            $table->string('token_issuer_type')->nullable();
            $table->string('incoming_token_type_2')->nullable(); // Duplicate
            $table->string('token_issuer_name')->nullable();
            $table->string('latency')->nullable();
            $table->string('conditional_access')->nullable();
            $table->string('managed_identity_type')->nullable();
            $table->string('associated_resource_id')->nullable();
            $table->string('federated_token_id')->nullable();
            $table->string('federated_token_issuer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signin_logs');
    }
}
