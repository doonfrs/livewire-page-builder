<!-- Pages Modal -->
<div x-show="showPagesModal" class="fixed inset-0 z-52 flex items-center justify-center bg-black/40" style="display: none;"
    x-data="{ pageFilter: '' }">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg p-8 relative"
        @click.outside="showPagesModal = false">
        <button @click="showPagesModal = false"
            class="absolute top-3 right-3 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
            <x-heroicon-o-x-mark class="w-6 h-6" />
        </button>
        <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100 flex items-center gap-2">
            <x-heroicon-o-document-text class="w-6 h-6 text-blue-500" />
            {{ __('Pages') }}
        </h2>

        <!-- Search filter -->
        <div class="relative mb-4">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-500 dark:text-gray-400" />
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
                        <a href="{{ $themeId ? route('page-builder.editor', ['pageKey' => $page['key'], 'themeId' => $themeId]) : route('page-builder.editor', ['pageKey' => $page['key']]) }}"
                            class="block px-4 py-3 rounded-lg transition-all duration-200 {{ $page['isCurrentPage'] ? 'bg-blue-50 dark:bg-blue-900/20 border-l-4 border-l-blue-500 shadow-sm' : 'hover:bg-gray-50 dark:hover:bg-gray-700' }} text-gray-800 dark:text-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <x-heroicon-o-document-text class="w-4 h-4 mr-3 text-gray-500 dark:text-gray-400" />
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
                                    <!-- Empty page flag with tooltip -->
                                    @if (!$page['hasComponents'])
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-700 cursor-help"
                                            title="{{ __('This page has no content yet. Click to start building.') }}">
                                            <x-heroicon-o-exclamation-triangle class="w-3 h-3" />
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
