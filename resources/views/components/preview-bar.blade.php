@php
    $previewThemeId = session('page_builder_preview_theme_id');
    $previewTheme = null;

    if ($previewThemeId) {
        $previewTheme = \Trinavo\LivewirePageBuilder\Models\Theme::find($previewThemeId);
    }
@endphp

@if ($previewTheme)
    <div class="fixed top-0 left-0 right-0 z-[9999] bg-purple-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                    <x-heroicon-o-eye class="w-5 h-5" />
                    <span class="font-medium">
                        {{ __('Preview Mode') }}: <strong>{{ $previewTheme->name }}</strong>
                    </span>
                    @if ($previewTheme->description)
                        <span class="hidden sm:inline text-purple-200 text-sm">
                            ({{ $previewTheme->description }})
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ url('/page-builder/themes') }}"
                        class="inline-flex items-center px-3 py-1.5 bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium rounded-md transition">
                        <x-heroicon-o-arrow-left class="w-4 h-4 mr-1" />
                        {{ __('Back to Themes') }}
                    </a>
                    <a href="{{ url('/page-builder/preview/cancel') }}"
                        class="inline-flex items-center px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-md transition">
                        <x-heroicon-o-x-mark class="w-4 h-4 mr-1" />
                        {{ __('Exit Preview') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Spacer to prevent content from being hidden under fixed bar -->
    <div class="h-[52px]"></div>
@endif
