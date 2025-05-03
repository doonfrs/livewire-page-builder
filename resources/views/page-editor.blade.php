<div>
    <div class="flex items-center justify-between bg-gray-200 shadow-md p-3 text-gray-900">
        <div class="flex gap-2">
            <button class="w-8 h-8 p-1 hover:bg-gray-300 flex items-center justify-center" wire:click="addRow">
                <x-heroicon-o-plus />
            </button>
            <button class="w-8 h-8 p-1 hover:bg-gray-300 flex items-center justify-center" wire:click="$dispatch('save-page')">
                <x-heroicon-o-check />
            </button>
        </div>
    </div>

    <!-- Modal for Adding Block -->
    @if($showBlockModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl p-8 relative">
            <button wire:click="closeBlockModal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-700">
                <x-heroicon-o-x-mark class="w-6 h-6" />
            </button>
            <h2 class="text-xl font-bold mb-6 text-gray-800 flex items-center gap-2">
                <x-heroicon-o-plus class="w-6 h-6 text-pink-500" />
                Add Block
            </h2>
            <input type="text" wire:model="blockFilter" placeholder="Search blocks..." class="w-full border rounded-lg px-4 py-2 mb-6 focus:ring-2 focus:ring-pink-200 focus:border-pink-400 transition" />
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @forelse($this->filteredBlocks as $block)
                <button
                    wire:click="addBlockToModalRow('{{ $block['alias'] }}')"
                    class="group bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 p-5 flex flex-col items-center text-center focus:outline-none focus:ring-2 focus:ring-pink-200">
                    <x-dynamic-component :component="$block['icon'] ?? 'heroicon-o-cube'" class="w-10 h-10 mb-3 text-pink-500 group-hover:text-pink-600 transition-colors" />
                    <div class="font-semibold text-gray-800 mb-1 text-base">
                        {{ $block['label'] }}
                    </div>
                </button>
                @empty
                <div class="col-span-2 text-gray-400 text-center py-8">No blocks found.</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <div class="flex flex-1 overflow-hidden">
        <main id="main" class="flex-1 pt-10 p-6 pr-80 bg-gray-50 overflow-auto">
            @foreach($rows as $rowId=>$row)
            <livewire:row-block 
            :blocks="$row['blocks']"
            :rowId="$rowId"
            :properties="$row['properties']"
            :key="$rowId" />
            @endforeach
        </main>
    </div>

    @livewire('block-properties')

    {{--
        Tailwind safelist:
        col-span-1 col-span-2 col-span-3 col-span-4 col-span-5 col-span-6 col-span-7 col-span-8 col-span-9 col-span-10 col-span-11 col-span-12
        md:col-span-1 md:col-span-2 md:col-span-3 md:col-span-4 md:col-span-5 md:col-span-6 md:col-span-7 md:col-span-8 md:col-span-9 md:col-span-10 md:col-span-11 md:col-span-12
        lg:col-span-1 lg:col-span-2 lg:col-span-3 lg:col-span-4 lg:col-span-5 lg:col-span-6 lg:col-span-7 lg:col-span-8 lg:col-span-9 lg:col-span-10 lg:col-span-11 lg:col-span-12
    --}}
</div>