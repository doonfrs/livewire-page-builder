<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div
                class="relative overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                <div
                    class="absolute inset-0 bg-gradient-to-r from-pink-50 via-fuchsia-50 to-purple-50 dark:from-pink-900/10 dark:via-fuchsia-900/10 dark:to-purple-900/10">
                </div>
                <div class="relative px-6 py-6 sm:px-8 sm:py-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">
                                {{ __('Theme Manager') }}
                            </h1>
                            <p class="mt-2 text-gray-600 dark:text-gray-400 max-w-3xl">
                                {{ __('Manage your page builder themes. Select a theme to work with or create new ones.') }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('page-builder.editor', ['pageKey' => 'home', 'themeId' => $defaultThemeId]) }}"
                                class="hidden sm:inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm font-medium rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <x-heroicon-o-cog-6-tooth class="w-5 h-5 mr-2" />
                                {{ __('Manage Themes') }}
                            </a>
                            <button wire:click="openCreateModal"
                                class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg focus:ring-2 focus:ring-pink-200 transition-all duration-150">
                                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                                {{ __('Create Theme') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Theme Info -->
        @if ($selectedTheme)
            <div
                class="mb-8 bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/20 dark:to-purple-900/20 border border-pink-200 dark:border-pink-800 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div
                                class="w-12 h-12 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center">
                                <x-heroicon-o-paint-brush class="w-6 h-6 text-pink-600 dark:text-pink-400" />
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Currently Working With: {{ $selectedTheme->name }}
                            </h3>
                            @if ($selectedTheme->description)
                                <p class="text-gray-600 dark:text-gray-300">{{ $selectedTheme->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if ($defaultThemeId == $selectedTheme->id)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                <x-heroicon-o-star class="w-3 h-3 mr-1" />
                                {{ __('Default') }}
                            </span>
                        @endif
                        <a href="{{ route('page-builder.editor', ['pageKey' => 'home', 'themeId' => $selectedTheme->id]) }}"
                            class="inline-flex items-center px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-150">
                            <x-heroicon-o-pencil class="w-4 h-4 mr-1" />
                            {{ __('Start Building') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Themes Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-3 gap-8">
            @forelse($themes as $theme)
                <div
                    class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-all duration-200 overflow-hidden">
                    <!-- Theme Header -->
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                    {{ $theme['name'] }}
                                </h3>
                                @if ($theme['description'])
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                        {{ $theme['description'] }}
                                    </p>
                                @endif
                            </div>
                            @if ($defaultThemeId == $theme['id'])
                                <span
                                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                    <x-heroicon-o-star class="w-3 h-3 mr-1" />
                                    {{ __('Default') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Theme Preview -->
                    <div class="px-6 py-4">
                        <div
                            class="h-24 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-600">
                            <div class="text-center">
                                <x-heroicon-o-eye class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto" />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Preview') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Theme Actions -->
                    <div class="px-6 pb-6 space-y-2">
                        <!-- Primary Action -->
                        <a href="{{ route('page-builder.editor', ['pageKey' => 'home', 'themeId' => $theme['id']]) }}"
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg focus:ring-2 focus:ring-pink-200 transition-all duration-150">
                            <x-heroicon-o-paint-brush class="w-4 h-4 mr-2" />
                            {{ __('Design Pages') }}
                        </a>

                        <!-- Secondary Actions -->
                        <div class="grid grid-cols-3 gap-2">
                            <button wire:click="openEditModal({{ $theme['id'] }})"
                                class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-all duration-150">
                                <x-heroicon-o-pencil class="w-4 h-4 mr-1" />
                                {{ __('Edit') }}
                            </button>
                            @if ($defaultThemeId != $theme['id'])
                                <button wire:click="confirmSetDefaultTheme({{ $theme['id'] }})"
                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400 text-sm font-medium rounded-lg transition-all duration-150">
                                    <x-heroicon-o-star class="w-4 h-4 mr-1" />
                                    {{ __('Default') }}
                                </button>
                            @endif
                            <button wire:click="confirmDeleteTheme({{ $theme['id'] }})"
                                class="inline-flex items-center justify-center px-3 py-1.5 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 text-sm font-medium rounded-lg transition-all duration-150">
                                <x-heroicon-o-trash class="w-4 h-4 mr-1" />
                                {{ __('Delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <x-heroicon-o-paint-brush class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto" />
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('No themes yet') }}
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            {{ __('Get started by creating your first theme.') }}</p>
                        <button wire:click="openCreateModal"
                            class="mt-4 inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg focus:ring-2 focus:ring-pink-200 transition-all duration-150">
                            <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                            {{ __('Create Theme') }}
                        </button>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Create Theme Modal -->
    <div x-data="{ show: @entangle('showCreateModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                :class="document.documentElement.dir === 'rtl' ? 'text-right' : 'text-left'">
                <form wire:submit="createTheme">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-pink-100 dark:bg-pink-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-paint-brush class="h-6 w-6 text-pink-600 dark:text-pink-400" />
                            </div>
                            <div class="mt-3 text-center w-full"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                    'sm:mt-0 sm:ml-4 sm:text-left'">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                    id="modal-title">
                                    {{ __('Create New Theme') }}
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Theme Name') }}</label>
                                        <input type="text" wire:model="name" id="name"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                            placeholder="{{ __('Enter theme name') }}">
                                        @error('name')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="description"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Theme Description') }}</label>
                                        <textarea wire:model="description" id="description" rows="3"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                            placeholder="{{ __('Enter theme description') }}"></textarea>
                                        @error('description')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:w-auto sm:text-sm"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                            {{ __('Create Theme') }}
                        </button>
                        <button type="button" wire:click="closeCreateModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:w-auto sm:text-sm"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Theme Modal -->
    <div x-data="{ show: @entangle('showEditModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                :class="document.documentElement.dir === 'rtl' ? 'text-right' : 'text-left'">
                <form wire:submit="updateTheme">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-pink-100 dark:bg-pink-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-pencil class="h-6 w-6 text-pink-600 dark:text-pink-400" />
                            </div>
                            <div class="mt-3 text-center w-full"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                    'sm:mt-0 sm:ml-4 sm:text-left'">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                    id="modal-title">
                                    {{ __('Edit Theme') }}
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="edit_name"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Theme Name') }}</label>
                                        <input type="text" wire:model="name" id="edit_name"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                            placeholder="{{ __('Enter theme name') }}">
                                        @error('name')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <label for="edit_description"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Theme Description') }}</label>
                                        <textarea wire:model="description" id="edit_description" rows="3"
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                                            placeholder="{{ __('Enter theme description') }}"></textarea>
                                        @error('description')
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                        <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-base font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:w-auto sm:text-sm"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                            {{ __('Update Theme') }}
                        </button>
                        <button type="button" wire:click="closeEditModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:w-auto sm:text-sm"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-data="{ show: @entangle('showDeleteModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                :class="document.documentElement.dir === 'rtl' ? 'text-right' : 'text-left'">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                        </div>
                        <div class="mt-3 text-center"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                'sm:mt-0 sm:ml-4 sm:text-left'">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                {{ __('Delete Theme') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Are you sure') }} {{ __('you want to delete the theme') }}
                                    @if ($themeToDelete)
                                        <strong>"{{ $themeToDelete->name }}"</strong>
                                    @endif
                                    ? {{ __('This action cannot be undone') }}.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                    :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                    <button wire:click="deleteTheme"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                        {{ __('Delete Theme') }}
                    </button>
                    <button wire:click="closeDeleteModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:w-auto sm:text-sm"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Default Confirmation Modal -->
    <div x-data="{ show: @entangle('showDefaultModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="show" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                :class="document.documentElement.dir === 'rtl' ? 'text-right' : 'text-left'">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                        <div
                            class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 dark:bg-yellow-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <x-heroicon-o-star class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <div class="mt-3 text-center"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                'sm:mt-0 sm:ml-4 sm:text-left'">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                {{ __('Set Default Theme') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ __('Are you sure') }} {{ __('you want to set') }}
                                    @if ($themeToSetDefault)
                                        <strong>"{{ $themeToSetDefault->name }}"</strong>
                                    @endif
                                    {{ __('as the default theme? This will be used automatically when no theme is selected.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                    :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                    <button wire:click="setDefaultTheme"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:w-auto sm:text-sm"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                        {{ __('Set as Default') }}
                    </button>
                    <button wire:click="closeDefaultModal"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:w-auto sm:text-sm"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        // Listen for theme selection events
        $wire.on('theme-selected', (event) => {
            console.log('Theme selected:', event.themeName);
        });
    </script>
@endscript
