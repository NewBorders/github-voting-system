@extends('layouts.app')

@section('title', 'Edit ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('admin.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center mb-6">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to dashboard
    </a>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Edit Project: {{ $project->name }}</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Project Settings -->
        <div class="lg:col-span-2">
            <form action="{{ route('admin.projects.update', $project) }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
                @csrf
                @method('PATCH')
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Project Name *</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required
                           value="{{ old('name', $project->name) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" 
                           value="{{ $project->slug }}"
                           disabled
                           class="w-full px-4 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                    <p class="mt-1 text-sm text-gray-500">Slug cannot be changed</p>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $project->description) }}</textarea>
                </div>
                
                <hr>
                
                <h3 class="text-lg font-bold text-gray-900">GitHub Integration</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="github_owner" class="block text-sm font-medium text-gray-700 mb-1">GitHub Owner</label>
                        <input type="text" 
                               id="github_owner" 
                               name="github_owner" 
                               value="{{ old('github_owner', $project->github_owner) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    
                    <div>
                        <label for="github_repo" class="block text-sm font-medium text-gray-700 mb-1">Repository Name</label>
                        <input type="text" 
                               id="github_repo" 
                               name="github_repo" 
                               value="{{ old('github_repo', $project->github_repo) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
                
                <div>
                    <label for="github_token" class="block text-sm font-medium text-gray-700 mb-1">GitHub Token</label>
                    <input type="password" 
                           id="github_token" 
                           name="github_token" 
                           value="{{ old('github_token', $project->github_token) }}"
                           placeholder="Leave empty to keep current token"
                           class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="auto_sync" 
                           name="auto_sync" 
                           value="1"
                           {{ old('auto_sync', $project->auto_sync) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="auto_sync" class="ml-2 block text-sm text-gray-700">
                        Auto-sync GitHub issues
                    </label>
                </div>
                
                <hr>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $project->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        Project is active
                    </label>
                </div>
                
                <button type="submit" 
                        class="w-full bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 font-medium">
                    Update Project
                </button>
            </form>
        </div>
        
        <!-- Quick Actions -->
        <div class="space-y-6">
            @if($project->github_repo)
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">GitHub Sync</h3>
                    <form action="{{ route('admin.projects.sync', $project) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 font-medium">
                            Sync Issues Now
                        </button>
                    </form>
                    <p class="mt-2 text-sm text-gray-500">
                        Fetch latest issues from GitHub
                    </p>
                </div>
            @endif
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Links</h3>
                <div class="space-y-2">
                    <a href="{{ route('voting.show', $project->slug) }}" 
                       target="_blank"
                       class="block w-full text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 font-medium">
                        View Public Page
                    </a>
                    <a href="{{ route('admin.features', $project) }}" 
                       class="block w-full text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 font-medium">
                        Manage Features
                    </a>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Stats</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Total Features:</dt>
                        <dd class="font-medium">{{ $project->features->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Total Votes:</dt>
                        <dd class="font-medium">{{ $project->features->sum('vote_count') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Created:</dt>
                        <dd class="font-medium">{{ $project->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
