<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Testimonial;
use App\Http\Requests\TestimonialSubmissionRequest;
use Illuminate\Support\Facades\Auth;

class TestimonialController extends Controller
{
    public function index()
    {
        // Fetch only approved testimonials
        $testimonials = Testimonial::active()
            ->orderBy('rating', 'desc')
            ->paginate(6);

        return view('testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('testimonials.create');
    }

    public function store(TestimonialSubmissionRequest $request)
    {
        // Create a new testimonial with pending status
        $testimonial = Auth::user()->testimonials()->create([
            'name' => $request->input('name'),
            'content' => $request->input('content'),
            'position' => $request->input('position'),
            'company' => $request->input('company'),
            'rating' => $request->input('rating'),
            'status' => 'pending', // Explicitly set to lowercase
            'is_active' => false // Will be activated by admin
        ]);

        // Redirect with success message
        return redirect()->route('testimonials.index')
            ->with('success', 'Your testimonial has been submitted and is pending admin approval.');
    }

    // Method for users to view their submitted testimonials
    public function myTestimonials()
    {
        $testimonials = Auth::user()->testimonials()
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return view('testimonials.my-testimonials', compact('testimonials'));
    }
}
