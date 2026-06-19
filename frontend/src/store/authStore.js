import { create } from 'zustand';
import { devtools, persist } from 'zustand/middleware';
import { authService } from '../services/authService';
export const useAuthStore = create()(devtools(persist((set) => ({
    user: null,
    isAuthenticated: false,
    isLoading: false,
    error: null,
    login: async (email, password) => {
        set({ isLoading: true, error: null });
        try {
            const response = await authService.login({ email, password });
            localStorage.setItem('authToken', response.token);
            set({
                user: response.user,
                isAuthenticated: true,
                isLoading: false,
            });
        }
        catch (error) {
            set({
                error: error.response?.data?.message || 'Login failed',
                isLoading: false,
            });
            throw error;
        }
    },
    register: async (data) => {
        set({ isLoading: true, error: null });
        try {
            const response = await authService.register(data);
            localStorage.setItem('authToken', response.token);
            set({
                user: response.user,
                isAuthenticated: true,
                isLoading: false,
            });
        }
        catch (error) {
            set({
                error: error.response?.data?.message || 'Registration failed',
                isLoading: false,
            });
            throw error;
        }
    },
    logout: () => {
        localStorage.removeItem('authToken');
        set({
            user: null,
            isAuthenticated: false,
            error: null,
        });
    },
    checkAuth: async () => {
        const token = localStorage.getItem('authToken');
        if (!token) {
            set({ isLoading: false });
            return;
        }
        set({ isLoading: true });
        try {
            const user = await authService.getCurrentUser();
            set({
                user,
                isAuthenticated: true,
                isLoading: false,
            });
        }
        catch (error) {
            localStorage.removeItem('authToken');
            set({
                isAuthenticated: false,
                isLoading: false,
            });
        }
    },
    clearError: () => set({ error: null }),
}), {
    name: 'auth-store',
})));
