<!-- Theme Settings Modal (host-defined schema; see config/page-builder.php 'theme_settings') -->
<template x-teleport="body">
    <div x-data="{ show: @entangle('showThemeSettingsModal') }" x-show="show"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 p-4" style="display: none;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="if (show) $wire.closeThemeSettingsModal()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-6 relative max-h-[90vh] overflow-y-auto"
            @click.outside="$wire.closeThemeSettingsModal()"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <form wire:submit="saveThemeSettings">
                <div class="flex items-center gap-3 mb-1">
                    <div
                        class="flex items-center justify-center h-10 w-10 rounded-full bg-pink-100 dark:bg-pink-900/30 shrink-0">
                        <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-pink-600 dark:text-pink-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Theme Settings') }}
                        @if ($currentTheme)
                            <span class="text-gray-400 dark:text-gray-500">— {{ $currentTheme->name }}</span>
                        @endif
                    </h3>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    {{ __('Leave a field empty to use the store default shown as a placeholder.') }}
                </p>

                <div class="space-y-4">
                    @php($currentGroup = null)
                    @foreach ($this->themeSettingsSchema() as $field)
                        @if (($field['group'] ?? null) !== $currentGroup)
                            @php($currentGroup = $field['group'] ?? null)
                            @if ($currentGroup)
                                <h4
                                    class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 pt-1">
                                    {{ __($currentGroup) }}
                                </h4>
                            @endif
                        @endif
                        <div>
                            <label for="theme_setting_{{ $loop->index }}"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __($field['label'] ?? $field['key']) }}</label>
                            <input type="{{ ($field['type'] ?? 'text') === 'number' ? 'number' : 'text' }}"
                                wire:model="themeSettingsForm.{{ $field['key'] }}"
                                id="theme_setting_{{ $loop->index }}" placeholder="{{ $field['placeholder'] ?? '' }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm">
                            @error('themeSettingsForm.' . $field['key'])
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" wire:click="closeThemeSettingsModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-500 transition-all duration-150">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 focus:ring-2 focus:ring-pink-200 rounded-md transition-all duration-150">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
