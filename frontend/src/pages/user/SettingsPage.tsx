import React, { useState } from 'react'
import { useAuthStore } from '../../store/authStore'
import './UserPages.css'

const SettingsPage: React.FC = () => {
  const { user } = useAuthStore()
  const [activeTab, setActiveTab] = useState('profile')

  return (
    <div className="settings-page">
      <div className="page-header">
        <h1>Settings</h1>
        <p>Manage your account and preferences</p>
      </div>

      <div className="settings-container">
        <div className="settings-tabs">
          <button
            className={`tab-btn ${activeTab === 'profile' ? 'active' : ''}`}
            onClick={() => setActiveTab('profile')}
          >
            👤 Profile
          </button>
          <button
            className={`tab-btn ${activeTab === 'security' ? 'active' : ''}`}
            onClick={() => setActiveTab('security')}
          >
            🔒 Security
          </button>
          <button
            className={`tab-btn ${activeTab === 'notifications' ? 'active' : ''}`}
            onClick={() => setActiveTab('notifications')}
          >
            🔔 Notifications
          </button>
        </div>

        {activeTab === 'profile' && (
          <div className="card">
            <div className="card-header">
              <h2>Profile Information</h2>
            </div>
            <div className="card-body">
              <div className="info-group">
                <label>Name</label>
                <p>{user?.name}</p>
              </div>
              <div className="info-group">
                <label>Email</label>
                <p>{user?.email}</p>
              </div>
              <div className="info-group">
                <label>Phone</label>
                <p>{user?.phone}</p>
              </div>
              <div className="info-group">
                <label>Business</label>
                <p>{user?.business_name}</p>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'security' && (
          <div className="card">
            <div className="card-header">
              <h2>Security Settings</h2>
            </div>
            <div className="card-body">
              <div className="info-group">
                <label>Password</label>
                <p>••••••••</p>
                <button className="btn btn-secondary">Change Password</button>
              </div>
              <div className="info-group">
                <label>Two-Factor Authentication</label>
                <p>Not enabled</p>
                <button className="btn btn-secondary">Enable 2FA</button>
              </div>
            </div>
          </div>
        )}

        {activeTab === 'notifications' && (
          <div className="card">
            <div className="card-header">
              <h2>Notification Preferences</h2>
            </div>
            <div className="card-body">
              <label className="checkbox-label">
                <input type="checkbox" defaultChecked />
                Email notifications for payments
              </label>
              <label className="checkbox-label">
                <input type="checkbox" defaultChecked />
                SMS notifications for subscription changes
              </label>
              <label className="checkbox-label">
                <input type="checkbox" />
                Marketing emails
              </label>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}

export default SettingsPage
