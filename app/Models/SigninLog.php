<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Model representing sign-in records for application users.
 */
class SigninLog extends Model
{
    use HasFactory;

    protected $table = 'signin_logs';

    protected $fillable = [
        'date_utc', 'request_id', 'user_agent', 'correlation_id', 'user_id',
        'system', 'user', 'username', 'user_type', 'cross_tenant_access_type',
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

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeLast30Days($query)
    {
        return $query->where('date_utc', '>=', now()->subDays(30));
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('date_utc', 'desc');
    }

    public function scopeByApplication($query, $application)
    {
        return $query->where('application', $application);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getDateUtcFormattedAttribute()
    {
        return $this->date_utc ? Carbon::parse($this->date_utc)->format('Y-m-d H:i:s') : null;
    }

    public function getIpAddressFormattedAttribute()
    {
        return $this->ip_address ?: 'N/A';
    }

    public function getStatusLabelAttribute()
    {
        return match ($this->status) {
            'Success' => 'Successful',
            'Failed' => 'Failed',
            default => 'Unknown',
        };
    }

    public function isSuccessful()
    {
        return $this->status === 'Success';
    }

    public function isFailed()
    {
        return $this->status === 'Failed';
    }

    // Optional: Update to reflect actual systems if needed
    public static function getSystemList()
    {
        return SigninLog::select('system')->distinct()->pluck('system')->toArray();
    }
}