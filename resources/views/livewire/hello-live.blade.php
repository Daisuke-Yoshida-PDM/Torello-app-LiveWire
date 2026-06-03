<div class="p-6 space-y-3">
    <p class="text-lg">現在のカウント: <span class="font-bold">{{ $count }}</span></p>

    <button
        type="button"
        wire:click="increment"
        class="px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700"
    >
        +1
    </button>
</div>