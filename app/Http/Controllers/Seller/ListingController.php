<?php

declare(strict_types=1);

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Services\ImageUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

    // Manages the lifecycle of marketplace listings for the authenticated seller.
class ListingController extends Controller
{
    public function __construct(
        private ImageUploadService $imageUploadService
    ) {}

    // Display a listing of the seller's items.
    public function index(): View
    {
        $listings = Listing::where('seller_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('seller.listings.index', compact('listings'));
    }

    // Show the form for creating a new listing.
    public function create(): View
    {
        $userFarms = auth()->user()->farms;
        return view('seller.listings.create', compact('userFarms'));
    }

    // Store a newly created listing in storage.
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|string',
            'unit'        => 'required|string',
            'location'    => 'required|string',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'images.*'    => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->imageUploadService->upload($image, 'listings');
                if ($path) $imagePaths[] = $path;
            }
        }

        Listing::create(array_merge($validated, [
            'seller_id' => auth()->id(),
            'images'    => $imagePaths,
            'status'    => 'active'
        ]));

        return redirect()->route('seller.listings.index')
            ->with('success', 'Listing created successfully.');
    }

    // Show the form for editing the specified listing.
    public function edit(Listing $listing): View
    {
        $this->authorizeSeller($listing);
        $userFarms = auth()->user()->farms;
        return view('seller.listings.edit', compact('listing', 'userFarms'));
    }

    // Update the specified listing in storage.
    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $this->authorizeSeller($listing);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|string',
            'unit'        => 'required|string',
            'location'    => 'required|string',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'status'      => 'required|in:active,sold,archived',
        ]);

        $listing->update($validated);

        return redirect()->route('seller.listings.index')
            ->with('success', 'Listing updated successfully.');
    }

    // Remove the specified listing from storage.
    public function destroy(Listing $listing): RedirectResponse
    {
        $this->authorizeSeller($listing);
        $listing->delete();

        return redirect()->route('seller.listings.index')
            ->with('success', 'Listing deleted successfully.');
    }

    private function authorizeSeller(Listing $listing): void
    {
        if ($listing->seller_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
