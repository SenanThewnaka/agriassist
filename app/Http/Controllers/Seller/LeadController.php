<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

    // Manages marketplace inquiries and negotiations for the authenticated seller.
class LeadController extends Controller
{
    // Display a listing of negotiations (leads) received by the seller.
    public function index(): View
    {
        $leads = Order::with(['buyer', 'items.listing'])
            ->where('seller_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('seller.leads.index', compact('leads'));
    }

    // Redirect legacy detail view to the interactive negotiation room.
    public function show(Order $order)
    {
        if ($order->seller_id !== auth()->id()) {
            abort(403);
        }

        return redirect()->route('marketplace.chat', $order);
    }
}
