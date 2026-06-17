import { create } from 'zustand'
import { subscriptionService, Subscription, Plan } from '../services/subscriptionService'

interface SubscriptionState {
  subscription: Subscription | null
  plans: Plan[]
  isLoading: boolean
  error: string | null
  fetchSubscription: () => Promise<void>
  fetchPlans: () => Promise<void>
  upgradeSubscription: (planId: number, gateway: string, paymentNumber: string, transactionId: string) => Promise<void>
  clearError: () => void
}

export const useSubscriptionStore = create<SubscriptionState>((set) => ({
  subscription: null,
  plans: [],
  isLoading: false,
  error: null,

  fetchSubscription: async () => {
    set({ isLoading: true })
    try {
      const data = await subscriptionService.getSubscription()
      set({ subscription: data, isLoading: false })
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch subscription',
        isLoading: false,
      })
    }
  },

  fetchPlans: async () => {
    set({ isLoading: true })
    try {
      const data = await subscriptionService.getAllPlans()
      set({ plans: data, isLoading: false })
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Failed to fetch plans',
        isLoading: false,
      })
    }
  },

  upgradeSubscription: async (planId: number, gateway: string, paymentNumber: string, transactionId: string) => {
    set({ isLoading: true })
    try {
      await subscriptionService.upgradeSubscription({
        plan_id: planId,
        gateway: gateway as 'nagad' | 'bkash' | 'rocket',
        payment_number: paymentNumber,
        transaction_id: transactionId,
      })
      set({ isLoading: false })
    } catch (error: any) {
      set({
        error: error.response?.data?.message || 'Upgrade failed',
        isLoading: false,
      })
      throw error
    }
  },

  clearError: () => set({ error: null }),
}))
