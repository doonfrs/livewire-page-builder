<!-- Pages Modal -->
<div x-show="showPagesModal" class="fixed inset-0 z-52 flex items-center justify-center bg-black/40"
    style="display: none;" x-data="{ pageFilter: '' }">
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
                @foreach (config('page-builder.pages', []) as $key => $page)
                @php
                $isAssoc = is_string($key) && is_array($page);
                if ($isAssoc) {
                $pageName = $key;
                $pageLabel = null;
                if (isset($page['label'])) {
                $pageLabel = $page['label'];
                } else {
                $pageLabel = Str::headline($key);
                }
                } else {
                $pageName = $page;
                $pageLabel = Str::headline($page);
                }
                $pageLabel = __($pageLabel);
                @endphp
                <li class="mb-2" x-show="!pageFilter || '{{ strtolower($pageLabel) }}'.includes(pageFilter.toLowerCase())">
                    <a href="{{ route('page-builder.page.edit', ['pageKey' => $pageName]) }}"
                        class="block px-4 py-2 rounded hover:bg-blue-100 dark:hover:bg-blue-900 transition text-gray-800 dark:text-gray-100">
                        <div class="flex items-center border-b border-gray-200 dark:border-gray-700 pb-2">
                            <x-heroicon-o-document-text class="w-4 h-4 mr-2 me-2" />
                            {{ $pageLabel }}
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>