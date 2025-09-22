@extends('layouts.admin')

@section('title', 'Book Management')
@section('page-title', 'Book Management')

@section('content')
<div class="bg-maroon-50 p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-maroon-800">Book Management</h2>
        <a href="{{ route('admin.books.create') }}" class="bg-maroon-600 text-white px-4 py-2 rounded hover:bg-maroon-700 transition-colors">
            Upload New Book
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Total Books</h4>
            <p class="text-2xl font-bold text-maroon-800">{{ $books->total() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Free Books</h4>
            <p class="text-2xl font-bold text-maroon-800">
                {{ $books->where('is_free', true)->count() }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Paid Books</h4>
            <p class="text-2xl font-bold text-maroon-800">
                {{ $books->where('is_free', false)->count() }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Available Books</h4>
            <p class="text-2xl font-bold text-maroon-800">
                {{ $books->where('is_available', true)->count() }}
            </p>
        </div>
    </div>

    <!-- Books Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-maroon-100 text-maroon-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Book File</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Created At</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Availability</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-maroon-200">
                @forelse($books as $book)
                    <tr class="hover:bg-maroon-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-maroon-700">{{ $book->id }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-maroon-700">{{ $book->title }}</td>
                        <td class="px-4 py-3 text-sm text-maroon-600">{{ Str::limit($book->description, 50) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($book->book_url)
                                <a href="{{ $book->book_url }}" target="_blank" class="text-maroon-600 hover:text-maroon-800">
                                    View File
                                </a>
                            @else
                                <span class="text-gray-500">No File</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-maroon-600">
                            {{ $book->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($book->is_available)
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">
                                    Available
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs">
                                    Unavailable
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.books.edit', $book->id) }}" 
                                   class="text-maroon-600 hover:text-maroon-800">
                                    Edit
                                </a>
                                <form action="{{ route('admin.books.delete', $book->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this book?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-800">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-3 text-center text-maroon-600">
                            No books found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $books->links() }}
    </div>
</div>
@endsection
