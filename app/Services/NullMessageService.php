<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NullMessageService implements MessageServiceInterface
{
    /**
     * No-op message sender used in local/test environments.
     * Logs the attempted send and returns true to indicate "success".
     */
    public function sendMessage(string $to, string $message): bool
    {
        Log::info(sprintf('[NullMessageService] would send message to %s: %s', $to, $message));
        return true;
    }
}
