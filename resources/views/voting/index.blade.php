@extends('layouts.app')

@section('title', 'Vote for Features')

@section('content')
<div class="text-center mb-12">
    <h1 class="text-4xl font-bold text-white mb-4">Vote for Features</h1>
    <p class="text-lg text-gray-300">Help us prioritize what to build next by voting on features</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($projects as $project)
        <a href="{{ route('voting.show', $project->slug) }}" 
           class="block card rounded-lg shadow hover:shadow-xl transition-shadow p-6">
            <h3 class="text-xl font-bold text-white mb-2">{{ $project->name }}</h3>
            
            @if($project->description)
                <p class="text-gray-300 mb-4 line-clamp-3">{{ $project->description }}</p>
            @endif
            
            <div class="flex items-center justify-between text-sm text-gray-400">
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    {{ $project->features_count }} features
                </span>
                
                @if($project->github_repo)
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                        </svg>
                        GitHub
                    </span>
                @endif
            </div>
        </a>
    @empty
        <div class="col-span-full text-center py-12">
            <p class="text-gray-400 text-lg">No projects available for voting yet.</p>
        </div>
    @endforelse
</div>
@endsection
