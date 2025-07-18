<?php

namespace App\Events;

use App\Models\ImportRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportRequestCreated
{
    use Dispatchable;

    public ImportRequest $importRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(ImportRequest $importRequest)
    {

        $this->importRequest = $importRequest;
    }

}
