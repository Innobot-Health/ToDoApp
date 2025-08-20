<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function unauthenticated_user_cannot_access_tasks()
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_see_all_tasks()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Task::factory()->count(3)->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    /** @test */
    public function regular_user_can_only_see_their_own_tasks()
    {
        $user = User::factory()->create(['role' => 'user']);
        Task::factory()->create(['user_id' => $user->id]);
        Task::factory()->create(); // task of another user

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }

    /** @test */
    public function user_can_create_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'title' => 'New Task'
        ]);

        $response->assertStatus(201)
                 ->assertJson(['title' => 'New Task', 'user_id' => $user->id]);

        $this->assertDatabaseHas('tasks', ['title' => 'New Task', 'user_id' => $user->id]);
    }

    /** @test */
    public function cannot_create_task_without_title()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', []);
        $response->assertStatus(422)
                 ->assertJsonValidationErrors('title');
    }

    /** @test */
    public function user_can_view_own_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/tasks/{$task->id}");
        $response->assertStatus(200)
                 ->assertJson(['id' => $task->id, 'user_id' => $user->id]);
    }

    /** @test */
    public function user_cannot_view_others_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(); // belongs to another user

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/tasks/{$task->id}");
        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_view_any_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/tasks/{$task->id}");
        $response->assertStatus(200)
                 ->assertJson(['id' => $task->id]);
    }

    /** @test */
    public function user_can_update_own_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Title',
            'completed' => true
        ]);

        $response->assertStatus(200)
                 ->assertJson(['title' => 'Updated Title', 'completed' => true]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated Title']);
    }

    /** @test */
    public function user_cannot_update_others_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(); // another user

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/tasks/{$task->id}", ['title' => 'Fail Update']);
        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_delete_own_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Task deleted']);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    /** @test */
    public function user_cannot_delete_others_task()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(); // another user

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_delete_any_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $task = Task::factory()->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Task deleted']);
    }

    /** @test */
    public function update_fails_with_invalid_data()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => '', // invalid: empty string
            'completed' => 'not_boolean' // invalid type
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'completed']);
    }

    /** @test */
    public function cannot_view_task_with_invalid_id()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks/9999'); // non-existent
        $response->assertStatus(404)
                ->assertJson(['message' => 'Task not found']);
    }

    /** @test */
    public function cannot_update_non_existing_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/tasks/9999', [
            'title' => 'New Title'
        ]);

        $response->assertStatus(404); // changed from 403 to 404
    }

    /** @test */
    public function cannot_delete_non_existing_task()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/tasks/9999');
        $response->assertStatus(404); // changed from 403 to 404
    }

    /** @test */
    public function cannot_create_task_when_unauthenticated()
    {
        $response = $this->postJson('/api/tasks', ['title' => 'Test']);
        $response->assertStatus(401);
    }

    /** @test */
    public function cannot_update_task_when_unauthenticated()
    {
        $task = Task::factory()->create();
        $response = $this->putJson("/api/tasks/{$task->id}", ['title' => 'Test']);
        $response->assertStatus(401);
    }

    /** @test */
    public function cannot_delete_task_when_unauthenticated()
    {
        $task = Task::factory()->create();
        $response = $this->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(401);
    }

}
