<template>
  <div id="app">
    <!-- Show Register if not logged in and toggle enabled -->
    <Register 
      v-if="!loggedIn && showRegister" 
      @login-success="handleLoginSuccess" 
    />

    <div v-else-if="!loggedIn && !showRegister">
      <h2>Login</h2>
      <form @submit.prevent="login">
        <input type="email" v-model="email" placeholder="Email" required />
        <input type="password" v-model="password" placeholder="Password" required />
        <button type="submit">Login</button>
      </form>
      <p v-if="error">{{ error }}</p>
      <button @click="toggleForm">
        {{ showRegister ? 'Go to Login' : 'Go to Register' }}
      </button>
    </div>

    <TaskList 
      v-else 
      :user="user" 
      @logout="handleLogout" 
    />

    <!-- Show TaskList if logged in -->
    <TaskList 
      v-else 
      :user="user" 
      @logout="handleLogout" 
    />

  </div>
</template>

<script>
import Register from './Register.vue'
import TaskList from './TaskList.vue'

export default {
  components: { Register, TaskList },
  data() {
    return {
      loggedIn: false,
      user: null,
      showRegister: false,
      email: '',
      password: '',
      error: ''
    }
  },
  methods: {
    async login() {
      try {
        const response = await this.$axios.post('/api/login', {
          email: this.email,
          password: this.password
        })

        const userData = response.data.user
        const token = response.data.token

        // Save token & user in localStorage
        localStorage.setItem('token', token)
        localStorage.setItem('user', JSON.stringify(userData))

        // Set default Axios auth header
        this.$axios.defaults.headers.common['Authorization'] = 'Bearer ' + token

        // Update Pinia store / state
        this.handleLoginSuccess(userData)
      } catch (err) {
        this.error = 'Invalid credentials'
      }
    },
    handleLoginSuccess(userData) {
      this.loggedIn = true
      this.user = userData
    },
    handleLogout() {
      this.loggedIn = false
      this.user = null
      localStorage.removeItem('token')
    },
    toggleForm() {
      this.showRegister = !this.showRegister
    }
  }
}
</script>

<style>
#app {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100vh;
  background-color: #f0f4f8;
  font-family: Arial, sans-serif;
}

h2 {
  margin-bottom: 20px;
  color: #333;
}

form {
  display: flex;
  flex-direction: column;
  width: 300px;
  padding: 30px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

input {
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
}

input:focus {
  border-color: #007BFF;
  outline: none;
}

button {
  padding: 10px;
  background-color: #007BFF;
  border: none;
  color: white;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #0056b3;
}

p {
  margin-top: 10px;
  color: red;
  font-size: 14px;
}
</style>