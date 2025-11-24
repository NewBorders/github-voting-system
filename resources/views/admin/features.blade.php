@extends('layouts.app')

@section('title', 'Manage Features - ' . $project->name)

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.projects.edit', $project) }}" class="text-accent-light hover:text-accent flex items-center mb-4">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to project
    </a>
    
    <h1 class="text-3xl font-bold text-white">Manage Features</h1>
    <p class="text-gray-300 mt-2">{{ $project->name }}</p>
</div>

<div class="bg-[#2a2270] rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-[#1d1858]">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Feature</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Votes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">GitHub</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-[#2a2270] divide-y divide-gray-200">
            @forelse($features as $feature)
                <tr>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-white">{{ $feature->title }}</div>
                        @if($feature->description)
                            <div class="text-sm text-gray-400">{{ Str::limit($feature->description, 100) }}</div>
                        @endif
                        <div class="text-xs text-gray-400 mt-1">{{ $feature->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.features.update', $feature) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="status" 
                                    onchange="this.form.submit()"
                                    class="text-sm rounded-full px-3 py-1 font-medium border-0
                                        @if($feature->status === 'submitted') bg-gray-800 text-gray-200
                                        @elseif($feature->status === 'accepted') bg-green-900 text-green-200
                                        @elseif($feature->status === 'planned') bg-blue-900 text-blue-200
                                        @elseif($feature->status === 'in_progress') bg-yellow-900 text-yellow-200
                                        @elseif($feature->status === 'done') bg-purple-900 text-purple-200
                                        @else bg-red-100 text-red-800
                                        @endif">
                                @foreach(\App\Models\Feature::STATUSES as $status)
                                    <option value="{{ $status }}" {{ $feature->status === $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                        {{ $feature->vote_count }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($feature->github_issue_url)
                            <a href="{{ $feature->github_issue_url }}" 
                               target="_blank"
                               class="text-purple-400 hover:text-indigo-900">
                                #{{ $feature->github_issue_number }}
                            </a>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <form action="{{ route('admin.features.delete', $feature) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this feature?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-900">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                        No features yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($features->hasPages())
    <div class="mt-6">
        {{ $features->links() }}
    </div>
@endif
@endsection
