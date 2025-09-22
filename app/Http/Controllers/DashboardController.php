<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $enrollments = $user->enrollments()->with('course')->get();
        $attempts = $user->attempts()->with('course')->get();
        $payments = $user->payments()->with(['course', 'book'])->get();

        return view('dashboard.index', [
            'user' => $user,
            'enrollments' => $enrollments,
            'attempts' => $attempts,
            'payments' => $payments
        ]);
    }

    public function orders()
    {
        $user = Auth::user();
        $payments = $user->payments()->with(['course', 'book'])->get();

        return view('dashboard.orders', [
            'payments' => $payments
        ]);
    }

    public function results()
    {
        $user = Auth::user();
        $attempts = $user->attempts()->with('course')->get();

        return view('dashboard.results', [
            'attempts' => $attempts
        ]);
    }
}
