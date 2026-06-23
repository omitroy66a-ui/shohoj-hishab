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
  password_confirmation: string;
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

type LaravelAuthResponse = {
  data?: {
    user?: User;
    token?: string;
    access_token?: string;
  };
  user?: User;
  token?: string;
  access_token?: string;
};

const normalizeAuthResponse = (payload: LaravelAuthResponse): AuthResponse => {
  const data = payload.data || payload;
  const user = data.user;
  const token = data.token || data.access_token;

  if (!user || !token) {
    throw new Error("Invalid authentication response from server");
  }

  return {
    user,
    token,
  };
};

export const authService = {
  login: async (data: LoginRequest): Promise<AuthResponse> => {
    const response = await api.post<LaravelAuthResponse>("/auth/login", data);
    return normalizeAuthResponse(response.data);
  },

  register: async (data: RegisterRequest): Promise<AuthResponse> => {
    const response = await api.post<LaravelAuthResponse>("/auth/register", data);
    return normalizeAuthResponse(response.data);
  },

  logout: async (): Promise<void> => {
    await api.post("/auth/logout");
    localStorage.removeItem("authToken");
  },

  getCurrentUser: async (): Promise<User> => {
    const response = await api.get<User | { data?: User; user?: User }>("/auth/user");

    if ("data" in response.data && response.data.data) {
      return response.data.data;
    }

    if ("user" in response.data && response.data.user) {
      return response.data.user;
    }

    return response.data as User;
  },
};
