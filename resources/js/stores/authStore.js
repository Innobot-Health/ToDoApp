import { defineStore } from 'pinia';
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    loading: true
  }),
  actions: {
    async fetchUser() {
      try {
        const res = await axios.get('/api/me', { withCredentials: true })
        this.user = res.data
        this.isAuthenticated = true
      } catch (e) {
        this.user = null
        this.isAuthenticated = false
      } finally {
        this.loading = false
      }
    },
    async logout() {
      await axios.post('/logout', {}, { withCredentials: true })
      this.user = null
      this.isAuthenticated = false
    }
  },
})
