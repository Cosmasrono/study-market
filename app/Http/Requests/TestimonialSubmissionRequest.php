<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TestimonialSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only authenticated users can submit testimonials
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'content' => 'required|string|min:20|max:500',
            'position' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'rating' => 'required|integer|min:1|max:5'
        ];
    }

    /**
     * Get custom error messages for validation
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'content.required' => 'Your testimonial message is required.',
            'content.min' => 'Your testimonial should be at least 20 characters long.',
            'content.max' => 'Your testimonial cannot exceed 500 characters.',
            'rating.required' => 'Please provide a rating.',
            'rating.integer' => 'Rating must be a whole number.',
            'rating.min' => 'Minimum rating is 1.',
            'rating.max' => 'Maximum rating is 5.'
        ];
    }
}
