<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing testimonials
        DB::table('testimonials')->truncate();

        // Sample testimonials
        $testimonials = [
            [
                'name' => 'John Doe',
                'content' => 'This platform has been incredibly helpful in my learning journey. The book selection is amazing!',
                'position' => 'Software Engineer',
                'company' => 'Tech Innovations Inc.',
                'rating' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jane Smith',
                'content' => 'I found exactly the books I needed for my research. The platform is user-friendly and efficient.',
                'position' => 'Research Scientist',
                'company' => 'Global Research Labs',
                'rating' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mike Johnson',
                'content' => 'Great collection of books and excellent customer service. Highly recommended!',
                'position' => 'Project Manager',
                'company' => 'Creative Solutions LLC',
                'rating' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Insert testimonials
        Testimonial::insert($testimonials);
    }
}
