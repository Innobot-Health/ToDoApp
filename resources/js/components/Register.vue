<template>
  <div id="app">
    <!-- Show Register if not logged in and toggle enabled -->
    <div v-if="!loggedIn && showRegister">
      <h2>Register</h2>
      <form @submit.prevent="register">
        <input type="text" v-model="name" placeholder="Name" required />
        <input type="email" v-model="email" placeholder="Email" required />
        <input type="password" v-model="password" placeholder="Password" required />
        <input type="password" v-model="password_confirmation" placeholder="Confirm Password" required />

        <select v-model="role" required>
          <option disabled value="">Select role</option>
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>

        <button type="submit">Register</button>
      </form>

      <p v-if="error" style="color:red;">{{ error }}</p>
      <p v-if="success" style="color:green;">{{ success }}</p>

      <!-- Toggle button -->
      <button v-if="!loggedIn" @click="toggleForm">
        {{ showRegister ? 'Go to Login' : 'Go to Register' }}
      </button>
    </div>

    <!-- Show Login if not logged in -->
    <div v-else-if="!loggedIn">
      <h2>Login</h2>
      <form @submit.prevent="login">
        <input type="email" v-model="email" placeholder="Email" required />
        <input type="password" v-model="password" placeholder="Password" required />
        <button type="submit">Login</button>
      </form>
      <p v-if="error" style="color:red;">{{ error }}</p>

      <!-- Toggle button -->
      <button v-if="!loggedIn" @click="toggleForm">
        {{ showRegister ? 'Go to Login' : 'Go to Register' }}
      </button>
    </div>

    <!-- Show TaskList if logged in -->
    <TaskList 
      v-else 
      :user="user" 
      @logout="handleLogout" 
    />
  </div>
</template>

<script>
import TaskList from './TaskList.vue'

export default {
  components: { TaskList },
  data() {
    return {
      loggedIn: false,
      user: null,
      showRegister: false,

      // shared form fields
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      role: 'user',

      // messages
      error: '',
      success: ''
    }
  },
  methods: {
    async login() {
      this.error = ''
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
    async register() {
      this.error = ''
      this.success = ''
      try {
        const response = await this.$axios.post('/api/register', {
          name: this.name,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation,
          role: this.role
        })

        // show success
        this.success = 'Registration successful! Please log in.'
        // reset form
        this.name = ''
        this.email = ''
        this.password = ''
        this.password_confirmation = ''
        this.role = 'user'
        // flip back to login form
        this.showRegister = false
      } catch (err) {
        if (err.response && err.response.data) {
          const data = err.response.data
          if (data.errors) {
            this.error = Object.values(data.errors).flat().join(', ')
          } else if (data.message) {
            this.error = data.message
          } else {
            this.error = 'Registration failed'
          }
        } else {
          this.error = 'Registration failed'
        }
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
      this.error = ''
      this.success = ''
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
  min-height: 100vh;
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
  width: 320px;
  padding: 30px;
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

input, select {
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 14px;
}

input:focus, select:focus {
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
  font-size: 14px;
}
</style>
