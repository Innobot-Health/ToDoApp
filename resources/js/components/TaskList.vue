<template>
  <div class="task-container">
    <header class="task-header">
      <h2>Task List</h2>
      <button @click="logout" class="logout-btn">Logout</button>
      <p v-if="user.role === 'admin'" class="admin-note">
        Admin view: You can delete any task
      </p>
    </header>

    <!-- Form to add new task -->
    <form @submit.prevent="addTask" class="task-form" enctype="multipart/form-data">
      <input type="text" v-model="newTask" placeholder="New task" required class="task-input" />
      <input type="file" @change="onFileChange" class="file-input" />
      <button type="submit" class="add-btn">Add</button>
    </form>

    <!-- Task List -->
    <ul class="task-list">
      <li v-for="task in tasks" :key="task.id" class="task-item">
        <div class="task-left">
          <input type="checkbox" v-model="task.completed" @change="updateCompleted(task)" />
          <input 
            type="text" 
            v-model="task.title" 
            @blur="updateTitle(task)" 
            @keyup.enter="updateTitle(task)"
            class="task-title"
          />
        </div>

        <!-- Task Images -->
        <div class="task-images" v-if="task.images.length">
          <img
            v-for="image in task.images"
            :key="image.id"
            :src="getImageUrl(image.path)"
            :alt="image.path"
            class="task-image"
          />
        </div>

        <!-- Upload new image -->
        <input type="file" @change="onImageChange($event, task)" class="file-input" />

        <!-- Delete Button -->
        <button 
          v-if="user.role === 'admin' || task.user_id === user.id" 
          @click="deleteTask(task.id)"
          class="delete-btn">
          Delete
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  props: ['user'],
  data() {
    return {
      tasks: [],
      newTask: '',
      newImage: null, // for create
      updateImages: {} // for task-specific updates
    };
  },
  created() {
    const token = localStorage.getItem('token');
    if (token) {
      this.$axios.defaults.headers.common['Authorization'] = 'Bearer ' + token;
    }
    this.fetchTasks();
  },
  methods: {
    async fetchTasks() {
      const response = await this.$axios.get('/api/tasks');
      this.tasks = response.data;
    },

    // Handle file selection for new task
    onFileChange(e) {
      this.newImage = e.target.files[0];
    },

    /* Handle file selection for updating existing task
    onUpdateFileChange(e, task) {
      this.$set(this.updateImages, task.id, e.target.files[0]);
    }, */

    async addTask() {
      const formData = new FormData();
      formData.append('title', this.newTask);
      if (this.newImage) {
        formData.append('image', this.newImage);
      }

      const response = await this.$axios.post('/api/tasks', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      this.tasks.push(response.data);
      this.newTask = '';
      this.newImage = null;
    },

    async updateTitle(task) {
      const formData = new FormData();
      formData.append('title', task.title);

      const response = await this.$axios.put(`/api/tasks/${task.id}`, formData, {
        headers: { 'Content-Type': 'application/json' }
      });

      // Update local task
      const index = this.tasks.findIndex(t => t.id === task.id);
      this.tasks[index] = response.data;
    },

    async updateCompleted(task) {
      const response = await this.$axios.put(`/api/tasks/${task.id}`, {
        completed: task.completed
      }, {
        headers: { 'Content-Type': 'application/json' }
      });

      // Update local task
      const index = this.tasks.findIndex(t => t.id === task.id);
      this.tasks[index] = response.data;
    },

    async onImageChange(event, task) {
      const file = event.target.files[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('title', task.title);
      formData.append('completed', task.completed ? 1 : 0);
      formData.append('image', file);

      const response = await this.$axios.post(`/api/tasks/${task.id}/image`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      // Update local task with new image
      const index = this.tasks.findIndex(t => t.id === task.id);
      this.tasks[index] = response.data;
    },

    /* async updateTask(task) {
      const formData = new FormData();
      formData.append('title', task.title);
      formData.append('completed', task.completed ? 1 : 0);

      if (this.updateImages[task.id]) {
        formData.append('image', this.updateImages[task.id]);
      }

      const response = await this.$axios.put(`/api/tasks/${task.id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });

      const index = this.tasks.findIndex(t => t.id === task.id);
      this.tasks[index] = response.data;

      this.$delete(this.updateImages, task.id);
    }, */

    async deleteTask(id) {
      await this.$axios.delete(`/api/tasks/${id}`);
      this.tasks = this.tasks.filter(t => t.id !== id);
    },

    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('user');
      this.$emit('logout');
    },

    getImageUrl(path) {
      return `${this.$axios.defaults.baseURL}/storage/${path}`;
    }
  }
};
</script>

<style scoped>
.task-container {
  max-width: 700px;
  margin: 20px auto;
  padding: 20px;
  background: #fefefe;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.task-header {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  margin-bottom: 20px;
}

.logout-btn {
  margin-top: 10px;
  background: #f44336;
  color: #fff;
  border: none;
  padding: 6px 12px;
  border-radius: 5px;
  cursor: pointer;
}

.admin-note {
  margin-top: 8px;
  font-size: 0.9rem;
  color: #555;
}

.task-form {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
}

.task-input {
  flex: 1;
  padding: 8px 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.add-btn {
  background: #4caf50;
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 5px;
  cursor: pointer;
}

.task-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.task-item {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  padding: 10px;
  border-bottom: 1px solid #eee;
}

.task-left {
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
}

.task-title {
  border: none;
  background: transparent;
  font-size: 1rem;
  flex: 1;
  outline: none;
}

.task-images {
  display: flex;
  gap: 5px;
  margin: 5px 0;
}

.task-image {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 5px;
  border: 1px solid #ccc;
}

.file-input {
  margin-right: 10px;
}

.delete-btn {
  background: #f44336;
  color: #fff;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
}
</style>
