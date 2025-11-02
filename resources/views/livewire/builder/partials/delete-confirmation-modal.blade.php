<!-- Delete Confirmation Modal -->
<template x-teleport="body">
    <div x-show="showDeleteModal"
        x-init="$watch('showDeleteModal', value => {
            if (value) {
                $nextTick(() => $refs.deleteButton?.focus());
            }
        })"
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40" style="display: none;"
        @click.outside="showDeleteModal = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 relative"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="text-center">
                <!-- Warning Icon -->
                <div
                    class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 mb-4">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                </div>

                <!-- Title -->
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                    {{ __('Confirm Delete') }}
                </h3>

                <!-- Message -->
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6" x-text="deleteMessage">
                </p>

                <!-- Action Buttons -->
                <div class="flex gap-3 justify-center">
                    <button @click="showDeleteModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-500 transition-all duration-150">
                        {{ __('Cancel') }}
                    </button>
                    <button
                        @click="if (deleteAction) deleteAction()"
                        x-ref="deleteButton"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:ring-2 focus:ring-red-200 rounded-md transition-all duration-150">
                        {{ __('Yes, Delete') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
