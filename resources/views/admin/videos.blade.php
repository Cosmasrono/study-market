@extends('layouts.admin')

@section('title', 'Video Management')
@section('page-title', 'Video Management')

@section('content')
<div class="bg-maroon-50 p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-maroon-800">Video Management</h2>
        <a href="{{ route('admin.videos.create') }}" class="bg-maroon-600 text-white px-4 py-2 rounded hover:bg-maroon-700 transition-colors">
            Upload New Video
        </a>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Total Videos</h4>
            <p class="text-2xl font-bold text-maroon-800">{{ $videos->total() }}</p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Free Videos</h4>
            <p class="text-2xl font-bold text-maroon-800">
                {{ $videos->where('is_free', true)->count() }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Paid Videos</h4>
            <p class="text-2xl font-bold text-maroon-800">
                {{ $videos->where('is_free', false)->count() }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-lg text-center border-l-4 border-maroon-500">
            <h4 class="text-sm text-maroon-600 mb-2">Active Videos</h4>
            <p class="text-2xl font-bold text-maroon-800">
                {{ $videos->where('is_active', true)->count() }}
            </p>
        </div>
    </div>

    <!-- Videos Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="w-full">
            <thead class="bg-maroon-100 text-maroon-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Video</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Created At</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-maroon-200">
                @forelse($videos as $video)
                    <tr class="hover:bg-maroon-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-maroon-700">{{ $video->id }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-maroon-700">{{ $video->title }}</td>
                        <td class="px-4 py-3 text-sm text-maroon-600">{{ Str::limit($video->description, 50) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($video->video_url)
                                <a href="{{ $video->video_url }}" target="_blank" class="text-maroon-600 hover:text-maroon-800">
                                    View Video
                                </a>
                            @else
                                <span class="text-gray-500">No Video</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-maroon-600">
                            {{ $video->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($video->is_active)
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs">
                                    Active
                                </span>
                            @else
                                <span class="bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs">
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.videos.edit', $video->id) }}" 
                                   class="text-maroon-600 hover:text-maroon-800">
                                    Edit
                                </a>
                                <form action="{{ route('admin.videos.delete', $video->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this video?');">
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
                            No videos found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $videos->links() }}
    </div>
</div>
@endsection