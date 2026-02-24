import { defineStore } from 'pinia'
import authService from '../services/authService'
import router from '../router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token
  },

  actions: {

    async login(credentials) {
      const response = await authService.login(credentials)

      this.user = response.data.user
      this.token = response.data.token

      localStorage.setItem('token', this.token)

      router.push('/')
    },

    async register(data) {
      const response = await authService.register(data)

      this.user = response.data.user
      this.token = response.data.token

      localStorage.setItem('token', this.token)

      router.push('/')
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const response = await authService.me()
        this.user = response.data
      } catch (error) {
        this.logout()
      }
    },

    async logout() {
      try {
        await authService.logout()
      } catch (e) {}

      this.user = null
      this.token = null
      localStorage.removeItem('token')

      router.push('/login')
    }
  }
})