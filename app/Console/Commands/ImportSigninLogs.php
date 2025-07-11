<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SigninLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ImportInteractiveSignIns extends Command
{
    protected $signature = 'import:signin-logs {file}';
    protected $description = 'Import Signin Log records from CSV';

    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("❌ File not found: $filePath");
            return 1;
        }

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->error("❌ Unable to open file: $filePath");
            return 1;
        }

        $header = fgetcsv($handle); // read header row
        $expectedColumns = count($header);

        $this->info("✅ Detected $expectedColumns columns in CSV header.");

        $count = 0;
        $skipped = 0;
        $lineNumber = 1; // starts after header

        DB::beginTransaction();

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if (count($data) !== $expectedColumns) {
                    $this->warn("⚠️  Skipping row $lineNumber — found " . count($data) . " columns, expected $expectedColumns.");
                    $skipped++;
                    continue;
                }

                $record = new SigninLog();
                $record->id = (string) Str::uuid();

                // Safe date parsing
                $record->date_utc = (!empty($data[0]) && strtotime($data[0]))
                    ? Carbon::parse($data[0])->format('Y-m-d H:i:s')
                    : null;

                $record->request_id = $data[1];
                $record->user_agent = $data[2];
                $record->correlation_id = $data[3];
                $record->user_id = $data[4];
                $record->user = $data[5];
                $record->username = $data[6];
                $record->user_type = $data[7];
                $record->cross_tenant_access_type = $data[8];
                $record->incoming_token_type = $data[9];
                $record->authentication_protocol = $data[10];
                $record->unique_token_identifier = $data[11];
                $record->original_transfer_method = $data[12];
                $record->client_credential_type = $data[13];
                $record->token_protection_sign_in_session = $data[14];
                $record->token_protection_sign_in_session_status_code = $data[15];
                $record->application = $data[16];
                $record->application_id = $data[17];
                $record->app_owner_tenant_id = $data[18];
                $record->resource = $data[19];
                $record->resource_id = $data[20];
                $record->resource_tenant_id = $data[21];
                $record->resource_owner_tenant_id = $data[22];
                $record->home_tenant_id = $data[23];
                $record->home_tenant_name = $data[24];
                $record->ip_address = $data[25];
                $record->location = $data[26];
                $record->status = $data[27];
                $record->sign_in_error_code = $data[28];
                $record->failure_reason = $data[29];
                $record->client_app = $data[30];
                $record->device_id = $data[31];
                $record->browser = $data[32];
                $record->operating_system = $data[33];

                // Safe numeric casting for tinyint(1) columns
                $record->compliant = is_numeric($data[34]) ? (int)$data[34] : null;
                $record->managed = is_numeric($data[35]) ? (int)$data[35] : null;

                $record->join_type = $data[36];
                $record->multifactor_authentication_result = $data[37];
                $record->multifactor_authentication_auth_method = $data[38];
                $record->multifactor_authentication_auth_detail = $data[39];
                $record->authentication_requirement = $data[40];
                $record->sign_in_identifier = $data[41];
                $record->session_id = $data[42];
                $record->ip_address_seen_by_resource = $data[43];
                $record->through_global_secure_access = $data[44];
                $record->global_secure_access_ip_address = $data[45];
                $record->autonomous_system_number = $data[46];
                $record->flagged_for_review = $data[47];
                $record->token_issuer_type = $data[48];

                // Defensive: assign if column exists
                $record->incoming_token_type_duplicate = ($expectedColumns > 56) ? $data[49] : null;

                $record->token_issuer_name = $data[49];
                $record->latency = $data[50];
                $record->conditional_access = $data[51];
                $record->managed_identity_type = $data[52];
                $record->associated_resource_id = $data[53];
                $record->federated_token_id = $data[54];
                $record->federated_token_issuer = $data[55];

                $record->created_at = Carbon::now();
                $record->updated_at = Carbon::now();

                // Save safely with error catch
                try {
                    $record->save();
                    $count++;
                } catch (\Exception $e) {
                    $this->error("❌ Failed to save record on line $lineNumber: " . $e->getMessage());
                    DB::rollBack();
                    fclose($handle);
                    return 1;
                }
            }

            DB::commit();
            fclose($handle);

            $this->info("✅ Imported $count records successfully.");
            if ($skipped > 0) {
                $this->warn("⚠️  Skipped $skipped rows due to invalid column counts.");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            $this->error("❌ Critical error: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
