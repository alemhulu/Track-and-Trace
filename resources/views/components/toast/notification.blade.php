<div class="fixed inset-0 px-4 py-6 pointer-events-none sm:p-6 sm:items-start z-50 space-y-3 h-screen w-screen">
    <div x-data="{ show: false, messages: [], 
      remove(message) {this.messages.splice(this.messages.indexOf(message), 1)},}"
        x-on:alert.window="show = true;  let message =$event.detail; messages.push(message); setTimeout(() => { remove(message)}, 3500)"
        aria-live="assertive">
        <div x-show="show" x-transition:enter="transition ease-in duration-300"
            x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-out duration-300" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90" class="flex flex-col-reverse items-end w-full space-y-3">
            <template x-for="(message, messageIndex) in messages" :key="messageIndex">
                <div class="w-full flex items-center space-y-4 sm:items-end flex-row-reverse">
                    <div :class="{
             'bg-red-700/90 shadow-red-800/60  ring-red-900 hover:bg-red-700': message.type === 'error',
             'bg-green-700/90 shadow-green-800/60  ring-green-900 hover:bg-green-700': message.type === 'success',
             'bg-blue-500/90 shadow-blue-800/60  ring-blue-900 hover:bg-blue-500': message.type === 'info',
             'bg-yellow-600/90 shadow-yellow-800/60  ring-yellow-900 hover:bg-yellow-600': message.type === 'warning',
             }" class="max-w-xs w-full shadow-lg rounded-md pointer-events-auto ring-1 ring-opacity-5 overflow-hidden backdrop-blur-sm">
                        <div class=" px-2 py-3">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 flex">
                                    <i :class="{'fi-rr-shield-exclamation text-2xl' : message.type === 'error', 
                        'fi-rr-shield-check text-2xl' : message.type === 'success'}"
                                        class="flex text-white py-auto fi "></i>
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
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>