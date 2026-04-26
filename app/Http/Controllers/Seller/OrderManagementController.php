<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Events\OrderStatusUpdated;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * OrderManagementController
 * 
 * Manages order lifecycle transitions and inventory reconciliation.
 */
class OrderManagementController extends Controller
{
    // Transition a pending order to accepted and reconcile inventory.
    public function accept(Order $order): JsonResponse
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        if ($order->order_status !== 'pending') {
            return response()->json(['error' => 'Invalid order state transition.'], 422);
        }

        try {
            DB::transaction(function () use ($order) {
                $order->update(['order_status' => 'accepted']);

                foreach ($order->items as $item) {
                    $listing = $item->listing;
                    $listing->decrement('quantity', $item->quantity);

                    if ($listing->quantity <= 0) {
                        $listing->update(['status' => 'sold', 'quantity' => 0]);
                    }
                }
            });

            // Dispatch status update event
            OrderStatusUpdated::dispatch($order->load(['items.listing', 'buyer']));

            return response()->json(['success' => true, 'message' => 'Order accepted and inventory reconciled.']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Inventory reconciliation failed.'], 500);
        }
    }

    // Transition an order to rejected.
    public function reject(Order $order): JsonResponse
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        $order->update(['order_status' => 'rejected']);

        // Dispatch status update event
        OrderStatusUpdated::dispatch($order->load(['items.listing', 'buyer']));

        return response()->json(['success' => true, 'message' => 'Order rejected.']);
    }
}
