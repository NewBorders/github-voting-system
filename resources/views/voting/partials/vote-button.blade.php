<div class="flex flex-col items-center vote-button">
    @if($hasVoted ?? false)
        <button hx-delete="{{ route('voting.unvote', $feature->id) }}"
                hx-target="#vote-{{ $feature->id }}"
                hx-swap="innerHTML"
                @htmx:after-swap="window.sortFeatures()"
                class="flex flex-col items-center justify-center w-16 h-16 rounded-lg border-2 btn-primary transition-colors">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm font-bold">{{ $feature->vote_count }}</span>
        </button>
    @else
        <button hx-post="{{ route('voting.vote', $feature->id) }}"
                hx-target="#vote-{{ $feature->id }}"
                hx-swap="innerHTML"
                @htmx:after-swap="window.sortFeatures()"
                class="flex flex-col items-center justify-center w-16 h-16 rounded-lg border-2 border-dark-border bg-dark-primary text-gray-300 hover:border-accent hover:text-accent-light transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            <span class="text-sm font-bold">{{ $feature->vote_count }}</span>
        </button>
    @endif
</div>
