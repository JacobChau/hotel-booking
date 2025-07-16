import { defineStore } from 'pinia'
import { authAPI } from '../api/auth'
import Cookies from 'js-cookie'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    token: Cookies.get('auth_token') || null,
    loading: false,
    error: null
  }),

  getters: {
    hasError: (state) => !!state.error,
    isLoggedIn: (state) => state.isAuthenticated && !!state.token
  },

  actions: {
    // Initialize auth state from cookies
    initAuth() {
      const token = Cookies.get('auth_token')
      const user = Cookies.get('auth_user')
      
      if (token && user) {
        this.token = token
        this.user = JSON.parse(user)
        this.isAuthenticated = true
      }
    },

    // Login action
    async login(credentials) {
      this.loading = true
      this.error = null

      try {
        const response = await authAPI.login(credentials)
        
        // Handle successful login - backend returns { status, message, access_token, user }
        if (response.status && response.access_token) {
          this.user = response.user
          this.token = response.access_token
          this.isAuthenticated = true
          
          // Store in secure HTTP-only cookies (if backend supports it) or regular cookies
          Cookies.set('auth_token', response.access_token, { 
            expires: 7, // 7 days
            secure: window.location.protocol === 'https:',
            sameSite: 'strict'
          })
          Cookies.set('auth_user', JSON.stringify(response.user), { 
            expires: 7,
            secure: window.location.protocol === 'https:',
            sameSite: 'strict'
          })
          
          // Check for redirect path and return it
          const redirectPath = sessionStorage.getItem('redirectAfterLogin')
          if (redirectPath) {
            sessionStorage.removeItem('redirectAfterLogin')
            return { success: true, redirectPath }
          }
          
          return { success: true }
        } else {
          throw new Error(response.message || 'Login failed')
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'Login failed'
        this.isAuthenticated = false
        this.user = null
        this.token = null
        
        // Clear cookies
        Cookies.remove('auth_token')
        Cookies.remove('auth_user')
        
        throw new Error(this.error)
      } finally {
        this.loading = false
      }
    },

    // Register action
    async register(userData) {
      this.loading = true
      this.error = null

      try {
        const response = await authAPI.register(userData)
        
        // Registration successful - backend returns { status, message }
        if (response.status) {
          // After successful registration, log the user in automatically
          return await this.login({
            email: userData.email,
            password: userData.password
          })
        } else {
          throw new Error(response.message || 'Registration failed')
        }
      } catch (error) {
        this.error = error.response?.data?.message || error.message || 'Registration failed'
        throw new Error(this.error)
      } finally {
        this.loading = false
      }
    },

    // Logout action
    logout() {
      // Clear state without calling API
      this.user = null
      this.isAuthenticated = false
      this.token = null
      this.error = null
      
      // Clear cookies
      Cookies.remove('auth_token')
      Cookies.remove('auth_user')
      
      // Also clear any stored redirect path
      sessionStorage.removeItem('redirectAfterLogin')
    },

    // Clear error
    clearError() {
      this.error = null
    }
  }
})
