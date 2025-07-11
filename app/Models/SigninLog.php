<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing sign-in records for application users.
 *
 * Each record logs metadata about a user's sign-in attempt, 
 * including authentication details, IP addresses, device info, 
 * and conditional access outcomes.
 */
class SigninLog extends Model
{
    use HasFactory;

    /**
     * Explicitly define the database table used by this model.
     *
     * @var string
     */
    protected $table = 'signin_logs'; // ✅ Explicitly match your actual table name

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'date_utc', 'request_id', 'user_agent', 'correlation_id', 'user_id',
        'user', 'username', 'user_type', 'cross_tenant_access_type',
        'incoming_token_type', 'authentication_protocol', 'unique_token_identifier',
        'original_transfer_method', 'client_credential_type',
        'token_protection_sign_in_session', 'application', 'application_id',
        'resource', 'resource_id', 'resource_tenant_id', 'resource_owner_tenant_id',
        'home_tenant_id', 'home_tenant_name', 'ip_address', 'location', 'status',
        'sign_in_error_code', 'failure_reason', 'client_app', 'device_id',
        'browser', 'operating_system', 'compliant', 'managed', 'join_type',
        'multifactor_authentication_result', 'multifactor_authentication_auth_method',
        'multifactor_authentication_auth_detail', 'authentication_requirement',
        'sign_in_identifier', 'session_id', 'ip_address_seen_by_resource',
        'through_global_secure_access', 'global_secure_access_ip_address',
        'autonomous_system_number', 'flagged_for_review', 'token_issuer_type',
        'incoming_token_type_duplicate', 'token_issuer_name', 'latency',
        'conditional_access', 'managed_identity_type', 'associated_resource_id',
        'federated_token_id', 'federated_token_issuer',
    ];

    /**
     * Get the user associated with this sign-in event.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope: Filter sign-ins within the last 30 days.
     */
    public function scopeLast30Days($query)
    {
        return $query->where('date_utc', '>=', now()->subDays(30));
    }

    /**
     * Scope: Order sign-in records by most recent first.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('date_utc', 'desc');
    }
}
