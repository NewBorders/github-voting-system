@extends('layouts.app')

@section('title', 'Edit ' . $project->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <a href="{{ route('admin.index') }}" class="text-accent-light hover:text-accent flex items-center mb-6">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to dashboard
    </a>
    
    <h1 class="text-3xl font-bold text-white mb-8">Edit Project: {{ $project->name }}</h1>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Project Settings -->
        <div class="lg:col-span-2">
            <form action="{{ route('admin.projects.update', $project) }}" method="POST" class="card rounded-lg shadow p-6 space-y-6">
                @csrf
                @method('PATCH')
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Project Name *</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           required
                           value="{{ old('name', $project->name) }}"
                           class="w-full px-4 py-2 rounded-md">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
                    <input type="text" 
                           value="{{ $project->slug }}"
                           disabled
                           class="w-full px-4 py-2 border border-purple-600 rounded-md bg-[#1d1858] text-gray-200 bg-[#1d1858] text-gray-400">
                    <p class="mt-1 text-sm text-gray-400">Slug cannot be changed</p>
                </div>
                
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Description</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3"
                              class="w-full px-4 py-2 rounded-md">{{ old('description', $project->description) }}</textarea>
                </div>
                
                <hr>
                
                <h3 class="text-lg font-bold text-white">GitHub Integration</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="github_owner" class="block text-sm font-medium text-gray-300 mb-1">GitHub Owner</label>
                        <input type="text" 
                               id="github_owner" 
                               name="github_owner" 
                               value="{{ old('github_owner', $project->github_owner) }}"
                               class="w-full px-4 py-2 rounded-md">
                    </div>
                    
                    <div>
                        <label for="github_repo" class="block text-sm font-medium text-gray-300 mb-1">Repository Name</label>
                        <input type="text" 
                               id="github_repo" 
                               name="github_repo" 
                               value="{{ old('github_repo', $project->github_repo) }}"
                               class="w-full px-4 py-2 rounded-md">
                    </div>
                </div>
                
                <div>
                    <label for="github_token" class="block text-sm font-medium text-gray-300 mb-1">GitHub Token (optional)</label>
                    <input type="password" 
                           id="github_token" 
                           name="github_token" 
                           value="{{ old('github_token', $project->github_token) }}"
                           placeholder="Optional - nur für private Repositories"
                           class="w-full px-4 py-2 rounded-md">
                    <p class="mt-1 text-sm text-gray-400">Nur erforderlich für private Repositories. Leer lassen um aktuellen Token zu behalten.</p>
                </div>
                
                <hr>
                
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="is_active" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $project->is_active) ? 'checked' : '' }}
                           class="h-4 w-4 text-purple-400 focus:ring-2 focus:ring-purple-500 border-purple-600 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-300">
                        Project is active
                    </label>
                </div>
                
                <button type="submit" 
                        class="w-full btn-primary px-6 py-3 rounded-md font-medium">
                    Update Project
                </button>
            </form>
        </div>
        
        <!-- Quick Actions -->
        <div class="space-y-6">
            @if($project->github_repo)
                <div class="card rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold text-white mb-4">GitHub Sync</h3>
                    <form action="{{ route('admin.projects.sync', $project) }}" method="POST">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-purple-600 text-white px-4 py-3 rounded-md hover:bg-purple-700 font-medium">
                            Sync Issues Now
                        </button>
                    </form>
                    <p class="mt-2 text-sm text-gray-400">
                        Fetch latest issues from GitHub
                    </p>
                </div>
            @endif
            
            <div class="card rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-white mb-4">Quick Links</h3>
                <div class="space-y-2">
                    <a href="{{ route('voting.show', $project->slug) }}" 
                       target="_blank"
                       class="block w-full text-center bg-gray-100 text-gray-300 px-4 py-2 rounded-md hover:bg-gray-200 font-medium">
                        View Public Page
                    </a>
                    <a href="{{ route('admin.features', $project) }}" 
                       class="block w-full text-center bg-gray-100 text-gray-300 px-4 py-2 rounded-md hover:bg-gray-200 font-medium">
                        Manage Features
                    </a>
                </div>
            </div>
            
            <div class="card rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-white mb-4">Stats</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-300">Total Features:</dt>
                        <dd class="font-medium">{{ $project->features->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-300">Total Votes:</dt>
                        <dd class="font-medium">{{ $project->features->sum('vote_count') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-300">Created:</dt>
                        <dd class="font-medium">{{ $project->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
            
            <div class="card rounded-lg shadow p-6 border-2 border-red-900">
                <h3 class="text-lg font-bold text-red-400 mb-4">⚠️ Danger Zone</h3>
                <p class="text-sm text-gray-400 mb-4">
                    Deleting this project will permanently remove all features and votes. This action cannot be undone.
                </p>
                <form action="{{ route('admin.projects.delete', $project) }}" 
                      method="POST" 
                      onsubmit="return confirm('⚠️ Are you absolutely sure?\n\nThis will delete:\n• Project: {{ $project->name }}\n• {{ $project->features->count() }} Features\n• {{ $project->features->sum('vote_count') }} Votes\n\nThis action CANNOT be undone!\n\nType OK in the next prompt to confirm.') && prompt('Type DELETE to confirm:') === 'DELETE'">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full bg-red-600 text-white px-4 py-3 rounded-md hover:bg-red-700 font-medium transition-colors">
                        🗑️ Delete Project
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
