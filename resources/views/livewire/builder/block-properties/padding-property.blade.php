<div class="form-control w-full">
    <label class="label">
        <span class="label-text">{{ $property['label'] }}</span>
    </label>

    <!-- Control Modes -->
    <div class="tabs tabs-boxed mb-3">
        <button wire:click="$set('mode', 'unified')"
                class="tab {{ $mode === 'unified' ? 'tab-active' : '' }}"
                type="button">
            <x-heroicon-o-squares-plus class="w-4 h-4 me-2" />
            All
        </button>
        <button wire:click="$set('mode', 'individual')"
                class="tab {{ $mode === 'individual' ? 'tab-active' : '' }}"
                type="button">
            <x-heroicon-o-view-columns class="w-4 h-4 me-2" />
            Per Device
        </button>
    </div>

    @if ($mode === 'unified')
        <!-- Unified Mode - All Devices -->
        <div class="space-y-3">
            <div class="bg-base-200 rounded-lg p-4">
                <div class="text-xs font-medium text-base-content/60 mb-3 uppercase tracking-wider">
                    All Devices
                </div>

                <!-- Box Model Visual -->
                <div class="grid grid-cols-3 gap-3 max-w-80 mx-auto mb-4">
                    <!-- Top Row -->
                    <div></div>
                    <div class="text-center">
                        <select wire:model.live="unifiedClassValues.top"
                                class="select select-bordered w-full text-sm">
                            <option value="">-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($unifiedClassValues['top'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="unifiedCustomValues.top"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Top</div>
                    </div>
                    <div></div>

                    <!-- Middle Row -->
                    <div class="text-center">
                        <select wire:model.live="unifiedClassValues.left"
                                class="select select-bordered w-full text-sm">
                            <option value="">-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($unifiedClassValues['left'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="unifiedCustomValues.left"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Left</div>
                    </div>

                    <div class="bg-primary/10 border-2 border-dashed border-primary/30 rounded flex items-center justify-center text-xs text-primary font-medium">
                        Content
                    </div>

                    <div class="text-center">
                        <select wire:model.live="unifiedClassValues.right"
                                class="select select-bordered w-full text-sm">
                            <option value="">-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($unifiedClassValues['right'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="unifiedCustomValues.right"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Right</div>
                    </div>

                    <!-- Bottom Row -->
                    <div></div>
                    <div class="text-center">
                        <select wire:model.live="unifiedClassValues.bottom"
                                class="select select-bordered w-full text-sm">
                            <option value="">-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}">{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($unifiedClassValues['bottom'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="unifiedCustomValues.bottom"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Bottom</div>
                    </div>
                    <div></div>
                </div>

                @if (in_array('custom', [$unifiedClassValues['top'] ?? '', $unifiedClassValues['right'] ?? '', $unifiedClassValues['bottom'] ?? '', $unifiedClassValues['left'] ?? '']))
                    <div class="text-center text-xs text-base-content/50">
                        Values in {{ $property['unit'] }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Individual Mode -->
        <div class="space-y-3">
            <!-- Device Tabs -->
            <div class="tabs tabs-boxed">
                <button wire:click="$set('activeDevice', 'mobile')"
                        class="tab {{ $activeDevice === 'mobile' ? 'tab-active' : '' }}"
                        type="button">
                    <x-heroicon-o-device-phone-mobile class="w-4 h-4 me-2" />
                    Mobile
                </button>
                <button wire:click="$set('activeDevice', 'tablet')"
                        class="tab {{ $activeDevice === 'tablet' ? 'tab-active' : '' }}"
                        type="button">
                    <x-heroicon-o-device-tablet class="w-4 h-4 me-2" />
                    Tablet
                </button>
                <button wire:click="$set('activeDevice', 'desktop')"
                        class="tab {{ $activeDevice === 'desktop' ? 'tab-active' : '' }}"
                        type="button">
                    <x-heroicon-o-computer-desktop class="w-4 h-4 me-2" />
                    Desktop
                </button>
            </div>

            <!-- Individual Controls -->
            <div class="bg-base-200 rounded-lg p-4">
                <div class="text-xs font-medium text-base-content/60 mb-3 uppercase tracking-wider">
                    {{ ucfirst($activeDevice) }} Padding
                </div>

                <!-- Box Model Visual -->
                <div class="grid grid-cols-3 gap-3 max-w-80 mx-auto mb-4">
                    <!-- Top Row -->
                    <div></div>
                    <div class="text-center">
                        <select wire:change="updateIndividualValue('{{ $activeDevice }}', 'top', $event.target.value)"
                                class="select select-bordered w-full text-sm">
                            <option value="" {{ ($individualClassValues[$activeDevice]['top'] ?? '') === '' ? 'selected' : '' }}>-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}" {{ ($individualClassValues[$activeDevice]['top'] ?? '') === $class ? 'selected' : '' }}>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($individualClassValues[$activeDevice]['top'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="individualCustomValues.{{ $activeDevice }}.top"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Top</div>
                    </div>
                    <div></div>

                    <!-- Middle Row -->
                    <div class="text-center">
                        <select wire:change="updateIndividualValue('{{ $activeDevice }}', 'left', $event.target.value)"
                                class="select select-bordered w-full text-sm">
                            <option value="" {{ ($individualClassValues[$activeDevice]['left'] ?? '') === '' ? 'selected' : '' }}>-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}" {{ ($individualClassValues[$activeDevice]['left'] ?? '') === $class ? 'selected' : '' }}>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($individualClassValues[$activeDevice]['left'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="individualCustomValues.{{ $activeDevice }}.left"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Left</div>
                    </div>

                    <div class="bg-primary/10 border-2 border-dashed border-primary/30 rounded flex items-center justify-center text-xs text-primary font-medium">
                        Content
                    </div>

                    <div class="text-center">
                        <select wire:change="updateIndividualValue('{{ $activeDevice }}', 'right', $event.target.value)"
                                class="select select-bordered w-full text-sm">
                            <option value="" {{ ($individualClassValues[$activeDevice]['right'] ?? '') === '' ? 'selected' : '' }}>-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}" {{ ($individualClassValues[$activeDevice]['right'] ?? '') === $class ? 'selected' : '' }}>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($individualClassValues[$activeDevice]['right'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="individualCustomValues.{{ $activeDevice }}.right"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Right</div>
                    </div>

                    <!-- Bottom Row -->
                    <div></div>
                    <div class="text-center">
                        <select wire:change="updateIndividualValue('{{ $activeDevice }}', 'bottom', $event.target.value)"
                                class="select select-bordered w-full text-sm">
                            <option value="" {{ ($individualClassValues[$activeDevice]['bottom'] ?? '') === '' ? 'selected' : '' }}>-</option>
                            @foreach ($property['paddingClasses'] as $class => $label)
                                @if ($class !== '')
                                    <option value="{{ $class }}" {{ ($individualClassValues[$activeDevice]['bottom'] ?? '') === $class ? 'selected' : '' }}>{{ $label }}</option>
                                @endif
                            @endforeach
                        </select>
                        @if (($individualClassValues[$activeDevice]['bottom'] ?? '') === 'custom')
                            <input type="number"
                                   wire:model.live="individualCustomValues.{{ $activeDevice }}.bottom"
                                   class="input input-bordered w-full text-center mt-1 text-sm"
                                   placeholder="0"
                                   min="0">
                        @endif
                        <div class="text-xs text-base-content/50 mt-1">Bottom</div>
                    </div>
                    <div></div>
                </div>

                @if (in_array('custom', [$individualClassValues[$activeDevice]['top'] ?? '', $individualClassValues[$activeDevice]['right'] ?? '', $individualClassValues[$activeDevice]['bottom'] ?? '', $individualClassValues[$activeDevice]['left'] ?? '']))
                    <div class="text-center text-xs text-base-content/50">
                        Values in {{ $property['unit'] }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>