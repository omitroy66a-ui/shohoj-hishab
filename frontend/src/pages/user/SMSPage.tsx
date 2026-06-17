import React, { useEffect, useState } from 'react'
import { smsService } from '../../services/smsService'
import { Send, MessageSquare, AlertCircle } from 'lucide-react'
import './UserPages.css'

const SMSPage: React.FC = () => {
  const [smsTabs, setSmsTabs] = useState<'logs' | 'send'>('logs')
  const [phoneNumber, setPhoneNumber] = useState('')
  const [message, setMessage] = useState('')
  const [smsLogs, setSmsLogs] = useState<any[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (smsTabs === 'logs') {
      loadSMSLogs()
    }
  }, [smsTabs])

  const loadSMSLogs = async () => {
    setLoading(true)
    try {
      const data = await smsService.getSMSLogs()
      setSmsLogs(data)
    } catch (error) {
      console.error('Failed to load SMS logs', error)
    }
    setLoading(false)
  }

  const handleSendSMS = async (e: React.FormEvent) => {
    e.preventDefault()
    setLoading(true)
    try {
      await smsService.sendTestSMS(phoneNumber, message)
      alert('SMS sent successfully!')
      setPhoneNumber('')
      setMessage('')
    } catch (error: any) {
      alert('Failed to send SMS: ' + (error.response?.data?.message || 'Unknown error'))
    }
    setLoading(false)
  }

  return (
    <div className="sms-page">
      <div className="page-header">
        <h1>SMS Notifications</h1>
        <p>Manage your SMS communications</p>
      </div>

      <div className="tabs">
        <button
          className={`tab-btn ${smsTabs === 'logs' ? 'active' : ''}`}
          onClick={() => setSmsTabs('logs')}
        >
          📨 SMS Logs
        </button>
        <button
          className={`tab-btn ${smsTabs === 'send' ? 'active' : ''}`}
          onClick={() => setSmsTabs('send')}
        >
          ✉️ Send SMS
        </button>
      </div>

      {smsTabs === 'logs' && (
        <div className="card">
          <div className="card-header">
            <h2>SMS History</h2>
          </div>
          <div className="card-body">
            {smsLogs.length > 0 ? (
              <table className="table">
                <thead>
                  <tr>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Provider</th>
                    <th>Status</th>
                    <th>Date</th>
                  </tr>
                </thead>
                <tbody>
                  {smsLogs.map((log) => (
                    <tr key={log.id}>
                      <td>{log.phone_number}</td>
                      <td className="message-col">{log.message}</td>
                      <td>{log.provider}</td>
                      <td>
                        <span className={`badge badge-${log.status}`}>{log.status}</span>
                      </td>
                      <td>{new Date(log.created_at).toLocaleDateString()}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            ) : (
              <div className="empty-state">
                <MessageSquare size={48} />
                <p>No SMS logs yet</p>
              </div>
            )}
          </div>
        </div>
      )}

      {smsTabs === 'send' && (
        <div className="card">
          <div className="card-header">
            <h2>Send Test SMS</h2>
          </div>
          <form onSubmit={handleSendSMS} className="form card-body">
            <div className="form-group">
              <label>Phone Number</label>
              <input
                type="tel"
                value={phoneNumber}
                onChange={(e) => setPhoneNumber(e.target.value)}
                placeholder="01700000000"
                required
              />
            </div>

            <div className="form-group">
              <label>Message</label>
              <textarea
                value={message}
                onChange={(e) => setMessage(e.target.value)}
                placeholder="Enter your message..."
                rows={5}
                required
              />
              <small>{message.length} characters</small>
            </div>

            <button type="submit" className="btn btn-primary" disabled={loading}>
              {loading ? 'Sending...' : 'Send SMS'}
            </button>
          </form>
        </div>
      )}
    </div>
  )
}

export default SMSPage
