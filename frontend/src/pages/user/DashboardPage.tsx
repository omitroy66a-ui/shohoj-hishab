import React, { useEffect } from 'react'
import { useSubscriptionStore } from '../../store/subscriptionStore'
import { useAuthStore } from '../../store/authStore'
import SubscriptionCard from '../../components/subscription/SubscriptionCard'
import PlanComparison from '../../components/subscription/PlanComparison'
import FeaturesList from '../../components/subscription/FeaturesList'
import './UserPages.css'

const DashboardPage: React.FC = () => {
  const { subscription, fetchSubscription, isLoading } = useSubscriptionStore()
  const { user } = useAuthStore()

  useEffect(() => {
    fetchSubscription()
  }, [])

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active':
        return 'success'
      case 'expired':
        return 'danger'
      case 'cancelled':
        return 'warning'
      default:
        return 'info'
    }
  }

  return (
    <div className="dashboard-page">
      <div className="page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, {user?.name}! 👋</p>
      </div>

      <div className="dashboard-grid">
        {/* Current Subscription Card */}
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
                    <strong>৳{subscription.price}/month</strong>
                  </div>
                  <div className="info-row">
                    <label>Start Date:</label>
                    <strong>{new Date(subscription.start_date).toLocaleDateString()}</strong>
                  </div>
                  <div className="info-row">
                    <label>Expiry Date:</label>
                    <strong>{new Date(subscription.expiry_date).toLocaleDateString()}</strong>
                  </div>
                  <div className="info-row highlight">
                    <label>Days Remaining:</label>
                    <strong>{subscription.daysRemaining} days</strong>
                  </div>
                </div>

                {subscription.daysRemaining <= 7 && (
                  <div className="alert alert-warning">
                    ⚠️ Your subscription will expire soon! Consider upgrading to continue using all features.
                  </div>
                )}
              </>
            ) : (
              <div className="alert alert-info">
                📌 You don't have an active subscription. Start with a free trial or upgrade to a premium plan.
              </div>
            )}
          </div>
        </div>

        {/* Quick Stats */}
        <div className="card stats-card">
          <div className="card-header">
            <h2>Quick Stats</h2>
          </div>
          <div className="card-body">
            <div className="stat-item">
              <div className="stat-value">3</div>
              <div className="stat-label">Available Plans</div>
            </div>
            <div className="stat-item">
              <div className="stat-value">∞</div>
              <div className="stat-label">Features Unlocked</div>
            </div>
            <div className="stat-item">
              <div className="stat-value">24/7</div>
              <div className="stat-label">Support</div>
            </div>
          </div>
        </div>
      </div>

      {/* Features List */}
      <div className="card">
        <div className="card-header">
          <h2>Available Features</h2>
        </div>
        <div className="card-body">
          <FeaturesList />
        </div>
      </div>

      {/* Plan Comparison */}
      <div className="card">
        <div className="card-header">
          <h2>Plan Comparison</h2>
        </div>
        <div className="card-body">
          <PlanComparison />
        </div>
      </div>
    </div>
  )
}

export default DashboardPage
