<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportLockService
{
    private const LOCK_KEY = 'import_in_progress';
    private const LOCK_TIMEOUT = 300; // 5 minutes

    public function acquireLock(): bool
    {
        $acquired = Cache::add(self::LOCK_KEY, true, self::LOCK_TIMEOUT);
        
        if ($acquired) {
            Log::info('Import lock acquired');
        } else {
            Log::warning('Import lock acquisition failed - another import in progress');
        }
        
        return $acquired;
    }

    public function releaseLock(): void
    {
        Cache::forget(self::LOCK_KEY);
        Log::info('Import lock released');
    }

    public function isLocked(): bool
    {
        return Cache::has(self::LOCK_KEY);
    }
}
