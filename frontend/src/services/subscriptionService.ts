import api from './api'

export interface Subscription {
  id: number
  business_id: number
  plan_id: number
  plan_name: string
  plan_type: 'trial' | 'standard' | 'advanced'
  start_date: string
  expiry_date: string
  status: 'active' | 'expired' | 'cancelled'
  daysRemaining: number
  price: number
  duration_days: number
}

export interface Plan {
  id: number
  name: string
  price: number
  duration_days: number
  plan_type: 'trial' | 'standard' | 'advanced'
  features: string[]
  description: string
}

export interface UpgradeRequest {
  plan_id: number
  gateway: 'nagad' | 'bkash' | 'rocket'
  payment_number: string
  transaction_id: string
}

export interface Payment {
  id: number
  business_id: number
  subscription_id: number
  plan_id: number
  amount: number
  gateway: string
  payment_number: string
  transaction_id: string
  status: 'pending' | 'completed' | 'rejected'
  created_at: string
  updated_at: string
}

export const subscriptionService = {
  getSubscription: async () => {
    const response = await api.get('/subscription/get-subscription')
    return response.data
  },

  getAllPlans: async (): Promise<Plan[]> => {
    const response = await api.get('/subscription/plans')
    return response.data
  },

  upgradeSubscription: async (data: UpgradeRequest) => {
    const response = await api.post('/subscription/upgrade', data)
    return response.data
  },

  getPaymentHistory: async () => {
    const response = await api.get('/subscription/payment-history')
    return response.data
  },

  getPaymentDetails: async (paymentId: number) => {
    const response = await api.get(`/subscription/payment/${paymentId}`)
    return response.data
  },
}

export const adminSubscriptionService = {
  getPendingPayments: async () => {
    const response = await api.get('/subscription/admin/pending-payments')
    return response.data
  },

  approvePayment: async (paymentId: number) => {
    const response = await api.post(`/subscription/admin/approve-payment/${paymentId}`)
    return response.data
  },

  rejectPayment: async (paymentId: number, reason: string) => {
    const response = await api.post(`/subscription/admin/reject-payment/${paymentId}`, { reason })
    return response.data
  },

  applyDiscount: async (paymentId: number, discountAmount: number, discountType: 'flat' | 'percentage') => {
    const response = await api.post(`/subscription/admin/apply-discount/${paymentId}`, {
      discount_amount: discountAmount,
      discount_type: discountType,
    })
    return response.data
  },

  getDiscountHistory: async () => {
    const response = await api.get('/subscription/admin/discount-history')
    return response.data
  },
}
