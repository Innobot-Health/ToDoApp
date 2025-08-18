<?php

namespace App\Repositories;

use App\Models\Task;
use App\Repositories\Interfaces\TaskRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class TaskRepository implements TaskRepositoryInterface
{
    public function all()
    {
        $user = Auth::user();

        if (!$user) {
            return collect(); // return empty collection if not authenticated
        }

        if ($user->role === 'admin') {
        return Task::where(function ($query) use ($user) {
                $query->where('user_id', $user->id) // admin's own tasks
                    ->orWhereHas('user', function ($q) {
                        $q->where('role', '!=', 'admin'); // normal users' tasks
                    });
            })
            ->latest()
            ->get();
        }

        return Task::where('user_id', $user->id)->latest()->get();
    }

    public function find($id)
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if ($user->role === 'admin') {
            return Task::find($id); // single model
        }

        return Task::where('id', $id)
                ->where('user_id', $user->id)
                ->first(); // make sure it's first(), not get()
    }

    public function create(array $data)
    {
        if (!isset($data['user_id'])) {
            $data['user_id'] = Auth::id(); // fallback to logged-in user
        }

        return Task::create($data);
    }

    public function update($id, array $data)
    {
        $task = $this->find($id);

        if (!$task) {
            return null;
        }

        $task->update($data);

        return $task->fresh(); // always returns the latest model
    }

    public function delete($id)
    {
        $task = $this->find($id);

        if (!$task) {
            return false;
        }

        return $task->delete(); // works now because $task is a model
    }

}
