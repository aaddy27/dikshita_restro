<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'nullable|string|max:20',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1000',
            'favorite_dish' => 'nullable|string|max:150',
        ]);

        $validated['is_approved'] = true;

        $review = Review::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your review! Your feedback helps us serve you better.',
                'review' => $review,
            ]);
        }

        return back()->with('review_success', 'Thank you for your review! Your feedback helps us serve you better.');
    }
}
