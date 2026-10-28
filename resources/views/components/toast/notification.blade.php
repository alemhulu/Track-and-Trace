<div class="fixed inset-x-0 top-0 z-[60] px-4 py-6 pointer-events-none sm:p-6 space-y-3">
    <div x-data="{ show: false, messages: [], 
      remove(message) {this.messages.splice(this.messages.indexOf(message), 1)},}"
        x-on:alert.window="show = true;  let message =$event.detail; messages.push(message); setTimeout(() => { remove(message)}, 4950)"
        aria-live="">
        <div x-show="show" x-transition:enter="transition ease-in duration-300"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-out duration-300" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90" class="flex flex-col-reverse items-end w-full space-y-3">
            <template x-for="(message, messageIndex) in messages" :key="messageIndex">
                <div class="w-full flex items-center space-y-4 sm:items-end flex-row-reverse">
                    <div :class="{
             'bg-rose-600 shadow-rose-900/30': message.type === 'error',
             'bg-emerald-600 shadow-emerald-900/30': message.type === 'success',
             'bg-brand-600 shadow-brand-900/30': message.type === 'info',
             'bg-amber-500 shadow-amber-900/30': message.type === 'warning',
             }" class="relative w-full max-w-sm overflow-hidden shadow-xl pointer-events-auto rounded-xl ring-1 ring-black/5">
                        <div class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 flex">
                                    <i :class="{'fi-rr-shield-exclamation' : message.type === 'error',
                        'fi-rr-shield-check' : message.type === 'success',
                        'fi-rr-info' : message.type === 'info',
                        'fi-rr-exclamation' : message.type === 'warning'}"
                                        class="flex text-xl text-white fi"></i>
                                </div>

                                <div class="ml-3 w-0 flex-1 ">
                                    <p x-text="message.message" class="text-sm font-medium text-white"></p>
                                </div>

                                <div class="ml-4 mr-2 flex-shrink-0 flex py-auto ">
                                    <button @click="remove(message)"
                                        class="rounded-md inline-flex text-gray-200 hover:text-gray-50 focus:outline-none">
                                        <span class="sr-only">Close</span>
                                        <i class="fi fi-rr-cross flex text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <span class="absolute inset-x-0 bottom-0 h-1 bg-white/40 animate-life"></span>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>