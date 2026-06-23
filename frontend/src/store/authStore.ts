import { create } from 'zustand'
import { devtools, persist } from 'zustand/middleware'
import { authService, AuthResponse } from '../services/authService'

interface User {
  id: number
  name: string
  email: string
  phone: string
  role: 'user' | 'admin'
  business_name: string
}

interface AuthState {
  user: User | null
  isAuthenticated: boolean
  isLoading: boolean
  error: string | null
  login: (phone: string, password: string) => Promise<void>
  register: (data: any) => Promise<void>
  logout: () => void
  checkAuth: () => Promise<void>
  clearError: () => void
}

export const useAuthStore = create<AuthState>()(
  devtools(
    persist(
      (set) => ({
        user: null,
        isAuthenticated: false,
        isLoading: false,
        error: null,

        login: async (phone: string, password: string) => {
          set({ isLoading: true, error: null })
          try {
            const response: AuthResponse = await authService.login({ phone, password })
            localStorage.setItem('authToken', response.token)
            set({
              user: response.user,
              isAuthenticated: true,
              isLoading: false,
            })
          } catch (error: any) {
            set({
              error: error.response?.data?.message || 'Login failed',
              isLoading: false,
            })
            throw error
          }
        },

        register: async (data: any) => {
          set({ isLoading: true, error: null })
          try {
            const response: AuthResponse = await authService.register(data)
            localStorage.setItem('authToken', response.token)
            set({
              user: response.user,
              isAuthenticated: true,
              isLoading: false,
            })
          } catch (error: any) {
            set({
              error: error.response?.data?.message || 'Registration failed',
              isLoading: false,
            })
            throw error
          }
        },

        logout: () => {
          localStorage.removeItem('authToken')
          set({
            user: null,
            isAuthenticated: false,
            error: null,
          })
        },

        checkAuth: async () => {
          const token = localStorage.getItem('authToken')
          if (!token) {
            set({ isLoading: false })
            return
          }

          set({ isLoading: true })
          try {
            const user = await authService.getCurrentUser()
            set({
              user,
              isAuthenticated: true,
              isLoading: false,
            })
          } catch (error) {
            localStorage.removeItem('authToken')
            set({
              isAuthenticated: false,
              isLoading: false,
            })
          }
        },

        clearError: () => set({ error: null }),
      }),
      {
        name: 'auth-store',
      }
    )
  )
)
