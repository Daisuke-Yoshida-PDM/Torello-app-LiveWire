<?php

namespace App\Livewire;

use App\Models\Status;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Board extends Component
{
    // ----- 新規追加フォーム用 -----
    public ?int $showFormForStatus = null;

    #[Validate('required|string|max:100')]
    public string $newTaskTitle = '';

    // ----- 編集モーダル用 -----
    public ?int $editingTaskId = null;

    #[Validate('required|string|max:100')]
    public string $editingTitle = '';

    #[Validate('nullable|string|max:1000')]
    public ?string $editingDescription = null;

    #[Validate('nullable|date')]
    public ?string $editingStartDate = null;

    #[Validate('nullable|date|after_or_equal:editingStartDate')]
    public ?string $editingDueDate = null;

    // ----- アクション -----
    public function openForm(int $statusId): void
    {
        $this->showFormForStatus = $statusId;
        $this->newTaskTitle = '';
        $this->resetErrorBag('newTaskTitle');
    }

    public function cancelForm():void
    {
        $this->showFormForStatus = null;
        $this->newTaskTitle = '';
    }

    public function addTask(int $statusId):void
    {
        $this->validateOnly('newTaskTitle');

        //現在このステータスにある、一番大きい position の数値を調べる(なにもなければnull)
        $maxPosition = Task::where('user_id', Auth::id())
                            ->where('status_id', $statusId)
                            ->max('position');

        $nextPosition = $maxPosition !== null ? $maxPosition+1 : 0;

        //計算した position を含めて、新しくタスクを作成する
        Task::create([
            'title' => $this->newTaskTitle,
            'user_id' => Auth::id(),
            'status_id' => $statusId,
            'position' => $nextPosition,
        ]);

        $this->newTaskTitle = '';
        $this->showFormForStatus = null;
    }

    public function startEdit(int $taskId):void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);

        $this->editingTaskId = $task->id;
        $this->editingTitle = $task->title;
        $this->editingDescription = $task->description;
        $this->editingStartDate = $task->start_date?->format('Y-m-d');
        $this->editingDueDate = $task->due_date?->format('Y-m-d');
    }

    public function cancelEdit():void
    {
        $this->editingTaskId = null;
        $this->editingTitle = '';
        $this->editingDescription = null;
        $this->editingStartDate = null;
        $this->editingDueDate = null;
    }

    public function saveEdit():void
    {
        $this->validateOnly('editingTitle');
        $this->validateOnly('editingDescription');
        $this->validateOnly('editingStartDate');
        $this->validateOnly('editingDueDate');

        if(! $this->editingTaskId) {
            return;
        }

        $task = Task::where('user_id', Auth::id())->findOrFail($this->editingTaskId);
        $task->update([
            'title' => $this->editingTitle,
            'description' => $this->editingDescription,
            'start_date' => $this->editingStartDate,
            'due_date' => $this->editingDueDate,
        ]);

        $this->cancelEdit();
    }

    public function deleteTask(int $taskId):void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $task->delete();
    }

    #[On('task-moved')]
    public function moveTask(int $taskId, int $newStatusId, array $positionIds = []):void
    {
        $task = Task::where('user_id', Auth::id())->find($taskId);

        if(! $task) {
            return;
        }

        $status = Status::find($newStatusId);

        if(! $status) {
            return;
        }

        DB::transaction(function () use ($task, $status, $positionIds) {
            $task->update([
                'status_id' => $status->id,
            ]);

            //届いたIDの並び順リスト（配列）を上から順番に処理する
            foreach ($positionIds as $index => $id) {
                Task::where('user_id', Auth::id())
                    ->where('id', $id)
                    ->update([
                        'position' => $index,
                    ]);
            }
        });
    }

    public function render()
    {
            $statuses = Status::query()
            ->with(['tasks' => function($query) {
                $query->where('user_id', Auth::id())
                //タスクID降順だったところをポジション昇順に変更
                      ->orderBy('position', 'asc');
            }])
            ->orderBy('id')
            ->get();

        return view('livewire.board', [
            'statuses' => $statuses
        ]);
    }
}
