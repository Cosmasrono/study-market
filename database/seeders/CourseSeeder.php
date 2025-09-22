<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing courses instead of truncating
        Course::where('is_active', true)->delete();

        // ICT Courses
        $ictCourses = [
            [
                'title' => 'Web Development Bootcamp',
                'description' => 'Comprehensive full-stack web development course covering HTML, CSS, JavaScript, React, Node.js, and database management.',
                'price' => 299.99,
                'difficulty_level' => 'Intermediate',
                'duration_hours' => 120,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Cybersecurity Fundamentals',
                'description' => 'Learn essential cybersecurity principles, network security, ethical hacking, and defense strategies against modern cyber threats.',
                'price' => 249.99,
                'difficulty_level' => 'Beginner',
                'duration_hours' => 80,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Cloud Computing Certification',
                'description' => 'Master cloud technologies with AWS, Azure, and Google Cloud. Learn cloud architecture, deployment, and management.',
                'price' => 399.99,
                'difficulty_level' => 'Advanced',
                'duration_hours' => 160,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Data Science with Python',
                'description' => 'Comprehensive course in data analysis, machine learning, and AI using Python, pandas, numpy, and scikit-learn.',
                'price' => 349.99,
                'difficulty_level' => 'Intermediate',
                'duration_hours' => 100,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // Business Courses
        $businessCourses = [
            [
                'title' => 'Digital Marketing Mastery',
                'description' => 'Complete digital marketing course covering SEO, social media marketing, content strategy, and analytics.',
                'price' => 279.99,
                'difficulty_level' => 'Intermediate',
                'duration_hours' => 90,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Business Financial Management',
                'description' => 'Learn financial planning, budgeting, investment strategies, and financial analysis for business professionals.',
                'price' => 199.99,
                'difficulty_level' => 'Beginner',
                'duration_hours' => 60,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Entrepreneurship and Startup Strategies',
                'description' => 'Comprehensive course on building and scaling a successful startup, covering business planning, funding, and growth strategies.',
                'price' => 329.99,
                'difficulty_level' => 'Advanced',
                'duration_hours' => 110,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'title' => 'Project Management Professional (PMP) Prep',
                'description' => 'Prepare for PMP certification with comprehensive project management techniques, tools, and best practices.',
                'price' => 399.99,
                'difficulty_level' => 'Advanced',
                'duration_hours' => 140,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        // Insert courses using Eloquent model to respect relationships
        foreach (array_merge($ictCourses, $businessCourses) as $courseData) {
            Course::create($courseData);
        }
    }
}
