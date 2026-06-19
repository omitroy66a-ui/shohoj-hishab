export interface ErrorResponse {
  message: string
  status?: number
  code?: string
}

export interface AuthError extends Error {
  response?: {
    status: number
    data: ErrorResponse
  }
}

export interface FormErrors {
  [key: string]: string
}

export interface Subscription {
  id: number
  plan_id: number
  user_id: number
  plan_name: string
  status: 'active' | 'expired' | 'cancelled' | 'pending'
  start_date: string
  end_date: string
  created_at: string
  updated_at: string
}

export interface Plan {
  id: number
  name: string
  price: number
  duration_days: number
  features: string[]
}

export interface SMS {
  id: number
  phone_number: string
  message: string
  status: 'sent' | 'failed' | 'pending'
  created_at: string
}
