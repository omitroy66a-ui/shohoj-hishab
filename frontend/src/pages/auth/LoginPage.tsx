import React, { useState } from 'react'
import { useNavigate, Link } from 'react-router-dom'
import { Phone, Lock, AlertCircle, Loader as LoaderIcon } from 'lucide-react'
import { useAuthStore } from '../../store/authStore'
import { FormErrors, AuthError } from '../../types'
import './AuthPage.css'

const LoginPage: React.FC = () => {
  const [phone, setPhone] = useState('')
  const [password, setPassword] = useState('')
  const [errors, setErrors] = useState<FormErrors>({})
  const { login, isLoading, error } = useAuthStore()
  const navigate = useNavigate()

  const validateForm = (): FormErrors => {
    const newErrors: FormErrors = {}

    if (!phone.trim()) newErrors.phone = 'Mobile number is required'
    if (!password) newErrors.password = 'Password is required'

    return newErrors
  }

  const handleSubmit = async (e: React.FormEvent): Promise<void> => {
    e.preventDefault()

    const newErrors = validateForm()
    setErrors(newErrors)

    if (Object.keys(newErrors).length > 0) {
      return
    }

    try {
      await login(phone.trim(), password)
      navigate('/dashboard')
    } catch (err) {
      const authError = err as AuthError
      console.error('Login error:', authError.message)
    }
  }

  return (
    <div className="auth-container">
      <div className="auth-card">
        <div className="auth-header">
          <div className="auth-logo">SH</div>
          <h1>Sohoj Hishab</h1>
          <p>Welcome Back!</p>
        </div>

        <form onSubmit={handleSubmit} className="auth-form">
          {error && (
            <div className="error-banner">
              <AlertCircle size={20} />
              {error}
            </div>
          )}

          <div className="form-group">
            <label htmlFor="phone">Mobile Number</label>
            <div className="input-group">
              <Phone size={20} />
              <input
                id="phone"
                name="phone"
                type="tel"
                value={phone}
                onChange={(e) => {
                  setPhone(e.target.value)
                  setErrors((prev) => ({ ...prev, phone: '' }))
                }}
                placeholder="01700000000"
                className={errors.phone ? 'error' : ''}
              />
            </div>
            {errors.phone && <span className="error-text">{errors.phone}</span>}
          </div>

          <div className="form-group">
            <label htmlFor="password">Password</label>
            <div className="input-group">
              <Lock size={20} />
              <input
                id="password"
                name="password"
                type="password"
                value={password}
                onChange={(e) => {
                  setPassword(e.target.value)
                  setErrors((prev) => ({ ...prev, password: '' }))
                }}
                placeholder="********"
                className={errors.password ? 'error' : ''}
              />
            </div>
            {errors.password && <span className="error-text">{errors.password}</span>}
          </div>

          <button type="submit" className="btn btn-primary" disabled={isLoading}>
            {isLoading ? (
              <>
                <LoaderIcon size={18} className="spinner" />
                Logging in...
              </>
            ) : (
              'Login'
            )}
          </button>
        </form>

        <div className="auth-footer">
          <p>
            Don't have an account?{' '}
            <Link to="/register" className="link">
              Register here
            </Link>
          </p>
        </div>
      </div>
    </div>
  )
}

export default LoginPage
