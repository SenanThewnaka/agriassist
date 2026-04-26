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

class OrderPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Create a new event instance.
    public function __construct(
        public Order $order
    ) {}

    // Get the channels the event should broadcast on.
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->order->seller_id),
        ];
    }

    // The event's broadcast name.
    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    // Data to broadcast.
    public function broadcastWith(): array
    {
        return [
            'id'          => $this->order->id,
            'buyer_name'  => $this->order->buyer->full_name,
            'total_price' => number_format($this->order->total_price, 2),
            'message'     => 'You have received a new order request!'
        ];
    }
}
