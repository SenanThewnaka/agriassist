<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

/**
 * ChatController
 * 
 * Orchestrates real-time P2P negotiations.
 */
class ChatController extends Controller
{
    public function index(): View
    {
        $userId = auth()->id();

        $negotiations = Order::with(['buyer', 'seller', 'items.listing'])
            ->where('buyer_id', $userId)
            ->orWhere('seller_id', $userId)
            ->get()
            ->map(function($order) use ($userId) {
                $order->is_seller = $order->seller_id === $userId;
                $order->other_party = $order->is_seller ? $order->buyer : $order->seller;
                
                // Link preview via first item listing ID
                $order->latest_msg = Message::where('listing_id', $order->items->first()->listing_id)
                                        ->latest()
                                        ->first();
                
                return $order;
            })
            ->sortByDesc(fn($order) => $order->latest_msg?->created_at ?? $order->created_at);

        return view('marketplace.inbox', compact('negotiations'));
    }

    public function show(Order $order): View
    {
        if (auth()->id() !== $order->buyer_id && auth()->id() !== $order->seller_id) {
            abort(403);
        }

        $order->load(['buyer', 'seller', 'items.listing']);
        
        return view('marketplace.chat', compact('order'));
    }

    public function getMessages(Order $order): JsonResponse
    {
        $messages = Message::with('sender')
            ->where('listing_id', $order->items->first()->listing_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $receiverId = auth()->id() === $order->buyer_id ? $order->seller_id : $order->buyer_id;

        $message = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $receiverId,
            'message'     => $validated['message'],
            'listing_id'  => $order->items->first()->listing_id,
        ]);

        MessageSent::dispatch($message, (int)$order->id);

        return response()->json([
            'success' => true,
            'message' => $message->load('sender')
        ]);
    }
}
