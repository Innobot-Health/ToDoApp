<template>
  <div id="app">
    <!-- Register Screen -->
    <Register 
      v-if="!loggedIn && showRegister" 
      @login-success="handleLoginSuccess" 
    />

    <!-- Login Screen -->
    <Login 
      v-else-if="!loggedIn" 
      @login-success="handleLoginSuccess" 
    />

    <!-- Task List Screen -->
    <TaskList 
      v-else 
      :user="user" 
      @logout="handleLogout" 
    />
  </div>
</template>

<script>
import Login from './components/Login.vue'
import Register from './components/Register.vue'
import TaskList from './components/TaskList.vue'

export default {
  components: { Login, Register, TaskList },
  data() {
    return {
      loggedIn: false,
      user: null,
      showRegister: false
    }
  },
  created() {
    // Check localStorage when app loads
    const token = localStorage.getItem('token')
    const userData = localStorage.getItem('user')

    if (token && userData) {
      this.loggedIn = true
      this.user = JSON.parse(userData)
    }
  },
  methods: {
    handleLoginSuccess(userData) {
      this.loggedIn = true
      this.user = userData

      // Save to localStorage so it survives refresh
      localStorage.setItem('token', userData.token)
      localStorage.setItem('user', JSON.stringify(userData))
    },
    handleLogout() {
      this.loggedIn = false
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    },
    toggleForm() {
      this.showRegister = !this.showRegister
    }
  }
}
</script>
