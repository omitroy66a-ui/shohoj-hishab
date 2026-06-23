import api from "./api";

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  phone: string;
  password: string;
  business_name: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
  phone: string;
  role: "user" | "admin";
  business_name: string;
}

export interface AuthResponse {
  user: User;
  token: string;
}

export const authService = {
  // LOGIN
  login: async (data: LoginRequest): Promise<AuthResponse> => {
    const response = await api.post("/auth/login", data);
    return response.data;
  },

  // REGISTER
  register: async (data: RegisterRequest): Promise<AuthResponse> => {
    const response = await api.post("/auth/register", data);
    return response.data;
  },

  // LOGOUT
  logout: async (): Promise<void> => {
    await api.post("/auth/logout");
    localStorage.removeItem("authToken");
  },

  // CURRENT USER
  getCurrentUser: async (): Promise<User> => {
    const response = await api.get("/auth/user");
    return response.data;
  },
};