<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Create a new event instance.
    public function __construct(
        public Order $order
    ) {}

    // Get the channels the event should broadcast on.
    public function broadcastOn(): array
    {
        // Broadcast to both buyer and seller
        return [
            new PrivateChannel('App.Models.User.' . $this->order->buyer_id),
            new PrivateChannel('App.Models.User.' . $this->order->seller_id),
        ];
    }

    // The event's broadcast name.
    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    // Data to broadcast.
    public function broadcastWith(): array
    {
        return [
            'id'           => $this->order->id,
            'status'       => $this->order->order_status,
            'message'      => "Your order for {$this->order->items->first()->listing->title} has been {$this->order->order_status}."
        ];
    }
}
