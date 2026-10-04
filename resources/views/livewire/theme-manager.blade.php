@section('title', __('Theme Manager - :app', ['app' => config('app.name')]))
<div>
    <div class="min-h-screen overflow-x-hidden bg-base-200 text-base-content">
        <!-- Top bar -->
        <header class="bg-base-100 border-b border-base-300">
            <div class="max-w-7xl mx-auto flex items-center gap-2 sm:gap-3 h-16 px-3 sm:px-6">
                <a href="{{ url('/') }}" class="btn btn-ghost btn-square" title="{{ __('Home') }}"
                    aria-label="{{ __('Home') }}">
                    <x-heroicon-o-home class="w-5 h-5" />
                </a>
                <h1 class="flex-1 min-w-0 truncate text-lg font-semibold">
                    {{ __('Theme Manager') }}
                    <span class="hidden sm:inline font-normal text-base-content/50">- {{ config('app.name') }}</span>
                </h1>

                <div class="flex items-center gap-2 shrink-0">
                    <livewire:language-switcher />

                    @if (!empty($customHeaderHtml))
                        {!! $customHeaderHtml !!}
                    @endif

                    @if (!empty($templateGalleryUrl))
                        <a href="{{ $templateGalleryUrl }}" class="btn bg-base-100"
                            title="{{ __('Browse Template Gallery') }}">
                            <x-heroicon-o-rectangle-stack class="w-5 h-5" />
                            <span class="hidden sm:inline">{{ __('Templates') }}</span>
                        </a>
                    @endif

                    <!-- Actions menu: create, and import where the host allows moving themes as files -->
                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="btn bg-base-100 btn-square"
                            :aria-expanded="open" title="{{ __('More actions') }}"
                            aria-label="{{ __('More actions') }}">
                            <x-heroicon-o-ellipsis-vertical class="w-5 h-5" />
                        </button>
                        <ul x-show="open" x-cloak @click.away="open = false" @keydown.escape.window="open = false"
                            x-transition.origin.top
                            class="menu absolute end-0 z-30 mt-2 w-56 rounded-box bg-base-100 border border-base-300 shadow-lg">
                            <li>
                                <button type="button" wire:click="openCreateModal" @click="open = false">
                                    <x-heroicon-o-plus class="w-4 h-4" />
                                    {{ __('Create Theme') }}
                                </button>
                            </li>
                            @if ($canTransferThemes)
                                <li>
                                    <button type="button" wire:click="openImportModal" @click="open = false">
                                        <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                        {{ __('Import Theme') }}
                                    </button>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-3 py-6 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @forelse($themes as $theme)
                    <div wire:key="theme-{{ $theme['id'] }}"
                        class="card bg-base-100 border border-base-300">
                        <a href="{{ route('page-builder.editor', ['pageKey' => 'home', 'themeId' => $theme['id']]) }}"
                            class="relative block aspect-video overflow-hidden rounded-t-[inherit] bg-base-200 border-b border-base-300"
                            title="{{ __('Design Pages') }}">
                            {{-- Absolute, so a whole-page screenshot cannot stretch the box past 16:9:
                                 aspect-ratio alone still grows to fit taller content. The clipping is
                                 here and not on the card, which would also cut off the card's menu. --}}
                            @if ($theme['preview_image_url'])
                                <img src="{{ $theme['preview_image_url'] }}" alt="{{ $theme['name'] }}" loading="lazy"
                                    decoding="async" class="absolute inset-0 w-full h-full object-cover object-top">
                            @else
                                <span class="w-full h-full flex items-center justify-center text-base-content/25">
                                    <x-heroicon-o-photo class="w-10 h-10" />
                                </span>
                            @endif
                        </a>

                        <div class="card-body p-4 gap-4">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="font-semibold truncate">{{ $theme['name'] }}</h3>
                                    @if ($theme['description'])
                                        <p class="mt-1 text-sm text-base-content/60 line-clamp-2">
                                            {{ $theme['description'] }}
                                        </p>
                                    @endif
                                </div>
                                @if ($defaultThemeId == $theme['id'])
                                    <span class="badge badge-soft badge-success badge-sm shrink-0">{{ __('Default') }}</span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('page-builder.editor', ['pageKey' => 'home', 'themeId' => $theme['id']]) }}"
                                    class="btn btn-sm btn-neutral flex-1">
                                    <x-heroicon-o-paint-brush class="w-4 h-4" />
                                    {{ __('Design Pages') }}
                                </a>

                                <button type="button" wire:click="previewTheme({{ $theme['id'] }})"
                                    class="btn btn-sm btn-square" title="{{ __('Preview') }}"
                                    aria-label="{{ __('Preview') }}">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </button>

                                @if ($defaultThemeId == $theme['id'])
                                    <span class="btn btn-sm btn-square btn-soft btn-success cursor-default"
                                        title="{{ __('Default theme') }}" aria-label="{{ __('Default theme') }}">
                                        <x-heroicon-s-check-circle class="w-4 h-4" />
                                    </span>
                                @else
                                    <button type="button" wire:click="confirmSetDefaultTheme({{ $theme['id'] }})"
                                        class="btn btn-sm btn-square" title="{{ __('Set as Default') }}"
                                        aria-label="{{ __('Set as Default') }}">
                                        <x-heroicon-o-check-circle class="w-4 h-4" />
                                    </button>
                                @endif

                                <div class="relative" x-data="{ open: false }">
                                    <button type="button" @click="open = !open" :aria-expanded="open"
                                        class="btn btn-sm btn-square" title="{{ __('More actions') }}"
                                        aria-label="{{ __('More actions') }}">
                                        <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                    </button>
                                    <ul x-show="open" x-cloak @click.away="open = false"
                                        @keydown.escape.window="open = false" x-transition.origin.top
                                        class="menu absolute end-0 z-30 mt-2 w-48 rounded-box bg-base-100 border border-base-300 shadow-lg">
                                        <li>
                                            <button type="button" wire:click="openEditModal({{ $theme['id'] }})"
                                                @click="open = false">
                                                <x-heroicon-o-pencil-square class="w-4 h-4" />
                                                {{ __('Edit') }}
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button" wire:click="openCloneModal({{ $theme['id'] }})"
                                                @click="open = false">
                                                <x-heroicon-o-document-duplicate class="w-4 h-4" />
                                                {{ __('Clone') }}
                                            </button>
                                        </li>
                                        @if ($canTransferThemes)
                                            <li>
                                                <button type="button" wire:click="exportTheme({{ $theme['id'] }})"
                                                    @click="open = false">
                                                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                                                    {{ __('Export') }}
                                                </button>
                                            </li>
                                        @endif
                                        <li class="mt-1 pt-1 border-t border-base-300">
                                            <button type="button" wire:click="openDeleteModal({{ $theme['id'] }})"
                                                @click="open = false" class="text-error">
                                                <x-heroicon-o-trash class="w-4 h-4" />
                                                {{ __('Delete') }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full card bg-base-100 border border-base-300">
                        <div class="card-body items-center text-center py-12">
                            <x-heroicon-o-swatch class="w-12 h-12 text-base-content/30" />
                            <h3 class="mt-2 text-lg font-semibold">{{ __('No themes yet') }}</h3>
                            @if (!empty($templateGalleryUrl))
                                <a href="{{ $templateGalleryUrl }}" class="btn btn-neutral mt-4">
                                    <x-heroicon-o-rectangle-stack class="w-5 h-5" />
                                    {{ __('Browse Template Gallery') }}
                                </a>
                            @else
                                <p class="text-base-content/60">{{ __('Get started by creating your first theme.') }}</p>
                                <button type="button" wire:click="openCreateModal" class="btn btn-neutral mt-4">
                                    <x-heroicon-o-plus class="w-5 h-5" />
                                    {{ __('Create Theme') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </main>

        <!-- Create Theme Modal -->
        <div x-data="{ show: $wire.entangle('showCreateModal') }" class="modal" :class="show && 'modal-open'"
            role="dialog" aria-modal="true" aria-labelledby="create-theme-title">
            <div class="modal-box">
                <form wire:submit="createTheme">
                    <h3 class="text-lg font-semibold" id="create-theme-title">{{ __('Create New Theme') }}</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium mb-1">{{ __('Theme Name') }}</label>
                            <input type="text" wire:model="name" id="name" class="input w-full"
                                placeholder="{{ __('Enter theme name') }}">
                            @error('name')
                                <span class="text-error text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium mb-1">{{ __('Theme Description') }}</label>
                            <textarea wire:model="description" id="description" rows="3" class="textarea w-full"
                                placeholder="{{ __('Enter theme description') }}"></textarea>
                            @error('description')
                                <span class="text-error text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-action">
                        <button type="button" wire:click="closeCreateModal" class="btn">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-neutral">{{ __('Create Theme') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Theme Modal -->
        <div x-data="{ show: $wire.entangle('showEditModal') }" class="modal" :class="show && 'modal-open'"
            role="dialog" aria-modal="true" aria-labelledby="edit-theme-title">
            <div class="modal-box">
                <form wire:submit="updateTheme">
                    <h3 class="text-lg font-semibold" id="edit-theme-title">{{ __('Edit Theme') }}</h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="edit_name" class="block text-sm font-medium mb-1">{{ __('Theme Name') }}</label>
                            <input type="text" wire:model="name" id="edit_name" class="input w-full"
                                placeholder="{{ __('Enter theme name') }}">
                            @error('name')
                                <span class="text-error text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_description"
                                class="block text-sm font-medium mb-1">{{ __('Theme Description') }}</label>
                            <textarea wire:model="description" id="edit_description" rows="3" class="textarea w-full"
                                placeholder="{{ __('Enter theme description') }}"></textarea>
                            @error('description')
                                <span class="text-error text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-action">
                        <button type="button" wire:click="closeEditModal" class="btn">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-neutral">{{ __('Update Theme') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div x-data="{ show: $wire.entangle('showDeleteModal') }" class="modal" :class="show && 'modal-open'"
            role="dialog" aria-modal="true" aria-labelledby="delete-theme-title">
            <div class="modal-box">
                <h3 class="text-lg font-semibold" id="delete-theme-title">{{ __('Delete Theme') }}</h3>
                <p class="mt-2 text-sm text-base-content/70">
                    @if ($themeToDelete)
                        {{ __("Are you sure you want to delete the theme ':name'? This action cannot be undone.", ['name' => $themeToDelete->name]) }}
                    @else
                        {{ __('Are you sure you want to delete the theme? This action cannot be undone.') }}
                    @endif
                </p>
                @if ($themeToDelete && $themeToDelete->pages()->count() > 0)
                    <div role="alert" class="alert alert-error alert-soft mt-4 text-sm">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 shrink-0" />
                        <span>
                            <strong>{{ __('Warning') }}:</strong>
                            {{ __('This theme has') }}
                            {{ $themeToDelete->pages()->count() }}
                            {{ __('associated page(s)') }}.
                            {{ __('All pages will be permanently deleted along with the theme') }}.
                        </span>
                    </div>
                @endif
                <div class="modal-action">
                    <button type="button" wire:click="closeDeleteModal" class="btn">{{ __('Cancel') }}</button>
                    <button type="button" wire:click="deleteTheme" class="btn btn-error">{{ __('Delete Theme') }}</button>
                </div>
            </div>
        </div>

        <!-- Set Default Confirmation Modal -->
        <div x-data="{ show: $wire.entangle('showDefaultModal') }" class="modal" :class="show && 'modal-open'"
            role="dialog" aria-modal="true" aria-labelledby="default-theme-title">
            <div class="modal-box">
                <h3 class="text-lg font-semibold" id="default-theme-title">{{ __('Set Default Theme') }}</h3>
                <p class="mt-2 text-sm text-base-content/70">
                    @if ($themeToSetDefault)
                        {{ __("Are you sure you want to set ':name' as the default theme? This will be used automatically when no theme is selected.", ['name' => $themeToSetDefault->name]) }}
                    @else
                        {{ __('Are you sure you want to set this theme as the default theme? This will be used automatically when no theme is selected.') }}
                    @endif
                </p>
                <div class="modal-action">
                    <button type="button" wire:click="closeDefaultModal" class="btn">{{ __('Cancel') }}</button>
                    <button type="button" wire:click="setDefaultTheme" class="btn btn-neutral">
                        <x-heroicon-o-check-circle class="w-5 h-5" />
                        {{ __('Set as Default') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Locked Theme Notice: what Set as Default opens on a theme the host has locked -->
        <div x-data="{ show: $wire.entangle('showLockedModal') }" class="modal" :class="show && 'modal-open'"
            role="dialog" aria-modal="true" aria-labelledby="locked-modal-title">
            <div class="modal-box max-w-md">
                @if ($lockedNotice)
                    <div class="flex items-start gap-4">
                        <span class="flex shrink-0 items-center justify-center h-10 w-10 rounded-full bg-base-200">
                            <x-heroicon-o-sparkles class="h-5 w-5" />
                        </span>
                        <div class="min-w-0">
                            <h3 class="text-lg font-semibold" id="locked-modal-title">{{ $lockedNotice['title'] }}</h3>
                            @if ($lockedNotice['message'] !== '')
                                <p class="mt-2 text-sm text-base-content/70">{{ $lockedNotice['message'] }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="modal-action">
                        <button type="button" wire:click="closeLockedModal" class="btn">{{ __('Cancel') }}</button>
                        @if ($lockedNotice['url'] !== '')
                            <a href="{{ $lockedNotice['url'] }}" class="btn btn-neutral">
                                <x-heroicon-o-arrow-up-circle class="h-5 w-5" />
                                {{ $lockedNotice['label'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
            <div class="modal-backdrop" wire:click="closeLockedModal"></div>
        </div>

        @if ($canTransferThemes)
            <!-- Import Theme Modal -->
            <div x-data="{ show: $wire.entangle('showImportModal') }" class="modal" :class="show && 'modal-open'"
                role="dialog" aria-modal="true" aria-labelledby="import-theme-title">
                <div class="modal-box">
                    <form wire:submit="importTheme">
                        <h3 class="text-lg font-semibold" id="import-theme-title">{{ __('Import Theme') }}</h3>
                        <p class="mt-2 text-sm text-base-content/70">{{ __('Select a theme file to import.') }}</p>
                        <div class="mt-4">
                            <label for="importFile" class="block text-sm font-medium mb-1">{{ __('Theme File') }}</label>
                            <input type="file" wire:model="importFile" id="importFile" class="file-input w-full">
                            @error('importFile')
                                <span class="text-error text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="modal-action">
                            <button type="button" wire:click="closeImportModal" class="btn">{{ __('Cancel') }}</button>
                            <button type="submit" wire:loading.attr="disabled" :disabled="!$wire.importFile"
                                class="btn btn-neutral">
                                <span wire:loading.remove wire:target="importTheme" class="flex items-center gap-2">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                                    {{ __('Import Theme') }}
                                </span>
                                <span wire:loading wire:target="importTheme" class="flex items-center gap-2">
                                    <span class="loading loading-spinner loading-xs"></span>
                                    {{ __('Importing...') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Clone Theme Modal -->
        <div x-data="{ show: $wire.entangle('showCloneModal') }" class="modal" :class="show && 'modal-open'"
            role="dialog" aria-modal="true" aria-labelledby="clone-theme-title">
            <div class="modal-box">
                <form wire:submit="cloneTheme">
                    <h3 class="text-lg font-semibold" id="clone-theme-title">{{ __('Clone Theme') }}</h3>
                    <p class="mt-2 text-sm text-base-content/70">
                        {{ __('Create a copy of this theme with all its pages. Enter a name for the cloned theme.') }}
                    </p>
                    <div class="mt-4">
                        <label for="cloneName" class="block text-sm font-medium mb-1">{{ __('Clone Theme Name') }}</label>
                        <input type="text" wire:model="cloneName" id="cloneName" class="input w-full"
                            placeholder="{{ __('Enter theme name') }}">
                        @error('cloneName')
                            <span class="text-error text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="modal-action">
                        <button type="button" wire:click="closeCloneModal" class="btn">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-neutral">{{ __('Clone Theme') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Component -->
    @include('page-builder::livewire.builder.partials.notification')
</div>
