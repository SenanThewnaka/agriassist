<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\Order;
use Illuminate\View\View;

/**
 * DashboardController
 * 
 * Aggregates active listings and transaction state for sellers.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        
        $stats = [
            'active_listings' => Listing::where('seller_id', $user->id)->where('status', 'active')->count(),
            'total_leads'     => Order::where('seller_id', $user->id)->count(),
            'recent_inquiries' => Order::with(['buyer', 'items.listing'])
                                    ->where('seller_id', $user->id)
                                    ->latest()
                                    ->take(5)
                                    ->get(),
        ];

        return view('seller.dashboard', compact('stats'));
    }
}
