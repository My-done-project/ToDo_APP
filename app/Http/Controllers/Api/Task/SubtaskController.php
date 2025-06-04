<?php

namespace App\Http\Controllers\Api\Task;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreSubtaskRequest;
use App\Http\Requests\Task\UpdateSubtaskRequest;
use App\Models\Subtask;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class SubtaskController extends Controller
{
     use ApiResponse;

    public function index(Task $task): JsonResponse
    {
        $this->authorizeTask($task);

        return $this->success($task->subtasks, 'Subtask list');
    }

    public function store(StoreSubtaskRequest $request, Task $task): JsonResponse
    {
        $this->authorizeTask($task);

        $subtask = $task->subtasks()->create($request->validated());

        return $this->success($subtask, 'Subtask created successfully', 201);
    }

    public function update(UpdateSubtaskRequest $request, Subtask $subtask): JsonResponse
    {
        $this->authorizeTask($subtask->task);

        $subtask->update($request->validated());

        return $this->success($subtask, 'Subtask updated successfully');
    }

    public function destroy(Subtask $subtask): JsonResponse
    {
        $this->authorizeTask($subtask->task);

        $subtask->delete();

        return $this->success(null, 'Subtask deleted successfully');
    }

    protected function authorizeTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
