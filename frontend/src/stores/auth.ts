import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '../api'

interface User {
  id: number
  email: string
  firstName: string
  lastName: string
  roles: string[]
  totalPoints: number
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.roles?.includes('ROLE_ADMIN') ?? false)

  async function login(email: string, password: string) {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.post('/auth/login', { email, password })
      token.value = response.data.token
      localStorage.setItem('token', response.data.token)
      await fetchUser()
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Błąd logowania'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function register(data: { email: string; password: string; firstName: string; lastName: string }) {
    loading.value = true
    error.value = null
    
    try {
      await api.post('/auth/register', data)
    } catch (e: any) {
      error.value = e.response?.data?.error || 'Błąd rejestracji'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchUser() {
    if (!token.value) return
    
    try {
      const response = await api.get('/auth/me')
      user.value = response.data
    } catch (e) {
      logout()
    }
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('token')
  }

  // Fetch user on init if token exists
  if (token.value) {
    fetchUser()
  }

  return {
    token,
    user,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    register,
    fetchUser,
    logout
  }
})
