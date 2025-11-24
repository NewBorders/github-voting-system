@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center mb-6">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to dashboard
    </a>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Create New Project</h1>
    
    <form action="{{ route('admin.projects.store') }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
        @csrf
        
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Project Name *</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   required
                   value="{{ old('name') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug (URL-friendly) *</label>
            <input type="text" 
                   id="slug" 
                   name="slug" 
                   required
                   pattern="[a-z0-9-]+"
                   value="{{ old('slug') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-sm text-gray-500">Only lowercase letters, numbers, and hyphens</p>
            @error('slug')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" 
                      name="description" 
                      rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
        </div>
        
        <hr>
        
        <h3 class="text-lg font-bold text-gray-900">GitHub Integration</h3>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="github_owner" class="block text-sm font-medium text-gray-700 mb-1">GitHub Owner</label>
                <input type="text" 
                       id="github_owner" 
                       name="github_owner" 
                       value="{{ old('github_owner') }}"
                       placeholder="octocat"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div>
                <label for="github_repo" class="block text-sm font-medium text-gray-700 mb-1">Repository Name</label>
                <input type="text" 
                       id="github_repo" 
                       name="github_repo" 
                       value="{{ old('github_repo') }}"
                       placeholder="hello-world"
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
        
        <div>
            <label for="github_token" class="block text-sm font-medium text-gray-700 mb-1">GitHub Token (optional)</label>
            <input type="password" 
                   id="github_token" 
                   name="github_token" 
                   value="{{ old('github_token') }}"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
            <p class="mt-1 text-sm text-gray-500">For private repositories or higher rate limits</p>
        </div>
        
        <div class="flex items-center">
            <input type="checkbox" 
                   id="auto_sync" 
                   name="auto_sync" 
                   value="1"
                   {{ old('auto_sync') ? 'checked' : '' }}
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="auto_sync" class="ml-2 block text-sm text-gray-700">
                Auto-sync GitHub issues (not implemented yet)
            </label>
        </div>
        
        <hr>
        
        <div class="flex items-center">
            <input type="checkbox" 
                   id="is_active" 
                   name="is_active" 
                   value="1"
                   checked
                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                Project is active
            </label>
        </div>
        
        <div class="flex gap-4">
            <button type="submit" 
                    class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700 font-medium">
                Create Project
            </button>
            <a href="{{ route('admin.index') }}" 
               class="flex-1 text-center bg-gray-100 text-gray-700 px-6 py-3 rounded-md hover:bg-gray-200 font-medium">
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
