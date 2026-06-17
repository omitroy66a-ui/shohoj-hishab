import React from 'react'

const FeaturesList: React.FC = () => {
  const features = [
    { name: 'POS System', icon: '🛒' },
    { name: 'Inventory Management', icon: '📦' },
    { name: 'Customer Management', icon: '👥' },
    { name: 'Supplier Management', icon: '🏭' },
    { name: 'Expense Tracking', icon: '💸' },
    { name: 'Advanced Accounting', icon: '📊' },
    { name: 'Reports & Analytics', icon: '📈' },
    { name: 'Multi-Branch Support', icon: '🏢' },
    { name: 'Mobile App API', icon: '📱' },
    { name: 'E-commerce Integration', icon: '🌐' },
    { name: 'SMS Notifications', icon: '📬' },
    { name: 'API Access', icon: '⚙️' },
  ]

  return (
    <div className="features-grid">
      {features.map((feature, idx) => (
        <div key={idx} className="feature-item">
          <span className="feature-icon">{feature.icon}</span>
          <span className="feature-name">{feature.name}</span>
        </div>
      ))}
    </div>
  )
}

export default FeaturesList
