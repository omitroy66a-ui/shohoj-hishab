import React, { useEffect, useState } from 'react'
import { useSubscriptionStore } from '../../store/subscriptionStore'
import { useAuthStore } from '../../store/authStore'
import PlanComparison from '../../components/subscription/PlanComparison'
import FeaturesList from '../../components/subscription/FeaturesList'
import './UserPages.css'

const DashboardPage: React.FC = () => {
  const { subscription, fetchSubscription } = useSubscriptionStore()
  const { user } = useAuthStore()

  const [stats, setStats] = useState({
    sales: 0,
    purchase: 0,
    expense: 0,
    customers: 0,
    products: 0
  })

  useEffect(() => {
    fetchSubscription()

    fetch('http://localhost/api/dashboard/index.php')
      .then((res) => res.json())
      .then((data) => {
        setStats({
          sales: Number(data.sales) || 0,
          purchase: Number(data.purchase) || 0,
          expense: Number(data.expense) || 0,
          customers: Number(data.customers) || 0,
          products: Number(data.products) || 0
        })
      })
      .catch((err) => {
        console.error('Dashboard API Error:', err)
      })
  }, [])

  return (
    <div className="dashboard-page">
      <div className="page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, {user?.name || 'User'}! 👋</p>
      </div>

      <div className="dashboard-grid">
        {/* Subscription Card */}
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
                    ⚠️ Your subscription will expire soon!
                  </div>
                )}
              </>
            ) : (
              <div className="alert alert-info">
                📌 No active subscription found.
              </div>
            )}
          </div>
        </div>

        {/* Business Stats */}
        <div className="card stats-card">
          <div className="card-header">
            <h2>Business Statistics</h2>
          </div>

          <div className="card-body">
            <div className="stat-item">
              <div className="stat-value">৳{stats.sales}</div>
              <div className="stat-label">Total Sales</div>
            </div>

            <div className="stat-item">
              <div className="stat-value">৳{stats.purchase}</div>
              <div className="stat-label">Total Purchase</div>
            </div>

            <div className="stat-item">
              <div className="stat-value">৳{stats.expense}</div>
              <div className="stat-label">Total Expense</div>
            </div>

            <div className="stat-item">
              <div className="stat-value">{stats.customers}</div>
              <div className="stat-label">Customers</div>
            </div>

            <div className="stat-item">
              <div className="stat-value">{stats.products}</div>
              <div className="stat-label">Products</div>
            </div>
          </div>
        </div>
      </div>

      {/* Features */}
      <div className="card">
        <div className="card-header">
          <h2>Available Features</h2>
        </div>

        <div className="card-body">
          <FeaturesList />
        </div>
      </div>

      {/* Plans */}
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