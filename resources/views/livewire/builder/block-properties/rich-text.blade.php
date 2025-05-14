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

                <!-- Toggle Multilingual Button -->
                <li class="ml-auto">
                    <button type="button"
                        class="inline-block p-2 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                        wire:click="toggleMultilingual">
                        @if ($multilingual)
                            <x-heroicon-o-globe-alt class="inline w-4 h-4 mr-1 align-text-bottom" />
                            {{ __('Single Language Mode') }}
                        @else
                            <x-heroicon-o-language class="inline w-4 h-4 mr-1 align-text-bottom" />
                            {{ __('Multilingual Mode') }}
                        @endif
                    </button>
                </li>
            </ul>
        </div>
    @endif

    <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">
        @if (isset($propertyLabel))
            <x-heroicon-o-document-text
                class="inline w-4 h-4 mr-1 align-text-bottom text-gray-400 dark:text-gray-500" />
            {{ $propertyLabel }}
            @if ($multilingual && count($contentLocales) > 1)
                <span
                    class="ml-1 text-xs text-gray-500 dark:text-gray-400">({{ $contentLocales[$currentLocale] ?? $currentLocale }})</span>
            @endif
        @endif
    </label>

    <div wire:ignore x-data="{
        quill: null,
        debounceTimer: null,
        currentLocale: @js($currentLocale),
        content: @js($currentValue),
        contentCache: {}, // Cache to store content for each locale
        isLocaleChanging: false,
    
        initEditor() {
            // Initialize Quill with appropriate modules
            this.quill = new Quill('#rich-text-editor-{{ $propertyName }}', {
                theme: 'snow',
                modules: {
                    resize: {
                        tools: [
                            'left',
                            'center',
                            'right',
                            'full',
                            'edit',
                            {
                                text: 'Alt',
                                verify(activeEle) {
                                    return activeEle && activeEle.tagName === 'IMG';
                                },
                                handler(evt, button, activeEle) {
                                    let alt = activeEle.alt || '';
                                    alt = window.prompt('Alt for image', alt);
                                    if (alt == null) return;
                                    activeEle.setAttribute('alt', alt);
    
                                    this.updateLivewireProperty();
                                },
                            },
                        ],
                    },
                    toolbar: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'align': [] }],
                        ['link', 'image'],
                        ['clean']
                    ]
                }
            });
    
            // Set initial content
            this.quill.root.innerHTML = this.content;
    
            // Store initial content in cache
            this.contentCache[this.currentLocale] = this.content;
    
            // Set up the text change event with debouncing
            this.quill.on('text-change', () => {
                // Skip updates during locale change
                if (this.isLocaleChanging) return;
    
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => {
                    // Cache the current content
                    this.contentCache[this.currentLocale] = this.quill.root.innerHTML;
                    this.updateLivewireProperty();
                }, 500); // 500ms debounce
            });
    
            // Listen for locale change events from Livewire
            this.$wire.$on('localeChanged', (newLocale) => {
                console.log('Locale changed event received:', newLocale);
                this.handleLocaleChange(newLocale);
            });
        },
    
        updateLivewireProperty() {
            // Skip updates during locale change to prevent overwriting
            if (this.isLocaleChanging) return;
    
            console.log('Updating Livewire property for locale:', this.currentLocale);
            // Update the Livewire property with the current content
            this.$wire.set('currentValue', this.quill.root.innerHTML);
        },
    
        handleLocaleChange(newLocale) {
            if (newLocale === this.currentLocale) return;
    
            console.log('Handling locale change from', this.currentLocale, 'to', newLocale);
    
            // Set flag to prevent text-change events during locale switch
            this.isLocaleChanging = true;
    
            // Cache current content before switching
            this.contentCache[this.currentLocale] = this.quill.root.innerHTML;
    
            // Switch locale
            this.currentLocale = newLocale;
    
            // Fetch new content from Livewire
            this.$nextTick(() => {
                this.$wire.refreshContent().then(newContent => {
                    console.log('Received content for locale:', newLocale, newContent);
    
                    // Store in cache and update editor
                    this.contentCache[newLocale] = newContent;
    
                    // Update the editor content
                    if (this.quill) {
                        this.quill.root.innerHTML = newContent;
                    }
    
                    // Reset flag after content is updated
                    setTimeout(() => {
                        this.isLocaleChanging = false;
                        console.log('Locale change completed, editor unlocked');
                    }, 100);
                });
            });
        }
    }" x-init="initEditor()">
        <div id="rich-text-editor-{{ $propertyName }}" class="dark:bg-gray-800 dark:text-white"></div>
    </div>
</div>
