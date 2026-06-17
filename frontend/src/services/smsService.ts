import api from './api'

export interface SMSLog {
  id: number
  subscription_id: number
  phone_number: string
  message: string
  provider: string
  status: 'sent' | 'failed' | 'pending'
  created_at: string
}

export interface SMSCampaign {
  id: number
  name: string
  message: string
  recipients_count: number
  sent_count: number
  failed_count: number
  status: 'draft' | 'sending' | 'completed'
  created_at: string
}

export interface SMSTemplate {
  id: number
  name: string
  message: string
  type: string
  created_at: string
}

export const smsService = {
  sendTestSMS: async (phoneNumber: string, message: string) => {
    const response = await api.post('/sms/send-test', {
      phone_number: phoneNumber,
      message,
    })
    return response.data
  },

  sendBulkSMS: async (phoneNumbers: string[], message: string) => {
    const response = await api.post('/sms/send-bulk', {
      phone_numbers: phoneNumbers,
      message,
    })
    return response.data
  },

  getSMSLogs: async (limit = 100) => {
    const response = await api.get(`/sms/logs?limit=${limit}`)
    return response.data
  },

  getSMSTemplates: async (): Promise<SMSTemplate[]> => {
    const response = await api.get('/sms/templates')
    return response.data
  },

  getSMSStats: async () => {
    const response = await api.get('/sms/stats')
    return response.data
  },
}

export const adminSMSService = {
  createCampaign: async (name: string, message: string, recipients: string[]) => {
    const response = await api.post('/sms/admin/campaign/create', {
      name,
      message,
      recipients,
    })
    return response.data
  },

  getSMSCampaigns: async () => {
    const response = await api.get('/sms/admin/campaigns')
    return response.data
  },

  getSMSStats: async () => {
    const response = await api.get('/sms/admin/stats')
    return response.data
  },
}
