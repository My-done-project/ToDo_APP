<?php

namespace App\Http\Controllers\Api\Task;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;

class TaskController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        $tasks = auth()->user()->tasks()->latest()->get();
        return $this->success($tasks, 'Task list');
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }

        $task = Task::create($data);
        return $this->success($task, 'Task created successfully', 201);
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorizeTask($task);
        return $this->success($task, 'Task detail');
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorizeTask($task);

        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            if ($task->attachment && Storage::disk('public')->exists($task->attachment)) {
                Storage::disk('public')->delete($task->attachment);
            }
            $data['attachment'] = $request->file('attachment')->store('attachments', 'public');
        }


        $task->update($data);
        return $this->success($task, 'Task updated successfully');
    }

    public function destroy(Task $task): JsonResponse
    {
        $this->authorizeTask($task);
        $task->delete();
        return $this->success(null, 'Task deleted successfully');
    }

    protected function authorizeTask(Task $task)
    {
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        $this->authorizeTask($task);

        $task->status = $request->status;
        $task->save();

        return $this->success($task, 'Task status updated successfully');
    }
}
