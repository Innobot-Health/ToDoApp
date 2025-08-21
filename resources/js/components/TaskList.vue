<template>
  <div class="task-container">

    <!-- Show Update Password if toggled -->
    <UpdatePassword 
      v-if="showUpdatePassword" 
      @back-to-tasks="showUpdatePassword = false" 
    />

    <!-- Otherwise show normal Task List -->
    <div v-else>
      <!-- Header Section -->
      <div class="task-header-container">
        <header class="task-header">
          <h2>Task List</h2>
          <button @click="logout" class="logout-btn">Logout</button>
          <button @click="showUpdatePassword = true" class="update-password-btn">
            Change Password
          </button>
          <p v-if="user.role === 'admin'" class="admin-note">
            Admin view: You can delete any task
          </p>
        </header>
      </div>

      <!-- Form Section -->
      <div class="task-form-container">
        <form @submit.prevent="addTask" class="task-form" enctype="multipart/form-data">
          <input type="text" v-model="newTask" placeholder="New task" required class="task-input" />
          <input type="file" @change="onFileChange" class="file-input" multiple />
          <button type="submit" class="add-btn">Add</button>
        </form>
      </div>

      <!-- Task List Section -->
      <div class="task-list-container">
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
            <input type="file" @change="onImageChange($event, task)" class="file-input" multiple />

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
    </div>
  </div>
</template>

<script>
import UpdatePassword from './UpdatePassword.vue'

export default {
  props: ['user'],
  components: { UpdatePassword },
  data() {
    return {
      tasks: [],
      newTask: '',
      newImages: [], // for create
      updateImages: {}, // for task-specific updates
      showUpdatePassword: false // 👈 NEW toggle for password update
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
      this.newImages = Array.from(e.target.files);
    },

    async addTask() {
      if (!this.newTask) return;

      const formData = new FormData();
      formData.append('title', this.newTask);

      this.newImages.forEach((file, index) => {
        formData.append(`images[${index}]`, file);
      });

      const response = await this.$axios.post('/api/tasks', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      this.fetchTasks();

      this.tasks.push(response.data);
      this.newTask = '';
      this.newImages = [];
    },

    async updateTitle(task) {
      const response = await this.$axios.put(`/api/tasks/${task.id}`, {
        title: task.title
      }, {
        headers: { 'Content-Type': 'application/json' }
      });

      const index = this.tasks.findIndex(t => t.id === task.id);
      this.tasks[index] = response.data;
    },

    async updateCompleted(task) {
      const response = await this.$axios.put(`/api/tasks/${task.id}`, {
        completed: task.completed
      }, {
        headers: { 'Content-Type': 'application/json' }
      });

      const index = this.tasks.findIndex(t => t.id === task.id);
      this.tasks[index] = response.data;
    },

    async onImageChange(event, task) {
      const files = Array.from(event.target.files);
      if (!files.length) return;

      const formData = new FormData();
      formData.append('title', task.title);
      formData.append('completed', task.completed ? 1 : 0);
      
      files.forEach((file, index) => {
        formData.append(`images[${index}]`, file);
      });

      const response = await this.$axios.post(`/api/tasks/${task.id}/image`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      }); 

      const index = this.tasks.findIndex(t => t.id === task.id);
      this.tasks[index] = response.data;
    },

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

<style>
.task-container {
  width: 90%;          /* Full width for the page */
  max-width: 800px;    /* Optional max width */
  height: auto;      /* Auto height to fit content */
  margin: 20px auto;
  padding: 20px;
  background: #fefefe;
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.task-header {
  display: block;
  margin-bottom: 20px;
}

.task-header h2 {
  margin: 0 0 10px 0; 
  font-size: 1.5rem;
}

.logout-btn {
  display: inline-block;
  margin-bottom: 10px;
  background: #f44336;
  color: #fff;
  border: none;
  padding: 6px 12px;
  border-radius: 5px;
  cursor: pointer;
}

.admin-note {
  display: block;
  font-size: 0.9rem;
  color: #555;
  margin-bottom: 10px;
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
  display: grid;
  grid-template-columns: 1fr; /* Single column, you can adjust */
  gap: 10px;
  list-style: none;
  padding: 0;
  margin: 0;
}

.task-item {
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border-bottom: 1px solid #eee;
}

.task-left {
  display: grid;
  grid-template-columns: auto 1fr;
  align-items: center;
  gap: 10px;
}

.task-title {
  width: 100%;
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
