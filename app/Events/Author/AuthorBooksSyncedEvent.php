<?php

namespace App\Events\Author;

use App\Models\Author;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuthorBooksSyncedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Author $author;

    /**
     * Create a new event instance.
     *
     * @param Author $author
     */
    public function __construct(Author $author)
    {
        $this->author = $author;
    }
}
