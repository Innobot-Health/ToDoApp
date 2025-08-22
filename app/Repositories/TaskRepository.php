<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class TaskRepository implements TaskRepositoryInterface
{
    public function all($user)
    {
        if ($user->role === 'admin') {
            return Task::with('images', 'user')
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhereHas('user', fn($q) => $q->where('role', '!=', 'admin'));
                })
                ->latest()
                ->get();
        }
        return Task::with('images')->where('user_id', $user->id)->latest()->get();
    }

    public function find($id)
    {
        return Task::with('images')->find($id);
    }

    public function create(array $data, $images = [])
    {
        $task = Task::create($data);

        if (!empty($images)) {
            foreach ($images as $image) {
                $path = $image->store('tasks', 'public');
                $task->images()->create(['path' => $path]);
            }
        }

        return $task->load('images');
    }

    public function update(Task $task, array $data, $images = [])
    {
        $task->update($data);

        if (!empty($images)) {
            // delete old ones
            foreach ($task->images as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }

            // add new ones
            foreach ($images as $image) {
                $path = $image->store('tasks', 'public');
                $task->images()->create(['path' => $path]);
            }
        }

        return $task->load('images');
    }

    public function updateImages(Task $task, array $data, $images = [])
    {
        $task->update($data);

        // delete old
        foreach ($task->images as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }

        // add new
        foreach ($images as $image) {
            $path = $image->store('tasks', 'public');
            $task->images()->create(['path' => $path]);
        }

        return $task->load('images');
    }

    public function delete(Task $task)
    {
        foreach ($task->images as $img) {
            Storage::disk('public')->delete($img->path);
            $img->delete();
        }

        $task->delete();
    }
}