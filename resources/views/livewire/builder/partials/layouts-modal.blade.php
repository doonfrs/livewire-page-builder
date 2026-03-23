<!-- Layouts Modal -->
<div x-data="{ confirmLayout: null }"
    x-show="$wire.showLayoutsModal"
    class="fixed inset-0 z-52 flex items-center justify-center bg-black/40"
    style="display: none;">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-8 relative"
        @click.outside="$wire.set('showLayoutsModal', false); confirmLayout = null">
        <button
            @click="$wire.set('showLayoutsModal', false); confirmLayout = null"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <x-heroicon-o-rectangle-stack class="w-6 h-6 text-indigo-500" />
            {{ __('Layouts') }}
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">
            {{ __('Select a layout to replace the current page content.') }}
        </p>

        <!-- Confirmation banner -->
        <div x-show="confirmLayout !== null"
            x-transition
            class="mb-4 flex items-start gap-3 bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg px-4 py-3 text-sm text-yellow-800 dark:text-yellow-200">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0 mt-0.5 text-yellow-500" />
            <div class="flex-1">
                {{ __('This will replace all current page content. Are you sure?') }}
                <div class="flex gap-2 mt-2">
                    <button
                        @click="$wire.applyLayout(confirmLayout); confirmLayout = null"
                        class="px-3 py-1 rounded-md bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium transition-colors">
                        {{ __('Yes, apply layout') }}
                    </button>
                    <button
                        @click="confirmLayout = null"
                        class="px-3 py-1 rounded-md bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-xs font-medium hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Layout list -->
        <div class="overflow-y-auto max-h-[50vh] pr-1 space-y-2">
            @forelse ($availableLayouts as $layout)
                <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex items-start gap-3 flex-1 min-w-0">
                        <x-heroicon-o-rectangle-stack class="w-5 h-5 text-indigo-400 shrink-0 mt-0.5" />
                        <div class="min-w-0">
                            <div class="font-medium text-gray-900 dark:text-gray-100 truncate">{{ $layout['name'] }}</div>
                            @if ($layout['description'])
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $layout['description'] }}</div>
                            @endif
                        </div>
                    </div>
                    <button
                        @click="confirmLayout = @js($layout['path'])"
                        class="ml-3 shrink-0 flex items-center gap-1 px-3 py-1.5 rounded-md bg-indigo-500 hover:bg-indigo-600 text-white text-xs font-medium transition-colors">
                        <x-heroicon-o-arrow-down-on-square class="w-4 h-4" />
                        {{ __('Apply') }}
                    </button>
                </div>
            @empty
                <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                    <x-heroicon-o-rectangle-stack class="w-10 h-10 mx-auto mb-2 opacity-40" />
                    <p>{{ __('No layouts available.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
