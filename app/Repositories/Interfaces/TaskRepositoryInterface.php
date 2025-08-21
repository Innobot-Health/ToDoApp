<?php

namespace App\Repositories\Interfaces;

use App\Models\Task;

interface TaskRepositoryInterface
{
    public function all($user);
    public function find($id);
    public function create(array $data, array $images = []);
    public function update(Task $task, array $data, array $images = []);
    public function updateImages(Task $task, array $data, array $images = []);
    public function delete(Task $task);
}
