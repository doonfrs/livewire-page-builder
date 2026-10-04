<!-- Theme Settings Modal (PageEditor::themeSettingsSchema(): the built-in preview picture plus config/page-builder.php 'theme_settings') -->
<template x-teleport="body">
    <div x-data="{ show: $wire.entangle('showThemeSettingsModal') }" x-show="show"
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
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-base-200 shrink-0">
                        <x-heroicon-o-cog-6-tooth class="h-5 w-5" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ __('Theme Settings') }}
                        @if ($currentTheme)
                            <span class="text-gray-400 dark:text-gray-500">- {{ $currentTheme->name }}</span>
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
                            <input type="{{ in_array($field['type'] ?? 'text', ['number', 'url'], true) ? $field['type'] : 'text' }}"
                                wire:model="themeSettingsForm.{{ $field['key'] }}"
                                id="theme_setting_{{ $loop->index }}" placeholder="{{ $field['placeholder'] ?? '' }}"
                                @if (($field['type'] ?? 'text') === 'url') dir="ltr" @endif
                                class="input input-sm mt-1 w-full">
                            @error('themeSettingsForm.' . $field['key'])
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-3 justify-end mt-6">
                    <button type="button" wire:click="closeThemeSettingsModal" class="btn btn-sm">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-sm btn-neutral">
                        {{ __('Save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
