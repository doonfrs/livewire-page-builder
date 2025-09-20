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
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"></path>
                    </svg>
                </template>
                <template x-if="notification.type === 'error'">
                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd"></path>
                    </svg>
                </template>
                <template x-if="notification.type === 'info'">
                    <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"></path>
                    </svg>
                </template>
                <template x-if="notification.type === 'warning'">
                    <svg class="h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"></path>
                    </svg>
                </template>
            </div>
            <div x-text="notification.message" class="text-sm font-medium"></div>
            <button @click="remove(notification.id)" class="ml-auto text-gray-400 hover:text-gray-500">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>
    </template>
</div>