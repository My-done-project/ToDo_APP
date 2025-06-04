<?php

namespace App\Http\Controllers\Api\Task;

use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\RescheduleTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Exports\TaskExport;
use App\Imports\TaskImport;
use Maatwebsite\Excel\Facades\Excel;

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

    public function progress(Task $task): JsonResponse
    {
        $this->authorizeTask($task);

        $totalSubtasks = $task->subtasks()->count();
        $doneSubtasks  = $task->subtasks()->where('is_done', true)->count();

        $progress = $totalSubtasks > 0 ? round(($doneSubtasks / $totalSubtasks) * 100, 2) : 0;

        return $this->success([
            'task_id'         => $task->id,
            'title'           => $task->title,
            'progress'        => $progress,
            'total_subtasks'  => $totalSubtasks,
            'done_subtasks'   => $doneSubtasks,
            'created_at'      => $task->created_at,
            'updated_at'      => $task->updated_at,
            'status'          => $task->status,
        ], 'Task progress');
    }

    public function calendarView(): JsonResponse
    {
        $tasks = auth()->user()->tasks()
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->get();

        return $this->success($tasks, 'Task list by date');
    }

    public function reschedule(RescheduleTaskRequest $request, Task $task): JsonResponse
    {
        $this->authorizeTask($task);

        $task->due_date = $request->due_date;
        $task->save();

        return $this->success($task, 'Task rescheduled successfully');
    }

    public function statistics(): JsonResponse
    {
        $user = auth()->user();

        $totalTasks     = $user->tasks()->count();
        $completedTasks = $user->tasks()->where('status', 'Done')->count();

        $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;

        $todayTasks  = $user->tasks()->whereDate('created_at', today())->count();
        $weekTasks   = $user->tasks()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $monthTasks  = $user->tasks()->whereMonth('created_at', now()->month)->count();

        return $this->success([
            'total_tasks'       => $totalTasks,
            'completed_tasks'   => $completedTasks,
            'completion_rate'   => $completionRate,
            'task_today'        => $todayTasks,
            'task_this_week'    => $weekTasks,
            'task_this_month'   => $monthTasks,
        ], 'Task statistics');
    }

    public function search(Request $request): JsonResponse
    {
        $query = auth()->user()->tasks();

        // Search by title or notes
        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                ->orWhere('notes', 'like', "%$search%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by priority
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        // Filter by due date range
        if ($from = $request->get('from_date')) {
            $query->whereDate('due_date', '>=', $from);
        }
        if ($to = $request->get('to_date')) {
            $query->whereDate('due_date', '<=', $to);
        }

        $tasks = $query->orderBy('due_date')->get();

        return $this->success($tasks, 'Filtered task list');
    }

    public function export()
    {
        return Excel::download(new TaskExport, 'tasks.csv');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        Excel::import(new TaskImport, $request->file('file'));

        return $this->success(null, 'Task import successful');
    }

}
