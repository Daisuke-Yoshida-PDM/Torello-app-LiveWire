<?php

namespace App\Livewire;

use App\Models\Status;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Board extends Component
{
    // ----- 新規追加フォーム用 -----
    public ?int $showFormForStatus = null;

    #[validate('required|string|max:100')]
    public string $newTaskTitle = '';

    // ----- 編集モーダル用 -----
    public ?int $editingTaskId = null;

    #[validate('required|string|max:100')]
    public string $editingTitle = '';

    #[validate('nullable|string|max:1000')]
    public ?string $editingDescription = null;

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

        Task::create([
            'title' => $this->newTaskTitle,
            'user_id' => Auth::id(),
            'status_id' => $statusId,
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
    }

    public function cancelEdit():void
    {
        $this->editingTaskId = null;
        $this->editingTitle = '';
        $this->editingDescription = null;
    }

    public function saveEdit():void
    {
        $this->validateOnly('editingTitle');
        $this->validateOnly('editingDescription');

        if(! $this->editingTaskId) {
            return;
        }

        $task = Task::where('user_id', Auth::id())->findOrFail($this->editingTaskId);
        $task->update([
            'title' => $this->editingTitle,
            'description' => $this->editingDescription,
        ]);

        $this->cancelEdit();
    }

    public function deleteTask(int $taskId):void
    {
        $task = Task::where('user_id', Auth::id())->findOrFail($taskId);
        $task->delete();
    }

    #[On('task-moved')]
    public function moveTask(int $taskId, int $newStatusId):void 
    {
        $task = Task::where('user_id', Auth::id())->find($taskId);

        if(! $task) {
            return;
        }

        $status = Status::find($newStatusId);

        if(! $status) {
            return;
        }

        $task->update([
            'status_id' => $status->id,
        ]);
    }

    public function render()
    {
            $statuses = Status::query()
            ->with(['tasks' => function($query) {
                $query->where('user_id', Auth::id())
                      ->orderByDesc('id');
            }])
            ->orderBy('id')
            ->get();

        return view('livewire.board', [
            'statuses' => $statuses
        ]);
    }
}
