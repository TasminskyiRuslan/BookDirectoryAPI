<?php

namespace App\Listeners\Author;

use App\Events\Author\AuthorBooksSyncedEvent;
use Illuminate\Support\Facades\Cache;

class FlushAuthorCacheListener
{
    /**
     * Handle the event.
     *
     * @param AuthorBooksSyncedEvent $event
     * @return void
     */
    public function handle(AuthorBooksSyncedEvent $event): void
    {
        Cache::tags(['author', 'book'])->flush();
    }
}
