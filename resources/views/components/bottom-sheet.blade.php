@props(['name', 'maxHeight' => '80vh'])

<div 
    x-data="{ open: false }"
    x-on:open-{{ $name }}.window="open = true"
    x-on:close-{{ $name }}.window="open = false"
    x-on:keydown.escape.window="open = false"
>
    {{-- Backdrop --}}
    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-40"
        @click="open = false"
    ></div>

    {{-- Sheet --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-2xl shadow-2xl overflow-hidden"
        style="max-height: {{ $maxHeight }}"
    >
        {{-- Handle --}}
        <div class="flex justify-center py-2">
            <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
        </div>
        
        {{-- Content --}}
        <div class="overflow-y-auto px-4 pb-8" style="max-height: calc({{ $maxHeight }} - 40px)">
            {{ $slot }}
        </div>
    </div>
</div>
