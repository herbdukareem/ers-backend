<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewEventNotification
{
    use Dispatchable, SerializesModels;

    protected $followers;
    protected $event;

    public function __construct($followers, $event)
    {
        $this->followers = $followers;
        $this->event = $event;
    }

    public function broadcastOn()
    {
        // Batch broadcasting to followers' channels
        $batchSize = 100; // Number of notifications per batch

        $channels = [];
        foreach (array_chunk($this->followers, $batchSize) as $batch) {
            $batchChannels = collect($batch)->map(function ($follower) {
                return new Channel('notification.'.$follower->id);
            })->toArray();

            $channels[] = $batchChannels;
        }

        return $channels;
    }

    public function broadcastWith()
    {
        return $this->event;
    }

    public function broadcastAs(): string
    {
        return 'eventNotification';
    }
}
