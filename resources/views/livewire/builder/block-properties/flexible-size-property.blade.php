<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $property['label'] }}</span>
    </label>

    <!-- Mode Toggle -->
    <div class="tabs tabs-boxed mb-3">
        <button wire:click="$set('mode', 'class')" class="tab {{ $mode === 'class' ? 'tab-active' : '' }}" type="button">
            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
            Classes
        </button>
        <button wire:click="$set('mode', 'custom')" class="tab {{ $mode === 'custom' ? 'tab-active' : '' }}"
            type="button">
            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
            Custom
        </button>
    </div>

    @if ($mode === 'class')
        <!-- Class Selection -->
        <select wire:model.live="selectedClass" class="select select-bordered w-full">
            <option value="">Select {{ strtolower($property['label']) }}</option>
            @foreach ($property['classes'] as $class => $label)
                <option value="{{ $class }}">{{ $label }}</option>
            @endforeach
        </select>
    @else
        <!-- Custom Value Input -->
        <div class="flex items-center gap-2">
            <input type="number" wire:model.live="customValue" class="input input-bordered input-sm flex-1"
                placeholder="100" min="0" step="1">
            <span class="text-sm font-mono text-base-content/70">{{ $property['unit'] }}</span>
        </div>
    @endif
</div>
