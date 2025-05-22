<div
    x-data="{ 
        notifications: [],
        add(message, type = 'info') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            setTimeout(() => {
                this.remove(id);
            }, 3000);
        },
        remove(id) {
            this.notifications = this.notifications.filter(notification => notification.id !== id);
        }
    }"
    x-on:notify.window="add($event.detail.message, $event.detail.type)"
    class="fixed top-0 right-0 z-50 p-4 space-y-2">
    <template x-for="notification in notifications" :key="notification.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-2"
            :class="{
                'bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-300': notification.type === 'success',
                'bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-300': notification.type === 'error',
                'bg-blue-50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300': notification.type === 'info',
                'bg-yellow-50 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300': notification.type === 'warning'
            }"
            class="flex items-center px-4 py-3 rounded-lg shadow-lg border"
            :class="{
                'border-green-200 dark:border-green-800': notification.type === 'success',
                'border-red-200 dark:border-red-800': notification.type === 'error',
                'border-blue-200 dark:border-blue-800': notification.type === 'info',
                'border-yellow-200 dark:border-yellow-800': notification.type === 'warning'
            }">
            <div class="flex-shrink-0 mr-3">
                <template x-if="notification.type === 'success'">
                    <x-heroicon-s-check-circle class="h-5 w-5 text-green-500" />
                </template>
                <template x-if="notification.type === 'error'">
                    <x-heroicon-s-x-circle class="h-5 w-5 text-red-500" />
                </template>
                <template x-if="notification.type === 'info'">
                    <x-heroicon-s-information-circle class="h-5 w-5 text-blue-500" />
                </template>
                <template x-if="notification.type === 'warning'">
                    <x-heroicon-s-exclamation-circle class="h-5 w-5 text-yellow-500" />
                </template>
            </div>
            <div x-text="notification.message" class="text-sm font-medium"></div>
            <button @click="remove(notification.id)" class="ml-auto text-gray-400 hover:text-gray-500">
                <x-heroicon-s-x-circle class="h-4 w-4" />
            </button>
        </div>
    </template>
</div>