<div>
    @if ($previewThemeId)
        @php
            $currentTheme = collect($themes)->firstWhere('id', $previewThemeId);
        @endphp

        <div class="fixed top-0 left-0 right-0 z-[9999] bg-purple-600 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-center justify-between py-2 sm:py-3 gap-2 sm:gap-4">
                    <div class="flex items-center gap-2 sm:gap-3 min-w-0 flex-1 sm:flex-initial">
                        <x-heroicon-o-eye class="w-5 h-5 shrink-0" />
                        <span class="font-medium hidden sm:inline">
                            {{ __('Preview Mode') }}:
                        </span>

                        <!-- Theme Dropdown -->
                        <select wire:model.live="previewThemeId"
                            class="bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium rounded-md px-2 sm:px-3 py-1.5 border-0 focus:ring-2 focus:ring-white/30 transition min-w-0 flex-1 sm:flex-initial sm:min-w-[200px]">
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

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ url('/page-builder/themes') }}"
                            class="inline-flex items-center p-1.5 sm:px-3 sm:py-1.5 bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium rounded-md transition"
                            title="{{ __('Back to Themes') }}">
                            <x-heroicon-o-arrow-left class="w-4 h-4 sm:me-1 rtl:rotate-180" />
                            <span class="hidden sm:inline">{{ __('Back to Themes') }}</span>
                        </a>
                        <button wire:click="cancelPreview"
                            class="inline-flex items-center p-1.5 sm:px-3 sm:py-1.5 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-md transition"
                            title="{{ __('Exit Preview') }}">
                            <x-heroicon-o-x-mark class="w-4 h-4 sm:me-1" />
                            <span class="hidden sm:inline">{{ __('Exit Preview') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spacer to prevent content from being hidden under fixed bar -->
        <div class="h-[48px] sm:h-[52px]"></div>
    @endif

    @script
    <script>
        $wire.on('refresh-page', () => {
            window.location.reload();
        });
    </script>
    @endscript
</div>
