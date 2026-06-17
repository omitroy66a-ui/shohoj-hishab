import React, { useEffect, useState } from 'react'
import { useSubscriptionStore } from '../../store/subscriptionStore'
import { subscriptionService } from '../../services/subscriptionService'
import './UserPages.css'

const UpgradePage: React.FC = () => {
  const { plans, fetchPlans, upgradeSubscription, isLoading, error } = useSubscriptionStore()
  const [selectedPlan, setSelectedPlan] = useState<number | null>(null)
  const [selectedDuration, setSelectedDuration] = useState<number>(30)
  const [paymentMethod, setPaymentMethod] = useState('nagad')
  const [formData, setFormData] = useState({
    paymentNumber: '',
    transactionId: '',
  })

  useEffect(() => {
    fetchPlans()
  }, [])

  const durations = [
    { days: 30, label: 'Monthly', multiplier: 1 },
    { days: 180, label: '6 Months', multiplier: 5.85 },
    { days: 365, label: 'Yearly', multiplier: 11.58 },
  ]

  const currentPlan = plans.find((p) => p.id === selectedPlan)
  const selectedDurationOption = durations.find((d) => d.days === selectedDuration)
  const totalPrice = currentPlan ? currentPlan.price * (selectedDurationOption?.multiplier || 1) : 0

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!selectedPlan) {
      alert('Please select a plan')
      return
    }

    try {
      await upgradeSubscription(selectedPlan, paymentMethod, formData.paymentNumber, formData.transactionId)
      alert('Payment submitted successfully! Admin will review and activate your plan.')
      setFormData({ paymentNumber: '', transactionId: '' })
    } catch (err) {
      console.error('Payment error:', err)
    }
  }

  return (
    <div className="upgrade-page">
      <div className="page-header">
        <h1>Upgrade Your Plan</h1>
        <p>Choose the perfect plan for your business</p>
      </div>

      <div className="plans-grid">
        {plans.map((plan) => (
          <div
            key={plan.id}
            className={`plan-card ${selectedPlan === plan.id ? 'selected' : ''}`}
            onClick={() => setSelectedPlan(plan.id)}
          >
            <div className="plan-header">
              <h3>{plan.name}</h3>
              <p className="plan-type">{plan.plan_type.toUpperCase()}</p>
            </div>

            <div className="plan-price">
              <span className="price">৳{plan.price}</span>
              <span className="period">/month</span>
            </div>

            <p className="plan-description">{plan.description}</p>

            <ul className="plan-features">
              {plan.features?.slice(0, 5).map((feature, idx) => (
                <li key={idx}>✅ {feature}</li>
              ))}
            </ul>

            <button className={`btn ${selectedPlan === plan.id ? 'btn-primary' : 'btn-secondary'}`}>
              {selectedPlan === plan.id ? '✓ Selected' : 'Select Plan'}
            </button>
          </div>
        ))}
      </div>

      {selectedPlan && (
        <div className="payment-form-container">
          <div className="card">
            <div className="card-header">
              <h2>Payment Details</h2>
            </div>

            <form onSubmit={handleSubmit} className="payment-form">
              <div className="form-section">
                <h3>Duration</h3>
                <div className="duration-options">
                  {durations.map((duration) => (
                    <label key={duration.days} className="radio-label">
                      <input
                        type="radio"
                        value={duration.days}
                        checked={selectedDuration === duration.days}
                        onChange={(e) => setSelectedDuration(parseInt(e.target.value))}
                      />
                      {duration.label}
                    </label>
                  ))}
                </div>
              </div>

              <div className="form-section">
                <h3>Payment Method</h3>
                <div className="payment-methods">
                  {['nagad', 'bkash', 'rocket'].map((method) => (
                    <label key={method} className="radio-label">
                      <input
                        type="radio"
                        value={method}
                        checked={paymentMethod === method}
                        onChange={(e) => setPaymentMethod(e.target.value)}
                      />
                      {method.toUpperCase()}
                    </label>
                  ))}
                </div>
              </div>

              <div className="payment-instructions">
                <h4>📱 Payment Instructions</h4>
                <p>Send <strong>৳{totalPrice.toFixed(0)}</strong> to <strong>{paymentMethod.toUpperCase()}: 01763206165</strong></p>
                <p>Then enter your payment reference number below</p>
              </div>

              <div className="form-group">
                <label>Payment Number</label>
                <input
                  type="text"
                  placeholder="INV-001 or reference number from payment"
                  value={formData.paymentNumber}
                  onChange={(e) => setFormData({ ...formData, paymentNumber: e.target.value })}
                  required
                />
              </div>

              <div className="form-group">
                <label>Transaction ID</label>
                <input
                  type="text"
                  placeholder="TXN-NAGAD-2024-001 or transaction ID"
                  value={formData.transactionId}
                  onChange={(e) => setFormData({ ...formData, transactionId: e.target.value })}
                  required
                />
              </div>

              <div className="price-summary">
                <div className="summary-row">
                  <span>Plan Price:</span>
                  <strong>৳{currentPlan?.price}</strong>
                </div>
                <div className="summary-row">
                  <span>Duration:</span>
                  <strong>{selectedDurationOption?.label}</strong>
                </div>
                <div className="summary-row total">
                  <span>Total Amount:</span>
                  <strong>৳{totalPrice.toFixed(0)}</strong>
                </div>
              </div>

              {error && <div className="alert alert-danger">{error}</div>}

              <button type="submit" className="btn btn-primary btn-lg" disabled={isLoading}>
                {isLoading ? 'Processing...' : 'Submit Payment'}
              </button>
            </form>
          </div>
        </div>
      )}
    </div>
  )
}

export default UpgradePage
