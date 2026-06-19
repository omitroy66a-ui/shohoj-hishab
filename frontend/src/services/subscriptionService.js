import api from './api';
export const subscriptionService = {
    getSubscription: async () => {
        const response = await api.get('/subscription/get-subscription');
        return response.data;
    },
    getAllPlans: async () => {
        const response = await api.get('/subscription/plans');
        return response.data;
    },
    upgradeSubscription: async (data) => {
        const response = await api.post('/subscription/upgrade', data);
        return response.data;
    },
    getPaymentHistory: async () => {
        const response = await api.get('/subscription/payment-history');
        return response.data;
    },
    getPaymentDetails: async (paymentId) => {
        const response = await api.get(`/subscription/payment/${paymentId}`);
        return response.data;
    },
};
export const adminSubscriptionService = {
    getPendingPayments: async () => {
        const response = await api.get('/subscription/admin/pending-payments');
        return response.data;
    },
    approvePayment: async (paymentId) => {
        const response = await api.post(`/subscription/admin/approve-payment/${paymentId}`);
        return response.data;
    },
    rejectPayment: async (paymentId, reason) => {
        const response = await api.post(`/subscription/admin/reject-payment/${paymentId}`, { reason });
        return response.data;
    },
    applyDiscount: async (paymentId, discountAmount, discountType) => {
        const response = await api.post(`/subscription/admin/apply-discount/${paymentId}`, {
            discount_amount: discountAmount,
            discount_type: discountType,
        });
        return response.data;
    },
    getDiscountHistory: async () => {
        const response = await api.get('/subscription/admin/discount-history');
        return response.data;
    },
};
