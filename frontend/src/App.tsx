import { useEffect } from 'react'
import { Routes, Route, Navigate } from 'react-router-dom'
import { useAuthStore } from './store/authStore'
import Layout from './components/common/Layout'
import LoginPage from './pages/auth/LoginPage'
import RegisterPage from './pages/auth/RegisterPage'
import DashboardPage from './pages/user/DashboardPage'
import UpgradePage from './pages/user/UpgradePage'
import AdminDashboard from './pages/admin/AdminDashboard'
import AdminPayments from './pages/admin/AdminPayments'
import AdminSMS from './pages/admin/AdminSMS'
import SMSPage from './pages/user/SMSPage'
import SettingsPage from './pages/user/SettingsPage'
import Loader from './components/common/Loader'
import './styles/App.css'

function App() {
  const { isAuthenticated, user, checkAuth, isLoading } = useAuthStore()

useEffect(() => {
  checkAuth()
}, [checkAuth])

  if (isLoading) {
    return <Loader />
  }

  return (
    <Routes>
      {/* Auth Routes */}
      <Route path="/login" element={<LoginPage />} />
      <Route path="/register" element={<RegisterPage />} />

      {/* User Routes */}
      <Route
        path="/dashboard"
        element={
          isAuthenticated ? (
            <Layout>
              <DashboardPage />
            </Layout>
          ) : (
            <Navigate to="/login" />
          )
        }
      />
      <Route
        path="/upgrade"
        element={
          isAuthenticated ? (
            <Layout>
              <UpgradePage />
            </Layout>
          ) : (
            <Navigate to="/login" />
          )
        }
      />
      <Route
        path="/sms"
        element={
          isAuthenticated ? (
            <Layout>
              <SMSPage />
            </Layout>
          ) : (
            <Navigate to="/login" />
          )
        }
      />
      <Route
        path="/settings"
        element={
          isAuthenticated ? (
            <Layout>
              <SettingsPage />
            </Layout>
          ) : (
            <Navigate to="/login" />
          )
        }
      />

      {/* Admin Routes */}
      <Route
        path="/admin/dashboard"
        element={
          isAuthenticated && user?.role === 'admin' ? (
            <Layout>
              <AdminDashboard />
            </Layout>
          ) : (
            <Navigate to="/login" />
          )
        }
      />
      <Route
        path="/admin/payments"
        element={
          isAuthenticated && user?.role === 'admin' ? (
            <Layout>
              <AdminPayments />
            </Layout>
          ) : (
            <Navigate to="/login" />
          )
        }
      />
      <Route
        path="/admin/sms"
        element={
          isAuthenticated && user?.role === 'admin' ? (
            <Layout>
              <AdminSMS />
            </Layout>
          ) : (
            <Navigate to="/login" />
          )
        }
      />

      {/* Default Route */}
      <Route
        path="/"
        element={
          isAuthenticated ? (
            <Navigate to="/dashboard" />
          ) : (
            <Navigate to="/login" />
          )
        }
      />
    </Routes>
  )
}

export default App