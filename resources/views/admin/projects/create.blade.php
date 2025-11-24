@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.index') }}" class="text-accent-light hover:text-accent flex items-center mb-6">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to dashboard
    </a>
    
    <h1 class="text-3xl font-bold text-white mb-8">Create New Project</h1>
    
    <form action="{{ route('admin.projects.store') }}" method="POST" class="bg-[#2a2270] rounded-lg shadow p-6 space-y-6 border border-purple-700">
        @csrf
        
        <div>
            <label for="name" class="block text-sm font-medium text-gray-200 mb-1">Project Name *</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   required
                   value="{{ old('name') }}"
                   class="w-full px-4 py-2 rounded-md">
            @error('name')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <label for="slug" class="block text-sm font-medium text-gray-200 mb-1">Slug (URL-friendly) *</label>
            <input type="text" 
                   id="slug" 
                   name="slug" 
                   required
                   pattern="[a-z0-9-]+"
                   value="{{ old('slug') }}"
                   class="w-full px-4 py-2 rounded-md">
            <p class="mt-1 text-sm text-gray-400">Only lowercase letters, numbers, and hyphens</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-200 mb-1">Description</label>
            <textarea id="description" 
                      name="description" 
                      rows="3"
                      class="w-full px-4 py-2 rounded-md">{{ old('description') }}</textarea>
        </div>
        
        <hr class="border-purple-700">
        
        <h3 class="text-lg font-bold text-white">GitHub Integration (Required)</h3>
        <p class="text-sm text-gray-300 mb-4">Features are only synced from GitHub Issues</p>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="github_owner" class="block text-sm font-medium text-gray-200 mb-1">GitHub Owner *</label>
                <input type="text" 
                       id="github_owner" 
                       name="github_owner" 
                       required
                       value="{{ old('github_owner') }}"
                       placeholder="e.g., facebook"
                       class="w-full px-4 py-2 rounded-md">
            </div>
            
            <div>
                <label for="github_repo" class="block text-sm font-medium text-gray-200 mb-1">Repository Name *</label>
                <input type="text" 
                       id="github_repo" 
                       name="github_repo" 
                       required
                       value="{{ old('github_repo') }}"
                       placeholder="e.g., react"
                       class="w-full px-4 py-2 rounded-md">
            </div>
        </div>
        
        <div>
            <label for="github_token" class="block text-sm font-medium text-gray-200 mb-1">GitHub Token (optional)</label>
            <input type="password" 
                   id="github_token" 
                   name="github_token" 
                   value="{{ old('github_token') }}"
                   placeholder="Optional - nur für private Repositories"
                   class="w-full px-4 py-2 rounded-md">
            <p class="mt-1 text-sm text-gray-400">Nur erforderlich für private Repositories. Public Repos funktionieren ohne Token.</p>
        </div>
        
        <hr class="border-purple-700">
        
        <div class="flex items-center">
            <input type="checkbox" 
                   id="is_active" 
                   name="is_active" 
                   value="1"
                   checked
                   class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-purple-600 rounded bg-[#1d1858]">
            <label for="is_active" class="ml-2 block text-sm text-gray-300">
                Project is active
            </label>
        </div>
        
        <div class="flex gap-4">
            <button type="submit" 
                    class="flex-1 btn-primary px-6 py-3 rounded-md font-medium">
                Create Project
            </button>
            <a href="{{ route('admin.index') }}" 
               class="flex-1 text-center btn-secondary px-6 py-3 rounded-md font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
    // Auto-generate slug from name
    document.getElementById('name').addEventListener('input', function(e) {
        const slug = e.target.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    });
</script>
@endsection
