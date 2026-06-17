import React from 'react'
import { Link, useLocation } from 'react-router-dom'
import { Menu, X, LogOut, BarChart3, MessageSquare, Settings } from 'lucide-react'
import { useAuthStore } from '../../store/authStore'
import './Header.css'

const Header: React.FC = () => {
  const [isOpen, setIsOpen] = React.useState(false)
  const { user, logout } = useAuthStore()
  const location = useLocation()

  const isAdmin = user?.role === 'admin'

  const navLinks = isAdmin
    ? [
        { path: '/admin/dashboard', label: 'Dashboard', icon: BarChart3 },
        { path: '/admin/payments', label: 'Payments', icon: '💳' },
        { path: '/admin/sms', label: 'SMS', icon: MessageSquare },
      ]
    : [
        { path: '/dashboard', label: 'Dashboard', icon: BarChart3 },
        { path: '/upgrade', label: 'Upgrade', icon: '⬆️' },
        { path: '/sms', label: 'SMS', icon: MessageSquare },
        { path: '/settings', label: 'Settings', icon: Settings },
      ]

  const handleLogout = () => {
    logout()
    window.location.href = '/login'
  }

  return (
    <header className="header">
      <div className="header-container">
        <Link to="/dashboard" className="logo">
          <span className="logo-icon">📊</span>
          Sohoj Hishab
        </Link>

        <nav className={`nav ${isOpen ? 'nav-open' : ''}`}>
          {navLinks.map((link) => (
            <Link
              key={link.path}
              to={link.path}
              className={`nav-link ${location.pathname === link.path ? 'active' : ''}`}
            >
              {link.label}
            </Link>
          ))}
        </nav>

        <div className="header-actions">
          <div className="user-info">
            <span className="user-name">{user?.name}</span>
            <span className="user-role">{user?.role === 'admin' ? '👑 Admin' : '👤 User'}</span>
          </div>
          <button onClick={handleLogout} className="logout-btn" title="Logout">
            <LogOut size={20} />
          </button>
        </div>

        <button className="mobile-menu-btn" onClick={() => setIsOpen(!isOpen)}>
          {isOpen ? <X size={24} /> : <Menu size={24} />}
        </button>
      </div>
    </header>
  )
}

export default Header
