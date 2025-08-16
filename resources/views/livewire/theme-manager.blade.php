@section('title', __('Theme Manager - :app', ['app' => config('app.name')]))

<div class="min-h-screen bg-gray-100 dark:bg-gray-900 flex flex-col">
    <!-- Header Navbar -->
    <div
        class="flex items-center justify-between bg-gray-200 dark:bg-gray-800 shadow-md p-3 text-gray-900 dark:text-gray-100">
        <div class="flex items-center gap-4">
            <a href="{{ url('/') }}" class="p-1 rounded hover:bg-gray-300/50 dark:hover:bg-gray-700/50"
                title="{{ __('Home') }}">
                <x-heroicon-o-home class="w-6 h-6" />
            </a>
            <x-heroicon-o-paint-brush class="w-6 h-6 text-pink-600" />
            <span
                class="text-lg sm:text-xl font-semibold">{{ __('Theme Manager - :app', ['app' => config('app.name')]) }}</span>
        </div>

        <div class="flex items-center gap-3">
            <livewire:language-switcher />
            <button wire:click="openImportModal"
                class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:ring-2 focus:ring-pink-200 transition">
                <x-heroicon-o-arrow-up-tray class="w-5 h-5 mr-1 ml-1" />
                {{ __('Import Theme') }}
            </button>
            <button wire:click="openCreateModal"
                class="inline-flex items-center px-3 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-md focus:ring-2 focus:ring-pink-200 transition">
                <x-heroicon-o-plus class="w-5 h-5 mr-1" />
                {{ __('Create Theme') }}
            </button>
        </div>
    </div>

    <!-- Contained layout wrapper -->
    <div class="flex-1 p-6">
        <div class="max-w-7xl mx-auto">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
                <div class="px-4 sm:px-6 lg:px-8 py-6">
                    <!-- Intro text -->
                    <p class="mb-6 text-gray-600 dark:text-gray-400">
                        {{ __('Manage your page builder themes. Select a theme to work with or create new ones.') }}</p>

                    <!-- Selected Theme Info -->
                    @if ($selectedTheme)
                        <div
                            class="mb-8 bg-gradient-to-r from-pink-50 to-purple-50 dark:from-pink-900/20 dark:to-purple-900/20 border border-pink-200 dark:border-pink-800 rounded-xl p-6">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-12 h-12 bg-pink-100 dark:bg-pink-900 rounded-full flex items-center justify-center">
                                            <x-heroicon-o-paint-brush
                                                class="w-6 h-6 text-pink-600 dark:text-pink-400" />
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ __('Currently Working With:') }} {{ $selectedTheme->name }}
                                        </h3>
                                        @if ($selectedTheme->description)
                                            <p class="text-gray-600 dark:text-gray-300">
                                                {{ $selectedTheme->description }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if ($defaultThemeId == $selectedTheme->id)
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400">
                                            <x-heroicon-o-star class="w-3 h-3 mr-1 ml-1" />
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
                                                <x-heroicon-o-star class="w-3 h-3 mr-1 ml-1" />
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
                                            <x-heroicon-o-eye
                                                class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto" />
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                {{ __('Preview') }}</p>
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
                                    <div class="grid grid-cols-3 gap-2 mb-2">
                                        <button wire:click="openEditModal({{ $theme['id'] }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-all duration-150">
                                            <x-heroicon-o-pencil class="w-4 h-4 mr-1 ml-1" />
                                            {{ __('Edit') }}
                                        </button>
                                        <button wire:click="openCloneModal({{ $theme['id'] }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 bg-green-100 dark:bg-green-900/30 hover:bg-green-200 dark:hover:bg-green-900/50 text-green-700 dark:text-green-400 text-sm font-medium rounded-lg transition-all duration-150">
                                            <x-heroicon-o-document-duplicate class="w-4 h-4 mr-1 ml-1" />
                                            {{ __('Clone') }}
                                        </button>
                                        <button wire:click="exportTheme({{ $theme['id'] }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 text-sm font-medium rounded-lg transition-all duration-150">
                                            <x-heroicon-o-arrow-down-tray class="w-4 h-4 mr-1 ml-1" />
                                            {{ __('Export') }}
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        @if ($defaultThemeId != $theme['id'])
                                            <button wire:click="confirmSetDefaultTheme({{ $theme['id'] }})"
                                                class="inline-flex items-center justify-center px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 text-yellow-700 dark:text-yellow-400 text-sm font-medium rounded-lg transition-all duration-150">
                                                <x-heroicon-o-star class="w-4 h-4 mr-1 ml-1" />
                                                {{ __('Default') }}
                                            </button>
                                        @else
                                            <div></div>
                                        @endif
                                        <button wire:click="confirmDeleteTheme({{ $theme['id'] }})"
                                            class="inline-flex items-center justify-center px-3 py-1.5 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 text-sm font-medium rounded-lg transition-all duration-150">
                                            <x-heroicon-o-trash class="w-4 h-4 mr-1 ml-1" />
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <!-- Empty State -->
                            <div class="col-span-full">
                                <div class="text-center py-12">
                                    <x-heroicon-o-paint-brush
                                        class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto" />
                                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                        {{ __('No themes yet') }}
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

                <!-- Footer bar -->
                <div class="h-3 bg-gray-100 dark:bg-gray-800 rounded-b-2xl"></div>
            </div>
        </div>
    </div>

    <!-- Simple Footer -->
    <div class="bg-gray-200 dark:bg-gray-800 h-12 border-t border-gray-300 dark:border-gray-700"></div>

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
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-sm font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-200 transition-all duration-150">
                            {{ __('Create Theme') }}
                        </button>
                        <button type="button" wire:click="closeCreateModal"
                            class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:ml-3"
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
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-pink-600 text-sm font-medium text-white hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500">
                            {{ __('Update Theme') }}
                        </button>
                        <button type="button" wire:click="closeEditModal"
                            class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:ml-3"
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
                                    @if ($themeToDelete)
                                        {{ __("Are you sure you want to delete the theme ':name'? This action cannot be undone.", ['name' => $themeToDelete->name]) }}
                                    @else
                                        {{ __('Are you sure you want to delete the theme? This action cannot be undone.') }}
                                    @endif
                                </p>
                                @if ($themeToDelete && $themeToDelete->pages()->count() > 0)
                                    <div
                                        class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <x-heroicon-o-exclamation-triangle
                                                    class="h-5 w-5 text-red-400 dark:text-red-300" />
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-red-700 dark:text-red-300">
                                                    <strong>{{ __('Warning') }}:</strong> {{ __('This theme has') }}
                                                    {{ $themeToDelete->pages()->count() }}
                                                    {{ __('associated page(s)') }}.
                                                    {{ __('All pages will be permanently deleted along with the theme') }}.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                    :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                    <button wire:click="deleteTheme"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        {{ __('Delete Theme') }}
                    </button>
                    <button wire:click="closeDeleteModal"
                        class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:ml-3"
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
                                    @if ($themeToSetDefault)
                                        {{ __("Are you sure you want to set ':name' as the default theme? This will be used automatically when no theme is selected.", ['name' => $themeToSetDefault->name]) }}
                                    @else
                                        {{ __('Are you sure you want to set this theme as the default theme? This will be used automatically when no theme is selected.') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                    :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                    <button wire:click="setDefaultTheme"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-sm font-medium text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        {{ __('Set as Default') }}
                    </button>
                    <button wire:click="closeDefaultModal"
                        class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:ml-3"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Theme Modal -->
    <div x-data="{ show: @entangle('showImportModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
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
                <form wire:submit="importTheme">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-arrow-up-tray class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="mt-3 text-center w-full"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                    'sm:mt-0 sm:ml-4 sm:text-left'">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                    id="modal-title">
                                    {{ __('Import Theme') }}
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        {{ __('Select a theme JSON file to import. The theme will be created with all its pages.') }}
                                    </p>
                                    <div>
                                        <label for="importFile"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Theme File') }}</label>
                                        <input type="file" wire:model="importFile" id="importFile" accept=".json"
                                            class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100 dark:file:bg-pink-900/30 dark:file:text-pink-400 dark:hover:file:bg-pink-900/50">
                                        @error('importFile')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                        <button type="submit" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed" :disabled="!$wire.importFile"
                            class="inline-flex items-center justify-center rounded-md border border-transparent shadow-sm px-8 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-blue-600"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                            <div wire:loading.remove wire:target="importTheme" class="flex items-center">
                                <x-heroicon-o-arrow-up-tray class="w-4 h-4 ml-2 mr-2 flex-shrink-0" />
                                <span>{{ __('Import Theme') }}</span>
                            </div>
                            <div wire:loading wire:target="importTheme" class="flex items-center">
                                <x-heroicon-o-arrow-path class="w-4 h-4 mr-2 animate-spin flex-shrink-0" />
                                <span>{{ __('Importing...') }}</span>
                            </div>
                        </button>
                        <button type="button" wire:click="closeImportModal"
                            class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Clone Theme Modal -->
    <div x-data="{ show: @entangle('showCloneModal') }" x-show="show" x-cloak class="fixed inset-0 z-50 overflow-y-auto"
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
                <form wire:submit="cloneTheme">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row-reverse' : ''">
                            <div
                                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-900/30 sm:mx-0 sm:h-10 sm:w-10">
                                <x-heroicon-o-document-duplicate class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="mt-3 text-center w-full"
                                :class="document.documentElement.dir === 'rtl' ? 'sm:mt-0 sm:mr-4 sm:text-right' :
                                    'sm:mt-0 sm:ml-4 sm:text-left'">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white"
                                    id="modal-title">
                                    {{ __('Clone Theme') }}
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                        {{ __('Create a copy of this theme with all its pages. Enter a name for the cloned theme.') }}
                                    </p>
                                    <div>
                                        <label for="cloneName"
                                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Clone Theme Name') }}</label>
                                        <input type="text" wire:model="cloneName" id="cloneName"
                                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"
                                            placeholder="{{ __('Enter theme name') }}">
                                        @error('cloneName')
                                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex"
                        :class="document.documentElement.dir === 'rtl' ? 'sm:flex-row' : 'sm:flex-row-reverse'">
                        <button type="submit"
                            class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            {{ __('Clone Theme') }}
                        </button>
                        <button type="button" wire:click="closeCloneModal"
                            class="mt-3 inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3"
                            :class="document.documentElement.dir === 'rtl' ? 'sm:mr-3' : 'sm:ml-3'">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>
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
