<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderItem;
use App\Events\OrderPlaced;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * MarketplaceController
 * 
 * Handles discovery and lead conversion for harvest and agricultural tools.
 */
class MarketplaceController extends Controller
{
    // Display the public marketplace feed with filtering.
    public function index(Request $request): View
    {
        $query = Listing::with('seller')->where('status', 'active');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(fn($q) => 
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
            );
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return view('marketplace.index', [
            'listings'   => $query->latest()->paginate(12)->withQueryString(),
            'categories' => ['Harvest', 'Seeds', 'Tools', 'Fertilizer']
        ]);
    }

    // Display listing intelligence and community trust (reviews).
    public function show(Listing $listing): View
    {
        if ($listing->status !== 'active' && auth()->id() !== $listing->seller_id) {
            abort(404);
        }

        $listing->load(['seller', 'reviews.buyer']);
        
        return view('marketplace.show', compact('listing'));
    }

    // Initialize a negotiation thread from a buyer inquiry.
    public function inquire(Request $request, Listing $listing): JsonResponse
    {
        $request->validate([
            'message' => 'required|string|min:5|max:1000',
        ]);

        if (auth()->id() === $listing->seller_id) {
            return response()->json(['error' => 'Self-negotiation is prohibited.'], 403);
        }

        try {
            $order = DB::transaction(function () use ($listing, $request) {
                // Initialize negotiation record
                $order = Order::create([
                    'buyer_id'     => auth()->id(),
                    'seller_id'    => $listing->seller_id,
                    'total_price'  => $listing->price,
                    'order_status' => 'pending'
                ]);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'listing_id' => $listing->id,
                    'quantity'   => 1,
                    'price'      => $listing->price
                ]);

                // Create initial touchpoint message
                Message::create([
                    'sender_id'   => auth()->id(),
                    'receiver_id' => $listing->seller_id,
                    'listing_id'  => $listing->id,
                    'order_id'    => $order->id,
                    'message'     => $request->message,
                ]);

                return $order;
            });

            OrderPlaced::dispatch($order->load('buyer'));

            return response()->json([
                'success'  => true,
                'redirect' => route('marketplace.chat', $order)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Negotiation initialization failed.'], 500);
        }
    }
}
