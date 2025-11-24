@extends('layouts.app')

@section('title', $project->name . ' - Vote')

@section('content')
<div class="mb-8">
    <a href="{{ route('voting.index') }}" class="text-indigo-600 hover:text-indigo-800 flex items-center mb-4">
        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to projects
    </a>
    
    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $project->name }}</h1>
    
    @if($project->description)
        <p class="text-gray-600 mb-4">{{ $project->description }}</p>
    @endif
    
    @if($project->github_repo)
        <a href="https://github.com/{{ $project->github_owner }}/{{ $project->github_repo }}" 
           target="_blank"
           class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
            </svg>
            {{ $project->github_owner }}/{{ $project->github_repo }}
        </a>
    @endif
</div>

<!-- Submit Feature Form -->
<div class="bg-white rounded-lg shadow p-6 mb-8">
    <h2 class="text-xl font-bold text-gray-900 mb-4">Suggest a Feature</h2>
    <form hx-post="{{ route('voting.submit', $project->slug) }}" 
          hx-target="#features-list" 
          hx-swap="afterbegin"
          class="space-y-4">
        @csrf
        <div>
            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Feature Title *</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   required
                   minlength="5"
                   maxlength="200"
                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="e.g., Add dark mode support">
        </div>
        
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
            <textarea id="description" 
                      name="description" 
                      rows="3"
                      maxlength="5000"
                      class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Provide more details about your feature idea..."></textarea>
        </div>
        
        <button type="submit" 
                class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium transition-colors">
            Submit Feature
        </button>
    </form>
</div>

<!-- Features List -->
<div class="space-y-4" id="features-list">
    @forelse($features as $feature)
        @include('voting.partials.feature-item', ['feature' => $feature, 'project' => $project])
    @empty
        <div class="text-center py-12 bg-white rounded-lg shadow">
            <p class="text-gray-500">No features yet. Be the first to suggest one!</p>
        </div>
    @endforelse
</div>

@if($features->hasPages())
    <div class="mt-6">
        {{ $features->links() }}
    </div>
@endif
@endsection
