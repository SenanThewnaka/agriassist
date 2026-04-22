<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ReviewController
 * 
 * Handles submission of ratings and reviews by verified buyers.
 */
class ReviewController extends Controller
{
    /**
     * Submit a review for a specific listing.
     */
    public function store(Request $request, Listing $listing): JsonResponse
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|min:3|max:500',
        ]);

        $userId = auth()->id();

        // 1. Verify that the user has an accepted/completed order for this listing
        $hasOrdered = Order::where('buyer_id', $userId)
            ->whereIn('order_status', ['accepted', 'completed'])
            ->whereHas('items', function ($query) use ($listing) {
                $query->where('listing_id', $listing->id);
            })->exists();

        if (!$hasOrdered) {
            return response()->json(['error' => 'Only verified buyers can leave a review.'], 403);
        }

        // 2. Prevent duplicate reviews
        if (Review::where('user_id', $userId)->where('listing_id', $listing->id)->exists()) {
            return response()->json(['error' => 'You have already reviewed this product.'], 422);
        }

        Review::create([
            'listing_id' => $listing->id,
            'user_id'    => $userId,
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment']
        ]);

        return response()->json([
            'success' => true, 
            'message' => 'Review submitted successfully. Thank you for your feedback!'
        ]);
    }
}
