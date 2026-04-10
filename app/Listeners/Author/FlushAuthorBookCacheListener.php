<?php

namespace App\Listeners\Author;

use App\Events\Author\AuthorBookRelationsSyncedEvent;
use Illuminate\Support\Facades\Cache;

class FlushAuthorBookCacheListener
{
    /**
     * Handle the event.
     *
     * @param AuthorBookRelationsSyncedEvent $event
     * @return void
     */
    public function handle(AuthorBookRelationsSyncedEvent $event): void
    {
        Cache::tags(['author', 'book'])->flush();
    }
}
