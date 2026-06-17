import React, { useEffect, useState } from 'react'
import { adminSMSService, smsService } from '../../services/smsService'
import { Send, Loader as LoaderIcon } from 'lucide-react'
import './AdminPages.css'

const AdminSMS: React.FC = () => {
  const [activeTab, setActiveTab] = useState<'campaigns' | 'send' | 'stats'>('campaigns')
  const [campaigns, setCampaigns] = useState<any[]>([])
  const [smsStats, setSmsStats] = useState<any>({})
  const [loading, setLoading] = useState(false)
  const [formData, setFormData] = useState({
    name: '',
    message: '',
    recipients: '',
  })

  useEffect(() => {
    loadData()
  }, [activeTab])

  const loadData = async () => {
    setLoading(true)
    try {
      if (activeTab === 'campaigns') {
        const data = await adminSMSService.getSMSCampaigns()
        setCampaigns(data)
      } else if (activeTab === 'stats') {
        const data = await smsService.getSMSStats()
        setSmsStats(data)
      }
    } catch (error) {
      console.error('Failed to load data', error)
    } finally {
      setLoading(false)
    }
  }

  const handleSendCampaign = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    try {
      const recipients = formData.recipients.split('\n').filter((r) => r.trim())
      await adminSMSService.createCampaign(formData.name, formData.message, recipients)
      alert('Campaign sent successfully!')
      setFormData({ name: '', message: '', recipients: '' })
      loadData()
    } catch (error: any) {
      alert('Failed to send campaign: ' + error.response?.data?.message)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="admin-sms">
      <div className="page-header">
        <h1>SMS Management</h1>
        <p>Send bulk SMS and manage campaigns</p>
      </div>

      <div className="tabs">
        <button
          className={`tab-btn ${activeTab === 'campaigns' ? 'active' : ''}`}
          onClick={() => setActiveTab('campaigns')}
        >
          📧 Campaigns
        </button>
        <button
          className={`tab-btn ${activeTab === 'send' ? 'active' : ''}`}
          onClick={() => setActiveTab('send')}
        >
          ✉️ Send SMS
        </button>
        <button
          className={`tab-btn ${activeTab === 'stats' ? 'active' : ''}`}
          onClick={() => setActiveTab('stats')}
        >
          📊 Statistics
        </button>
      </div>

      {activeTab === 'campaigns' && (
        <div className="card">
          <div className="card-header">
            <h2>SMS Campaigns</h2>
          </div>
          <div className="card-body">
            {campaigns.length > 0 ? (
              <table className="table">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Recipients</th>
                    <th>Sent</th>
                    <th>Failed</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  {campaigns.map((campaign) => (
                    <tr key={campaign.id}>
                      <td>{campaign.name}</td>
                      <td>{campaign.recipients_count}</td>
                      <td>{campaign.sent_count}</td>
                      <td>{campaign.failed_count}</td>
                      <td>
                        <span className={`badge badge-${campaign.status}`}>{campaign.status}</span>
                      </td>
                      <td>{new Date(campaign.created_at).toLocaleDateString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            ) : (
              <div className="empty-state">
                <p>No campaigns yet</p>
              </div>
            )}
          </div>
        </div>
      )}

      {activeTab === 'send' && (
        <div className="card">
          <div className="card-header">
            <h2>Send Bulk SMS Campaign</h2>
          </div>
          <form onSubmit={handleSendCampaign} className="card-body">
            <div className="form-group">
              <label>Campaign Name</label>
              <input
                type="text"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                placeholder="e.g., Trial Expiry Reminder"
                required
              />
            </div>

            <div className="form-group">
              <label>Message</label>
              <textarea
                value={formData.message}
                onChange={(e) => setFormData({ ...formData, message: e.target.value })}
                placeholder="Enter your SMS message..."
                rows={5}
                required
              />
              <small>{formData.message.length} characters</small>
            </div>

            <div className="form-group">
              <label>Recipients (One per line)</label>
              <textarea
                value={formData.recipients}
                onChange={(e) => setFormData({ ...formData, recipients: e.target.value })}
                placeholder="01700000000&#10;01800000000&#10;01900000000"
                rows={8}
                required
              />
              <small>{formData.recipients.split('\n').filter((r) => r.trim()).length} recipients</small>
            </div>

            <button type="submit" className="btn btn-primary" disabled={loading}>
              {loading ? (
                <>
                  <LoaderIcon size={18} className="spinner" />
                  Sending...
                </>
              ) : (
                <>
                  <Send size={18} />
                  Send Campaign
                </>
              )}
            </button>
          </form>
        </div>
      )}

      {activeTab === 'stats' && (
        <div className="card">
          <div className="card-header">
            <h2>SMS Statistics</h2>
          </div>
          <div className="card-body">
            <div className="stats-grid">
              <div className="stat-item">
                <label>Total SMS Sent</label>
                <div className="stat-value">{smsStats.total_sent || 0}</div>
              </div>
              <div className="stat-item">
                <label>Successfully Delivered</label>
                <div className="stat-value success">{smsStats.delivered || 0}</div>
              </div>
              <div className="stat-item">
                <label>Failed</label>
                <div className="stat-value danger">{smsStats.failed || 0}</div>
              </div>
              <div className="stat-item">
                <label>Success Rate</label>
                <div className="stat-value">
                  {smsStats.total_sent > 0
                    ? ((smsStats.delivered / smsStats.total_sent) * 100).toFixed(1)
                    : 0}
                  %
                </div>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

export default AdminSMS
