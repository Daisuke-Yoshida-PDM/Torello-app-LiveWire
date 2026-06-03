<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Trello Board</h1>

    <div class="flex gap-4 overflow-x-auto pb-4">
        @foreach ($statuses as $status)
            <div class="w-72 shrink-0 bg-gray-100 rounded-lg p-3" data-status-id="{{ $status->id }}">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-700">
                        {{ $status->name }}
                    </h2>
                    <span class="text-sm text-gray-500">
                        {{ $status->tasks->count() }}
                    </span>
                </div>

                <div class="space-y-2 min-h-[40px]">
                    @forelse ($status->tasks as $task)
                        <div class="bg-white shadow rounded p-3 group" data-task-id="{{ $task->id }}"
                            wire:key="task-{{ $task->id }}">
                            <button type="button" wire:click="startEdit({{ $task->id }})" class="w-full text-left">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $task->title }}
                                </p>
                                @if ($task->description)
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ \Illuminate\Support\Str::limit($task->description, 60) }}
                                    </p>
                                @endif
                            </button>

                            <button type="button" wire:click="deleteTask({{ $task->id }})"
                                wire:confirm="このタスクを削除しますか？"
                                class="mt-2 text-xs text-red-500 opacity-0 group-hover:opacity-100">
                                削除
                            </button>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400">
                            まだタスクがありません
                        </p>
                    @endforelse
                </div>

                {{-- 新規追加フォーム --}}
                @if ($showFormForStatus === $status->id)
                    <div class="mt-3 bg-white rounded p-3 space-y-2">
                        <input type="text" wire:model="newTaskTitle" placeholder="タイトルを入力"
                            class="w-full border rounded px-2 py-1 text-sm" autofocus />
                        @error('newTaskTitle')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <div class="flex gap-2">
                            <button type="button" wire:click="addTask({{ $status->id }})"
                                class="px-3 py-1 bg-cyan-600 text-white text-sm rounded hover:bg-cyan-700">
                                追加
                            </button>
                            <button type="button" wire:click="cancelForm"
                                class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                                キャンセル
                            </button>
                        </div>
                    </div>
                @else
                    <button type="button" wire:click="openForm({{ $status->id }})"
                        class="mt-3 w-full text-left text-sm text-gray-500 hover:text-gray-700">
                        + 新規タスク
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    {{-- 編集モーダル --}}
    @if ($editingTaskId)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50" wire:click.self="cancelEdit">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 space-y-4">
                <h3 class="text-lg font-semibold text-gray-800">タスクを編集</h3>

                <div class="space-y-1">
                    <label class="text-xs text-gray-600">タイトル</label>
                    <input type="text" wire:model="editingTitle" class="w-full border rounded px-3 py-2 text-sm" />
                    @error('editingTitle')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="text-xs text-gray-600">説明（任意）</label>
                    <textarea wire:model="editingDescription" rows="4" class="w-full border rounded px-3 py-2 text-sm"></textarea>
                    @error('editingDescription')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="cancelEdit"
                        class="px-3 py-1 bg-gray-200 text-gray-700 text-sm rounded hover:bg-gray-300">
                        キャンセル
                    </button>
                    <button type="button" wire:click="saveEdit"
                        class="px-3 py-1 bg-cyan-600 text-white text-sm rounded hover:bg-cyan-700">
                        保存
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
@assets
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.7/Sortable.min.js"></script>
@endassets

@script
    <script>
        const initSortable = () => {
            document.querySelectorAll('[data-status-id]').forEach((column) => {
                // 既に初期化済みのカラムはスキップ
                if (column.dataset.sortableInitialized === 'true') return;

                // カラム直下の「カード集合エリア」を取得（カラム自体ではなく、カード群を入れているdivに対して有効化する）
                const cardZone = column.querySelector('.space-y-2');
                if (!cardZone) return;

                new Sortable(cardZone, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd(evt) {
                        const taskId = Number(evt.item.dataset.taskId);
                        const newStatusId = Number(evt.to.closest('[data-status-id]').dataset.statusId);

                        if (!taskId || !newStatusId) return;

                        // Livewireに通知（Livewire 3 系: グローバル Livewire.dispatch）
                        Livewire.dispatch('task-moved', {
                            taskId: taskId,
                            newStatusId: newStatusId,
                        });
                    },
                });

                column.dataset.sortableInitialized = 'true';
            });
        }

        initSortable();

        // Livewireの再描画でDOMが入れ替わった後にも、再初期化を走らせる
        Livewire.hook('morph.updated', () => {
            initSortable();
        });
    </script>
@endscript
</div>
