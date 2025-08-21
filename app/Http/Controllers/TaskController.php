<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskImageRequest;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Http\Request;
use Exception;

class TaskController extends Controller
{
    protected $taskRepo;

    public function __construct(TaskRepositoryInterface $taskRepo)
    {
        $this->taskRepo = $taskRepo;
    }

    public function index()
    {
        $tasks = $this->taskRepo->all(auth()->user());
        return response()->json($tasks);
    }

    public function show($id)
    {
        $task = $this->taskRepo->find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);
        return response()->json($task);
    }

    public function store(StoreTaskRequest $request)
    {
        $task = $this->taskRepo->create(
            ['title' => $request->title, 'completed' => false, 'user_id' => auth()->id()],
            $request->file('images', []) // multiple images
        );

        dd($request->all(), $request->file('images'));

        return response()->json($task, 201);
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        $task = $this->taskRepo->find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);

        $task = $this->taskRepo->update(
            $task,
            $request->only(['title', 'completed']),
            $request->file('images', []) // multiple images
        );

        return response()->json($task);
    }

    public function updateImage(UpdateTaskImageRequest $request, $id)
    {
        $task = $this->taskRepo->find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);

        $task = $this->taskRepo->update(
            $task,
            $request->only(['title', 'completed']),
            $request->file('images', []) // multiple images
        );

        return response()->json($task);
    }

    public function destroy($id)
    {
        $task = $this->taskRepo->find($id);
        if (!$task) return response()->json(['message' => 'Task not found'], 404);

        $this->taskRepo->delete($task);
        return response()->json(['message' => 'Task deleted successfully']);
    }
}
