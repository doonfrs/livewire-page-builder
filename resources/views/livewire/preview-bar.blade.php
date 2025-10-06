<div>
    @if ($previewThemeId)
        @php
            $currentTheme = collect($themes)->firstWhere('id', $previewThemeId);
        @endphp

        <div class="fixed top-0 left-0 right-0 z-[9999] bg-purple-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between py-3 gap-4">
                    <div class="flex items-center gap-3">
                        <x-heroicon-o-eye class="w-5 h-5 flex-shrink-0" />
                        <span class="font-medium hidden sm:inline">
                            {{ __('Preview Mode') }}:
                        </span>

                        <!-- Theme Dropdown -->
                        <select wire:model.live="previewThemeId"
                            class="bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium rounded-md px-3 py-1.5 border-0 focus:ring-2 focus:ring-white/30 transition min-w-[200px]">
                            @foreach ($themes as $theme)
                                <option value="{{ $theme['id'] }}">{{ $theme['name'] }}</option>
                            @endforeach
                        </select>

                        @if ($currentTheme && $currentTheme['description'])
                            <span class="hidden lg:inline text-purple-200 text-sm">
                                ({{ $currentTheme['description'] }})
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ url('/page-builder/themes') }}"
                            class="inline-flex items-center px-3 py-1.5 bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium rounded-md transition">
                            <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" />
                            <span class="hidden sm:inline">{{ __('Back to Themes') }}</span>
                            <span class="sm:hidden">{{ __('Back') }}</span>
                        </a>
                        <button wire:click="cancelPreview"
                            class="inline-flex items-center px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-md transition">
                            <x-heroicon-o-x-mark class="w-4 h-4 mr-1" />
                            <span class="hidden sm:inline">{{ __('Exit Preview') }}</span>
                            <span class="sm:hidden">{{ __('Exit') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spacer to prevent content from being hidden under fixed bar -->
        <div class="h-[52px]"></div>
    @endif

    @script
    <script>
        $wire.on('refresh-page', () => {
            window.location.reload();
        });
    </script>
    @endscript
</div>
