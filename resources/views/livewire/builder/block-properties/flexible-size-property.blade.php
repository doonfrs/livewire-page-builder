<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $property['label'] }}</span>
    </label>

    <!-- Mode Toggle -->
    <div class="tabs tabs-boxed mb-3">
        <button wire:click="$set('mode', 'class')" class="tab {{ $mode === 'class' ? 'tab-active' : '' }}" type="button">
            <x-heroicon-o-squares-2x2 class="w-4 h-4 me-2" />
            Classes
        </button>
        <button wire:click="$set('mode', 'custom')" class="tab {{ $mode === 'custom' ? 'tab-active' : '' }}"
            type="button">
            <x-heroicon-o-pencil-square class="w-4 h-4 me-2" />
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
