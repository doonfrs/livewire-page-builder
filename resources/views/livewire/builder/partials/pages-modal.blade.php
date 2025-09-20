<!-- Pages Modal -->
<div x-show="{{ isset($modalMode) && $modalMode === 'copy' ? 'showCopyFromModal' : 'showPagesModal' }}"
    class="fixed inset-0 z-52 flex items-center justify-center bg-black/40" style="display: none;" x-data="{ pageFilter: '' }">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-8 relative"
        @click.outside="{{ isset($modalMode) && $modalMode === 'copy' ? 'showCopyFromModal = false' : 'showPagesModal = false' }}">
        <button
            @click="{{ isset($modalMode) && $modalMode === 'copy' ? 'showCopyFromModal = false' : 'showPagesModal = false' }}"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            @if (isset($modalMode) && $modalMode === 'copy')
                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"></path>
                </svg>
                {{ __('Copy From Page') }}
            @else
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                </svg>
                {{ __('Pages') }}
            @endif
        </h2>

        <!-- Search filter -->
        <div class="relative mb-4">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
                </svg>
            </div>
            <input type="text" x-model="pageFilter"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="{{ __('Search pages...') }}">
        </div>
        <!-- Scrollable page list -->
        <div class="overflow-y-auto max-h-[50vh] pr-1">
            <ul>
                @foreach ($pagesWithStatus as $page)
                    <li class="mb-2"
                        x-show="!pageFilter || '{{ strtolower($page['label']) }}'.includes(pageFilter.toLowerCase())">
                        @if (isset($modalMode) && $modalMode === 'copy')
                            <!-- Copy mode: button to copy components -->
                            <button
                                @click="{{ isset($modalMode) && $modalMode === 'copy' ? 'showCopyFromModal = false' : 'showPagesModal = false' }}"
                                class="w-full text-left px-4 py-3 rounded-lg transition-all duration-200 {{ $page['isCurrentPage'] ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-l-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }} text-gray-800 dark:text-gray-100"
                                {{ $page['isCurrentPage'] ? 'disabled' : '' }}
                                title="{{ $page['isCurrentPage'] ? __('Cannot copy from current page') : ($this->currentPageHasContent() ? __('Click to copy components from this page (will replace current content)') : __('Click to copy components from this page')) }}"
                                x-data="{
                                    confirmCopy() {
                                        @if ($this->currentPageHasContent()) // Show custom confirmation modal
                                            copySourcePageKey = '{{ $page['key'] }}';
                                            copySourcePageLabel = '{{ $page['label'] }}';
                                            showCopyConfirmationModal = true;
                                        @else
                                            // Copy immediately for empty pages
                                            $wire.copyComponentsFromPage('{{ $page['key'] }}'); @endif
                                    }
                                }" x-on:click="confirmCopy()">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center flex-1">
                                        <svg class="w-4 h-4 mr-3 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 0 1-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 0 1 1.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 0 0-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 0 1-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 0 0-3.375-3.375h-1.5a1.125 1.125 0 0 1-1.125-1.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H9.75"></path>
                                        </svg>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100">{{ $page['label'] }}</span>

                                        <!-- Current page indicator -->
                                        @if ($page['isCurrentPage'])
                                            <span
                                                class="ml-3 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">
                                                {{ __('Current') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Status indicators -->
                                    <div class="flex items-center gap-2">
                                        <!-- Block page indicator -->
                                        @if ($page['isBlock'])
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-700 cursor-help"
                                                title="{{ __('This is a reusable page block') }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"></path>
                                                </svg>
                                                {{ __('Block') }}
                                            </span>
                                        @endif

                                        <!-- Empty page flag with tooltip -->
                                        @if (!$page['hasComponents'])
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-700 cursor-help"
                                                title="{{ __('This page has no content to copy') }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"></path>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @else
                            <!-- Navigate mode: link to switch pages -->
                            <a href="{{ $themeId ? route('page-builder.editor', ['pageKey' => $page['key'], 'themeId' => $themeId]) : route('page-builder.editor', ['pageKey' => $page['key']]) }}"
                                class="block px-4 py-3 rounded-lg transition-all duration-200 {{ $page['isCurrentPage'] ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-l-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }} text-gray-800 dark:text-gray-100">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center flex-1">
                                        <svg class="w-4 h-4 mr-3 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"></path>
                                        </svg>
                                        <span
                                            class="font-medium text-gray-900 dark:text-gray-100">{{ $page['label'] }}</span>

                                        <!-- Current page indicator -->
                                        @if ($page['isCurrentPage'])
                                            <span
                                                class="ml-3 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-200">
                                                {{ __('Current') }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Status indicators -->
                                    <div class="flex items-center gap-2">
                                        <!-- Block page indicator -->
                                        @if ($page['isBlock'])
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-200 border border-purple-200 dark:border-purple-700 cursor-help"
                                                title="{{ __('This is a reusable page block') }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"></path>
                                                </svg>
                                                {{ __('Block') }}
                                            </span>
                                        @endif

                                        <!-- Empty page flag with tooltip -->
                                        @if (!$page['hasComponents'])
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-700 cursor-help"
                                                title="{{ __('This page has no content yet. Click to start building.') }}">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"></path>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
