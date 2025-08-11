<div
    x-data="{
        toasts: [],
        add(toast) {
            toast.id = Date.now() + Math.random();
            toast.type = toast.type || 'info';
            toast.timeout = (toast.timeout ?? 3000);
            toast.timeoutId = null;
            this.toasts.push(toast);
            if (toast.timeout > 0) {
                toast.timeoutId = setTimeout(() => this.remove(toast.id), toast.timeout);
            }
        },
        remove(id) { 
            const toast = this.toasts.find(t => t.id === id);
            if (toast && toast.timeoutId) {
                clearTimeout(toast.timeoutId);
            }
            this.toasts = this.toasts.filter(t => t.id !== id);
        },
        pauseTimeout(toast) {
            if (toast.timeoutId) {
                clearTimeout(toast.timeoutId);
                toast.timeoutId = null;
            }
        },
        resumeTimeout(toast) {
            if (toast.timeout > 0 && !toast.timeoutId) {
                toast.timeoutId = setTimeout(() => this.remove(toast.id), toast.timeout);
            }
        }
    }"
    @notify.window="add($event.detail)"
    class="fixed inset-0 z-50 pointer-events-none"
    aria-live="polite"
>
    <div class="absolute top-4 right-4 w-full max-w-sm sm:max-w-md flex flex-col space-y-2">
        <template x-for="t in toasts" :key="t.id">
            <div
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                @mouseenter="pauseTimeout(t)"
                @mouseleave="resumeTimeout(t)"
                @click="remove(t.id)"
                class="pointer-events-auto rounded-lg shadow-lg px-4 py-3 text-white cursor-pointer"
                :class="{
                    'bg-green-600': t.type === 'success',
                    'bg-red-600': t.type === 'error',
                    'bg-yellow-600': t.type === 'warning',
                    'bg-primary-600': t.type === 'primary',
                    'bg-gray-800': !['success','error','warning','primary'].includes(t.type),
                }"
            >
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <template x-if="t.type === 'success'">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="t.type === 'error'">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </template>
                        <template x-if="t.type === 'warning'">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14l-7-14-7 14z"/>
                            </svg>
                        </template>
                        <template x-if="!['success','error','warning'].includes(t.type)">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01"/>
                            </svg>
                        </template>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="font-medium" x-text="t.title || (t.type === 'success' ? 'Success' : 'Notice')"></p>
                        <p class="text-sm opacity-90" x-text="t.body"></p>
                    </div>
                    <div class="ml-3 text-white/80">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>