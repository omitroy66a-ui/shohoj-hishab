import api from './api';
export const smsService = {
    sendTestSMS: async (phoneNumber, message) => {
        const response = await api.post('/sms/send-test', {
            phone_number: phoneNumber,
            message,
        });
        return response.data;
    },
    sendBulkSMS: async (phoneNumbers, message) => {
        const response = await api.post('/sms/send-bulk', {
            phone_numbers: phoneNumbers,
            message,
        });
        return response.data;
    },
    getSMSLogs: async (limit = 100) => {
        const response = await api.get(`/sms/logs?limit=${limit}`);
        return response.data;
    },
    getSMSTemplates: async () => {
        const response = await api.get('/sms/templates');
        return response.data;
    },
    getSMSStats: async () => {
        const response = await api.get('/sms/stats');
        return response.data;
    },
};
export const adminSMSService = {
    createCampaign: async (name, message, recipients) => {
        const response = await api.post('/sms/admin/campaign/create', {
            name,
            message,
            recipients,
        });
        return response.data;
    },
    getSMSCampaigns: async () => {
        const response = await api.get('/sms/admin/campaigns');
        return response.data;
    },
    getSMSStats: async () => {
        const response = await api.get('/sms/admin/stats');
        return response.data;
    },
};
