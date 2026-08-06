<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait CleansUpPublicStorage
{
    /**
     * Delete a previously stored file on the "public" disk given its public
     * URL (as produced by Storage::disk('public')->url($path)), if any.
     */
    protected function deleteOldPublicFile(?string $url): void
    {
        if (! $url || ! str_contains($url, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($url, '/storage/'));
    }
}
