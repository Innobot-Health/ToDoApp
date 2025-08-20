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

    public function create(array $data, $image = null)
    {
        $task = Task::create($data);

        if ($image) {
            $path = $image->store('tasks', 'public');
            $task->images()->create(['path' => $path]);
        }

        return $task->load('images');
    }

    /* public function update(Task $task, array $data, $image = null)
    {
        $task->update($data);

        if ($image) {
            foreach ($task->images as $img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            }

            $path = $image->store('tasks', 'public');
            $task->images()->create(['path' => $path]);
        }

        return $task->load('images');
    } */

    public function updateImage(Task $task, array $data, $image)
    {
        $task->update($data);

        $path = $image->store('tasks', 'public');

        if ($task->images()->exists()) {
            $img = $task->images()->first();
            if (Storage::disk('public')->exists($img->path)) {
                Storage::disk('public')->delete($img->path);
            }
            $img->update(['path' => $path]);
        } else {
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