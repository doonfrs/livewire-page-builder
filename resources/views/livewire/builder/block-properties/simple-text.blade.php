<div>
    <!-- Language Tabs (if multilingual is enabled) -->
    @if ($multilingual && count($contentLocales) > 1)
        <div class="mb-2 border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium">
                @foreach ($contentLocales as $code => $name)
                    <li wire:key="locale-tab-{{ $code }}">
                        <button type="button"
                            class="inline-block p-2 {{ $currentLocale === $code ? 'border-b-2 border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
                            wire:click="switchLocale('{{ $code }}')">
                            {{ $name }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-center mb-1">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
            @if (isset($propertyLabel))
                <x-heroicon-o-bars-3-bottom-left class="inline w-4 h-4 mr-1 align-text-bottom text-gray-400 dark:text-gray-500" />
                {{ $propertyLabel }}
                @if ($multilingual && count($contentLocales) > 1)
                    <span
                        class="ml-1 text-xs text-gray-500 dark:text-gray-400">({{ $contentLocales[$currentLocale] ?? $currentLocale }})</span>
                @endif
            @endif
        </label>

        <!-- Variables Button -->
        <button
            type="button"
            x-data="{}"
            @click="$dispatch('open-simple-text-variables-modal-{{ $propertyName }}')"
            class="inline-flex items-center text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-md transition-colors duration-200">
            <x-heroicon-o-variable class="w-4 h-4 mr-1" />
            {{ __('Variables') }}
        </button>
    </div>

    <!-- Variables Modal -->
    <div
        x-data="{
            open: false,
            variables: @js($variables),
            searchTerm: '',
            filteredVariables() {
                if (!this.searchTerm.trim()) {
                    return this.variables;
                }
                const term = this.searchTerm.toLowerCase();
                return this.variables.filter(v =>
                    v.name.toLowerCase().includes(term) ||
                    v.value.toString().toLowerCase().includes(term)
                );
            },
            insertVariable(variable) {
                const variableTag = '{' + variable.name + '}';
                const textarea = document.getElementById('simple-text-editor-{{ $propertyName }}');

                if (textarea) {
                    const start = textarea.selectionStart;
                    const end = textarea.selectionEnd;
                    const text = textarea.value;

                    // Insert at cursor position
                    textarea.value = text.substring(0, start) + variableTag + text.substring(end);

                    // Update cursor position
                    const newCursorPos = start + variableTag.length;
                    textarea.setSelectionRange(newCursorPos, newCursorPos);
                    textarea.focus();

                    // Trigger input event to update Livewire
                    textarea.dispatchEvent(new Event('input', { bubbles: true }));
                }

                this.open = false;
            }
        }"
        @open-simple-text-variables-modal-{{ $propertyName }}.window="open = true"
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center"
        style="display: none;">

        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>

        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[80vh] flex flex-col">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    {{ __('Available Variables') }}
                </h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                    <x-heroicon-o-x-mark class="w-5 h-5" />
                </button>
            </div>

            <!-- Search -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="relative">
                    <input
                        x-model="searchTerm"
                        type="text"
                        class="block w-full ps-10 p-2.5 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-pink-500 focus:border-pink-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white"
                        placeholder="{{ __('Search variables...') }}"
                    >
                    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                    </div>
                </div>
            </div>

            <!-- Variables List -->
            <div class="p-4 overflow-y-auto grow">
                <template x-if="filteredVariables().length === 0">
                    <div class="text-center py-4 text-gray-500 dark:text-gray-400">
                        {{ __('No variables found') }}
                    </div>
                </template>

                <ul class="space-y-2">
                    <template x-for="variable in filteredVariables()" :key="variable.name">
                        <li>
                            <button
                                @click="insertVariable(variable)"
                                class="w-full text-left p-3 bg-gray-50 dark:bg-gray-700 hover:bg-pink-50 dark:hover:bg-pink-900/20 rounded-lg border border-gray-200 dark:border-gray-600 hover:border-pink-200 dark:hover:border-pink-800 transition-colors">
                                <div class="font-medium text-gray-900 dark:text-white" x-text="variable.name"></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-mono bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded" x-text="'{' + variable.name + '}'"></span>
                                    <span class="mx-1">&rarr;</span>
                                    <span x-text="variable.value"></span>
                                </div>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Click on a variable to insert it at the cursor position in your content.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Textarea Editor -->
    <textarea
        id="simple-text-editor-{{ $propertyName }}"
        wire:model.live.debounce.500ms="currentValue"
        rows="6"
        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-200 focus:border-blue-400 transition-all duration-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:focus:ring-blue-800 dark:focus:border-blue-600 resize-y"
        placeholder="{{ __('Enter your text here...') }}"
    ></textarea>
</div>
