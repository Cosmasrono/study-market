@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
<div class="container mx-auto px-4">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Admin Login</h1>
            <p class="text-gray-600 mt-2">Access Inzoberi School of Professional Admin Dashboard</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email Address *</label>
                <input type="email" 
                       name="email" 
                       id="email" 
                       value="{{ old('email') }}"
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                @error('email')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-bold mb-2">Password *</label>
                <input type="password" 
                       name="password" 
                       id="password" 
                       required 
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                @error('password')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="mb-4 flex items-center">
                <input type="checkbox" 
                       name="remember" 
                       id="remember" 
                       class="mr-2">
                <label for="remember" class="text-gray-700">Remember Me</label>
            </div>
            
            <div>
                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-md hover:bg-blue-700 transition duration-300">
                    Login
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <p class="text-gray-600">
                <a href="{{ route('login') }}" class="text-blue-600 hover:underline">
                    Back to User Login
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
