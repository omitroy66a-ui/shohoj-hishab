import api from './api';
export const authService = {
    login: async (data) => {
        const response = await api.post('/auth/login', data);
        return response.data;
    },
    register: async (data) => {
        const response = await api.post('/auth/register', data);
        return response.data;
    },
    logout: async () => {
        await api.post('/auth/logout');
    },
    getCurrentUser: async () => {
        const response = await api.get('/auth/user');
        return response.data;
    },
};
