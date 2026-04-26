<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Create a new event instance.
    public function __construct(
        public Message $message,
        public int $orderId
    ) {
        Log::debug("MessageSent event dispatched", ['order_id' => $orderId, 'message_id' => $message->id]);
    }

    // Get the channels the event should broadcast on.
    public function broadcastOn(): array
    {
        Log::debug("Broadcasting MessageSent on channel: order." . $this->orderId);
        return [
            new PrivateChannel('order.' . $this->orderId),
        ];
    }

    // The event's broadcast name.
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    // Data to broadcast.
    public function broadcastWith(): array
    {
        return [
            'id'         => $this->message->id,
            'message'    => $this->message->message,
            'sender_id'  => $this->message->sender_id,
            'created_at' => $this->message->created_at->toDateTimeString(),
            'sender'     => [
                'full_name' => $this->message->sender->full_name
            ]
        ];
    }
}
