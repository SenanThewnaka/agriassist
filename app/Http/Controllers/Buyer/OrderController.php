<?php

declare(strict_types=1);

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Message;
use App\Events\OrderPlaced;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

    // Manages the buyer's request flow for marketplace items.
class OrderController extends Controller
{
    // Handle the placement of a new order request.
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'requested_quantity' => 'required|numeric|min:0.1',
        ]);

        // 1. Availability Check
        if ($validated['requested_quantity'] > $listing->quantity) {
            return back()->with('error', 'Requested quantity exceeds available stock.');
        }

        // 2. Prevent self-ordering
        if (auth()->id() === $listing->seller_id) {
            return back()->with('error', 'You cannot place an order for your own listing.');
        }

        try {
            $order = DB::transaction(function () use ($listing, $validated) {
                // Create the master order record
                $order = Order::create([
                    'buyer_id'     => auth()->id(),
                    'seller_id'    => $listing->seller_id,
                    'total_price'  => $listing->price * $validated['requested_quantity'],
                    'order_status' => 'pending'
                ]);

                // Link the specific listing
                OrderItem::create([
                    'order_id'   => $order->id,
                    'listing_id' => $listing->id,
                    'quantity'   => $validated['requested_quantity'],
                    'price'      => $listing->price
                ]);

                // Create initial auto-message to identify the order in chat
                Message::create([
                    'sender_id'   => auth()->id(),
                    'receiver_id' => $listing->seller_id,
                    'listing_id'  => $listing->id,
                    'order_id'    => $order->id,
                    'message'     => "I'm interested in placing an order for {$validated['requested_quantity']} {$listing->unit} of {$listing->title}. Estimated total: Rs. " . number_format($listing->price * $validated['requested_quantity']),
                ]);

                return $order;
            });

            // Dispatch Real-Time Notification to Seller
            OrderPlaced::dispatch($order->load('buyer'));

            return redirect()->route('marketplace.chat', $order)
                ->with('success', 'Order request placed. Start chatting with the seller!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to place order request: ' . $e->getMessage());
        }
    }
}
