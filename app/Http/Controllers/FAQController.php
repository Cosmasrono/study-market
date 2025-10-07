<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FAQ; // Assuming you have an FAQ model

class FAQController extends Controller
{
    public function index()
    {
        // Fetch active FAQs
        $faqs = FAQ::active()->ordered()->get();

        return view('faq', compact('faqs'));
    }
}
