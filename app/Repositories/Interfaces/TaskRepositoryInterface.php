<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;

interface TaskRepositoryInterface
{
    public function all($user);
    public function find($id);
    public function create(array $data, $image = null);
    // public function update(Task $task, array $data, $image = null);
    public function updateImage(Task $task, array $data, $image);
    public function delete(Task $task);
}
