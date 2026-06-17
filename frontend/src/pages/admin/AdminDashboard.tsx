import React, { useEffect, useState } from 'react'
import { adminSubscriptionService } from '../../services/subscriptionService'
import { adminSMSService, smsService } from '../../services/smsService'
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts'
import './AdminPages.css'

const AdminDashboard: React.FC = () => {
  const [dashboardData, setDashboardData] = useState<any>({})
  const [loading, setLoading] = useState(true)
  const [smsStats, setSmsStats] = useState<any>({})

  useEffect(() => {
    loadDashboardData()
  }, [])

  const loadDashboardData = async () => {
    try {
      setLoading(true)
      const smsData = await adminSMSService.getSMSStats()
      setSmsStats(smsData)
      
      setDashboardData({
        totalUsers: 150,
        activeSubscriptions: 87,
        totalRevenue: 15420,
        pendingPayments: 12,
      })
    } catch (error) {
      console.error('Failed to load dashboard', error)
    } finally {
      setLoading(false)
    }
  }

  const chartData = [
    { name: 'Trial', users: 45 },
    { name: 'Standard', users: 38 },
    { name: 'Advanced', users: 4 },
  ]

  return (
    <div className="admin-dashboard">
      <div className="page-header">
        <h1>Admin Dashboard</h1>
        <p>System overview and key metrics</p>
      </div>

      <div className="stats-grid">
        <div className="stat-card">
          <div className="stat-header">
            <span className="stat-icon">👥</span>
            <h3>Total Users</h3>
          </div>
          <div className="stat-value">{dashboardData.totalUsers || 0}</div>
        </div>

        <div className="stat-card">
          <div className="stat-header">
            <span className="stat-icon">✅</span>
            <h3>Active Subscriptions</h3>
          </div>
          <div className="stat-value">{dashboardData.activeSubscriptions || 0}</div>
        </div>

        <div className="stat-card">
          <div className="stat-header">
            <span className="stat-icon">💰</span>
            <h3>Total Revenue</h3>
          </div>
          <div className="stat-value">৳{dashboardData.totalRevenue || 0}</div>
        </div>

        <div className="stat-card">
          <div className="stat-header">
            <span className="stat-icon">⏳</span>
            <h3>Pending Payments</h3>
          </div>
          <div className="stat-value highlight">{dashboardData.pendingPayments || 0}</div>
        </div>
      </div>

      <div className="dashboard-grid">
        <div className="card">
          <div className="card-header">
            <h2>Subscriptions by Plan</h2>
          </div>
          <div className="card-body">
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={chartData}>
                <CartesianGrid strokeDasharray="3 3" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip />
                <Legend />
                <Bar dataKey="users" fill="#8884d8" />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        <div className="card">
          <div className="card-header">
            <h2>SMS Statistics</h2>
          </div>
          <div className="card-body">
            <div className="stat-item">
              <label>Total SMS Sent</label>
              <strong>{smsStats.total_sent || 0}</strong>
            </div>
            <div className="stat-item">
              <label>SMS Delivered</label>
              <strong>{smsStats.delivered || 0}</strong>
            </div>
            <div className="stat-item">
              <label>SMS Failed</label>
              <strong>{smsStats.failed || 0}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

export default AdminDashboard
