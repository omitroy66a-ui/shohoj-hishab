import React from 'react'

interface SubscriptionCardProps {
  subscription?: any
}

const SubscriptionCard: React.FC<SubscriptionCardProps> = ({ subscription }) => {
  return (
    <div className="subscription-card">
      {subscription ? (
        <div>
          <h3>{subscription.plan_name}</h3>
          <p>Status: {subscription.status}</p>
          <p>Expires: {subscription.expiry_date}</p>
        </div>
      ) : (
        <p>No active subscription</p>
      )}
    </div>
  )
}

export default SubscriptionCard
