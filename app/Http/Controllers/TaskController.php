<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Exception;

class TaskController extends Controller
{
    protected $tasks;

    public function __construct(TaskRepositoryInterface $tasks)
    {
        $this->tasks = $tasks;
    }

    public function index()
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

            return response()->json($this->tasks->all());
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch tasks', 'details' => $e->getMessage()], 500);
        }
    }

    public function store(StoreTaskRequest $request)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

            $task = $this->tasks->create([
                'title'   => $request->title,
                'user_id' => $user->id
            ]);

            return response()->json($task, 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create task', 'details' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

            $task = $this->tasks->find($id);

            if (!$task) return response()->json(['message' => 'Task not found'], 404);

            return response()->json($task);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to fetch task', 'details' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

            $task = $this->tasks->find($id);
            if (!$task) return response()->json(['message' => 'Task not found'], 404);

            if (Gate::denies('update', $task)) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $updatedTask = $this->tasks->update($id, $request->only(['title', 'completed']));

            return response()->json($updatedTask);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update task', 'details' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = Auth::user();
            if (!$user) return response()->json(['message' => 'Unauthenticated'], 401);

            $task = $this->tasks->find($id);
            if (!$task) return response()->json(['message' => 'Task not found'], 404);

            if ($task->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $this->tasks->delete($id);

            return response()->json(['message' => 'Task deleted']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to delete task', 'details' => $e->getMessage()], 500);
        }
    }
}
