import React, { useEffect, useState } from 'react'
import { useAuthStore } from '../../store/authStore'
import { useSubscriptionStore } from '../../store/subscriptionStore'
import PlanComparison from '../../components/subscription/PlanComparison'
import FeaturesList from '../../components/subscription/FeaturesList'
import './UserPages.css'

const SettingsPage: React.FC = () => {
  const { user } = useAuthStore()
  const { subscription, fetchSubscription } = useSubscriptionStore()
  const [activeTab, setActiveTab] = useState('profile')
  const [profile, setProfile] = useState({
    name: '',
    email: '',
    phone: '',
    business_name: ''
  })
  const [profilePicture, setProfilePicture] = useState('')
  const [saveMessage, setSaveMessage] = useState('')

  useEffect(() => {
    fetchSubscription()
  }, [])

  useEffect(() => {
    const savedProfile = localStorage.getItem('userProfile')
    const savedPicture = localStorage.getItem('profilePicture')

    if (savedProfile) {
      try {
        const parsedProfile = JSON.parse(savedProfile)

        setProfile({
          name: parsedProfile.name || '',
          email: parsedProfile.email || '',
          phone: parsedProfile.phone || '',
          business_name: parsedProfile.business_name || ''
        })
      } catch (error) {
        console.error('Profile load error:', error)
      }
    } else {
      setProfile({
        name: user?.name || '',
        email: user?.email || '',
        phone: user?.phone || '',
        business_name: user?.business_name || ''
      })
    }

    setProfilePicture(savedPicture || '')
  }, [user])

  const handleProfileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = event.target

    setProfile((currentProfile) => ({
      ...currentProfile,
      [name]: value
    }))

    setSaveMessage('')
  }

  const handleProfilePictureChange = (
    event: React.ChangeEvent<HTMLInputElement>
  ) => {
    const file = event.target.files?.[0]

    if (!file) {
      return
    }

    const reader = new FileReader()

    reader.onloadend = () => {
      if (typeof reader.result === 'string') {
        setProfilePicture(reader.result)
        setSaveMessage('')
      }
    }

    reader.readAsDataURL(file)
  }

  const handleRemoveProfilePicture = () => {
    setProfilePicture('')
    localStorage.removeItem('profilePicture')
    setSaveMessage('Profile picture removed.')
  }

  const handleProfileSave = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault()

    localStorage.setItem('userProfile', JSON.stringify(profile))

    if (profilePicture) {
      localStorage.setItem('profilePicture', profilePicture)
    } else {
      localStorage.removeItem('profilePicture')
    }

    setSaveMessage('Profile updated successfully.')
  }

  return (
    <div className="settings-page">
      <div className="page-header">
        <h1>Settings</h1>
        <p>Manage your account and preferences</p>
      </div>

      <div className="settings-container">
        <div className="settings-tabs">
          <button
            type="button"
            className={`tab-btn ${activeTab === 'profile' ? 'active' : ''}`}
            onClick={() => setActiveTab('profile')}
          >
            Profile
          </button>

          <button
            type="button"
            className={`tab-btn ${activeTab === 'subscription' ? 'active' : ''}`}
            onClick={() => setActiveTab('subscription')}
          >
            Subscription
          </button>

          <button
            type="button"
            className={`tab-btn ${activeTab === 'security' ? 'active' : ''}`}
            onClick={() => setActiveTab('security')}
          >
            Security
          </button>

          <button
            type="button"
            className={`tab-btn ${activeTab === 'notifications' ? 'active' : ''}`}
            onClick={() => setActiveTab('notifications')}
          >
            Notifications
          </button>
        </div>

        {activeTab === 'profile' && (
          <div className="card">
            <div className="card-header">
              <h2>Profile Information</h2>
            </div>

            <div className="card-body">
              <form onSubmit={handleProfileSave}>
                <div className="info-group">
                  <label>Profile Picture</label>

                  {profilePicture && (
                    <img
                      src={profilePicture}
                      alt="Profile"
                      className="profile-picture-preview"
                    />
                  )}

                  {profilePicture && (
                    <button
                      type="button"
                      className="btn btn-secondary"
                      onClick={handleRemoveProfilePicture}
                    >
                      Remove Picture
                    </button>
                  )}

                  <input
                    type="file"
                    accept="image/*"
                    onChange={handleProfilePictureChange}
                  />
                </div>

                <div className="info-group">
                  <label>Name</label>
                  <input
                    type="text"
                    name="name"
                    value={profile.name}
                    onChange={handleProfileChange}
                  />
                </div>

                <div className="info-group">
                  <label>Email</label>
                  <input
                    type="email"
                    name="email"
                    value={profile.email}
                    onChange={handleProfileChange}
                  />
                </div>

                <div className="info-group">
                  <label>Phone</label>
                  <input
                    type="text"
                    name="phone"
                    value={profile.phone}
                    onChange={handleProfileChange}
                  />
                </div>

                <div className="info-group">
                  <label>Business</label>
                  <input
                    type="text"
                    name="business_name"
                    value={profile.business_name}
                    onChange={handleProfileChange}
                  />
                </div>

                {saveMessage && (
                  <div className="alert alert-info">
                    {saveMessage}
                  </div>
                )}

                <button type="submit" className="btn btn-primary">
                  Save Profile
                </button>
              </form>
            </div>
          </div>
        )}

        {activeTab === 'subscription' && (
          <>
            <div className="card subscription-card">
              <div className="card-header">
                <h2>Current Subscription</h2>

                <span className={`badge badge-${subscription?.status || 'info'}`}>
                  {subscription?.status?.toUpperCase() || 'NO PLAN'}
                </span>
              </div>

              <div className="card-body">
                {subscription ? (
                  <>
                    <div className="subscription-info">
                      <div className="info-row">
                        <label>Plan:</label>
                        <strong>{subscription.plan_name}</strong>
                      </div>

                      <div className="info-row">
                        <label>Plan Type:</label>
                        <strong>{subscription.plan_type.toUpperCase()}</strong>
                      </div>

                      <div className="info-row">
                        <label>Price:</label>
                        <strong>Tk {subscription.price}/month</strong>
                      </div>

                      <div className="info-row">
                        <label>Start Date:</label>
                        <strong>
                          {new Date(subscription.start_date).toLocaleDateString()}
                        </strong>
                      </div>

                      <div className="info-row">
                        <label>Expiry Date:</label>
                        <strong>
                          {new Date(subscription.expiry_date).toLocaleDateString()}
                        </strong>
                      </div>

                      <div className="info-row highlight">
                        <label>Days Remaining:</label>
                        <strong>{subscription.daysRemaining} days</strong>
                      </div>
                    </div>

                    {subscription.daysRemaining <= 7 && (
                      <div className="alert alert-warning">
                        Your subscription will expire soon!
                      </div>
                    )}
                  </>
                ) : (
                  <div className="alert alert-info">
                    No active subscription found.
                  </div>
                )}
              </div>
            </div>

            <div className="card">
              <div className="card-header">
                <h2>Available Features</h2>
              </div>

              <div className="card-body">
                <FeaturesList />
              </div>
            </div>

            <div className="card">
              <div className="card-header">
                <h2>Plan Comparison</h2>
              </div>

              <div className="card-body">
                <PlanComparison />
              </div>
            </div>
          </>
        )}

        {activeTab === 'security' && (
          <div className="card">
            <div className="card-header">
              <h2>Security Settings</h2>
            </div>

            <div className="card-body">
              <div className="info-group">
                <label>Password</label>
                <p>********</p>
                <button className="btn btn-secondary" type="button">
                  Change Password
                </button>
              </div>

              <div className="info-group">
                <label>Two-Factor Authentication</label>
                <p>Not enabled</p>
                <button className="btn btn-secondary" type="button">
                  Enable 2FA
                </button>
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
