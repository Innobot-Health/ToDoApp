<template>
  <div>
    <h2>Task List</h2>
    <button @click="logout">Logout</button>

    <p v-if="user.role === 'admin'">Admin view: You can delete any task</p>

    <form @submit.prevent="addTask">
      <input type="text" v-model="newTask" placeholder="New task" required />
      <button type="submit">Add</button>
    </form>

    <ul>
      <li v-for="task in tasks" :key="task.id">
        <input type="checkbox" v-model="task.completed" @change="updateTask(task)" />

        <!-- Editable title -->
        <input 
          type="text" 
          v-model="task.title" 
          @blur="updateTask(task)" 
          @keyup.enter="updateTask(task)"
        />

        <button 
          v-if="user.role === 'admin' || task.user_id === user.id" 
          @click="deleteTask(task.id)">
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
      newTask: ''
    };
  },
  created() {
    // Ensure Axios always has the token when TaskList is loaded
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
    async addTask() {
      const response = await this.$axios.post('/api/tasks', { title: this.newTask });
      this.tasks.push(response.data);
      this.newTask = '';
    },
    async updateTask(task) {
      await this.$axios.put(`/api/tasks/${task.id}`, {
        title: task.title,
        completed: task.completed
      });
    },
    async deleteTask(id) {
      await this.$axios.delete(`/api/tasks/${id}`);
      this.tasks = this.tasks.filter(t => t.id !== id);
    },
    logout() {
      localStorage.removeItem('token');
      localStorage.removeItem('user'); // clear saved user
      this.$emit('logout');
    }
  }
};
</script>

<style>
div {
  display: flex;
  flex-direction: column;
  align-items: center;
  font-family: Arial, sans-serif;
  background-color: #f0f4f8;
  min-height: 100vh;
  padding: 20px;
}

h2 {
  color: #333;
  margin-bottom: 20px;
}

button {
  padding: 8px 16px;
  margin-bottom: 20px;
  background-color: #007BFF;
  border: none;
  color: white;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #0056b3;
}

.admin-note {
  margin-bottom: 15px;
  color: #555;
}

form {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

form input[type="text"] {
  flex: 1;
  padding: 10px;
  border-radius: 5px;
  border: 1px solid #ccc;
}

form input[type="text"]:focus {
  border-color: #007BFF;
  outline: none;
}

form button {
  padding: 10px 20px;
  background-color: #007BFF;
  border-radius: 5px;
  color: white;
  cursor: pointer;
}

form button:hover {
  background-color: #0056b3;
}

ul {
  list-style-type: none;
  padding: 0;
  width: 100%;
  max-width: 500px;
}

li {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

li input[type="text"] {
  flex: 1;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

li input[type="checkbox"] {
  transform: scale(1.2);
}

li button {
  padding: 6px 12px;
  border: none;
  border-radius: 5px;
  background-color: #ff4d4f;
  color: white;
  cursor: pointer;
}

li button:hover {
  background-color: #d9363e;
}

p {
  font-size: 14px;
}
</style>
