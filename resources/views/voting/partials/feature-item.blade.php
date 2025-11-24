@php
    $clientId = request()->cookie('voting_client_id', 'temp-' . uniqid());
    $hasVoted = $feature->hasVoteFromClient($clientId);
@endphp

<div class="card rounded-lg shadow p-6 hover:shadow-xl transition-shadow">
    <div class="flex items-start gap-4">
        <!-- Vote Button -->
        <div id="vote-{{ $feature->id }}">
            @include('voting.partials.vote-button', ['feature' => $feature, 'hasVoted' => $hasVoted])
        </div>
        
        <!-- Feature Content -->
        <div class="flex-1">
            <div class="flex items-start justify-between gap-4 mb-2">
                <h3 class="text-lg font-bold text-white">{{ $feature->title }}</h3>
                @if($feature->github_issue_url)
                    <a href="{{ $feature->github_issue_url }}" 
                       target="_blank"
                       class="text-accent-light hover:text-accent flex items-center gap-1 shrink-0">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm">#{{ $feature->github_issue_number }}</span>
                    </a>
                @endif
            </div>
            
            @if($feature->description)
                <div x-data="{ expanded: false }" class="mb-3">
                    <p class="text-gray-300" 
                       x-show="!expanded"
                       x-transition>
                        {{ Str::limit($feature->description, 200) }}
                    </p>
                    <p class="text-gray-300 whitespace-pre-wrap" 
                       x-show="expanded"
                       x-transition
                       x-cloak>{{ $feature->description }}</p>
                    
                    @if(strlen($feature->description) > 200)
                        <button @click="expanded = !expanded" 
                                class="text-accent-light hover:text-accent text-sm mt-1 flex items-center">
                            <span x-show="!expanded">Show more</span>
                            <span x-show="expanded" x-cloak>Show less</span>
                            <svg class="w-4 h-4 ml-1" 
                                 :class="expanded ? 'rotate-180' : ''"
                                 fill="none" 
                                 stroke="currentColor" 
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif
            
            <div class="flex items-center gap-4 text-sm text-gray-400">
                <span class="badge badge-{{ $feature->status }}">
                    {{ ucfirst(str_replace('_', ' ', $feature->status)) }}
                </span>
                
                <span>{{ $feature->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>
</div>
