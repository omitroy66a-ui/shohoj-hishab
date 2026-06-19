import React, { useEffect, useState } from 'react'
import { adminSubscriptionService } from '../../services/subscriptionService'
import { Check, X } from 'lucide-react'
import './AdminPages.css'

const AdminPayments: React.FC = () => {
  const [payments, setPayments] = useState<any[]>([])
  const [loading, setLoading] = useState(true)
  const [selectedPaymentId, setSelectedPaymentId] = useState<number | null>(null)
  const [discountData, setDiscountData] = useState({ amount: 0, type: 'flat' as 'flat' | 'percentage' })

  useEffect(() => {
    loadPayments()
  }, [])

  const loadPayments = async () => {
    try {
      setLoading(true)
      const data = await adminSubscriptionService.getPendingPayments()
      setPayments(data)
    } catch (error) {
      console.error('Failed to load payments', error)
    } finally {
      setLoading(false)
    }
  }

  const handleApprove = async (paymentId: number) => {
    try {
      await adminSubscriptionService.approvePayment(paymentId)
      alert('Payment approved successfully!')
      loadPayments()
    } catch (error: any) {
      alert('Failed to approve: ' + error.response?.data?.message)
    }
  }

  const handleReject = async (paymentId: number) => {
    const reason = prompt('Enter rejection reason:')
    if (reason) {
      try {
        await adminSubscriptionService.rejectPayment(paymentId, reason)
        alert('Payment rejected!')
        loadPayments()
      } catch (error: any) {
        alert('Failed to reject: ' + error.response?.data?.message)
      }
    }
  }

  const handleApplyDiscount = async (paymentId: number) => {
    try {
      await adminSubscriptionService.applyDiscount(
        paymentId,
        discountData.amount,
        discountData.type
      )
      alert('Discount applied!')
      setSelectedPaymentId(null)
      loadPayments()
    } catch (error: any) {
      alert('Failed to apply discount: ' + error.response?.data?.message)
    }
  }

  return (
    <div className="admin-payments">
      <div className="page-header">
        <h1>Payment Management</h1>
        <p>Review and approve pending payments</p>
      </div>

      {loading ? (
        <div className="loading">Loading payments...</div>
      ) : (
        <>
          {payments.length > 0 ? (
            <div className="card">
              <div className="card-header">
                <h2>Pending Payments ({payments.length})</h2>
              </div>
              <div className="card-body">
                <table className="table table-responsive">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>User</th>
                      <th>Plan</th>
                      <th>Amount</th>
                      <th>Gateway</th>
                      <th>Payment #</th>
                      <th>Transaction ID</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {payments.map((payment) => (
                      <tr key={payment.id}>
                        <td>#{payment.id}</td>
                        <td>{payment.user_name}</td>
                        <td>{payment.plan_name}</td>
                        <td>৳{payment.amount}</td>
                        <td>{payment.gateway.toUpperCase()}</td>
                        <td>{payment.payment_number}</td>
                        <td>{payment.transaction_id}</td>
                        <td>
                          <span className={`badge badge-${payment.status}`}>
                            {payment.status}
                          </span>
                        </td>
                        <td>
                          <div className="action-buttons">
                            <button
                              onClick={() => handleApprove(payment.id)}
                              className="btn btn-sm btn-success"
                              title="Approve"
                            >
                              <Check size={16} />
                            </button>
                            <button
                              onClick={() => handleReject(payment.id)}
                              className="btn btn-sm btn-danger"
                              title="Reject"
                            >
                              <X size={16} />
                            </button>
                            <button
                              onClick={() => setSelectedPaymentId(payment.id)}
                              className="btn btn-sm btn-secondary"
                              title="Apply Discount"
                            >
                              💰
                            </button>
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          ) : (
            <div className="empty-state">
              <p>✅ No pending payments</p>
            </div>
          )}

          {selectedPaymentId && (
            <div className="modal">
              <div className="modal-content">
                <h3>Apply Discount</h3>
                <div className="form-group">
                  <label>Discount Amount</label>
                  <input
                    type="number"
                    value={discountData.amount}
                    onChange={(e) => setDiscountData({ ...discountData, amount: parseFloat(e.target.value) })}
                  />
                </div>
                <div className="form-group">
                  <label>Discount Type</label>
                  <select
                    value={discountData.type}
                    onChange={(e) => setDiscountData({ ...discountData, type: e.target.value as 'flat' | 'percentage' })}
                  >
                    <option value="flat">Flat (৳)</option>
                    <option value="percentage">Percentage (%)</option>
                  </select>
                </div>
                <div className="modal-actions">
                  <button
                    onClick={() => handleApplyDiscount(selectedPaymentId)}
                    className="btn btn-primary"
                  >
                    Apply
                  </button>
                  <button onClick={() => setSelectedPaymentId(null)} className="btn btn-secondary">
                    Cancel
                  </button>
                </div>
              </div>
            </div>
          )}
        </>
      )}
    </div>
  )
}

export default AdminPayments
