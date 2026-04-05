<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
class ListingController extends Controller {
    public function index() { return response()->json(Listing::all()); }
    public function store(Request $request) {
        $validated = $request->validate([
            'seller_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'category' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|string',
            'location' => 'nullable|string'
        ]);
        return response()->json(Listing::create($validated), 201);
    }
    public function show(Listing $listing) { return response()->json($listing); }
    public function update(Request $request, Listing $listing) {
        $validated = $request->validate([
            'title' => 'nullable|string',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric',
            'quantity' => 'nullable|string',
            'location' => 'nullable|string'
        ]);
        $listing->update($validated);
        return response()->json($listing);
    }
    public function destroy(Listing $listing) {
        $listing->delete();
        return response()->json(null, 204);
    }
}