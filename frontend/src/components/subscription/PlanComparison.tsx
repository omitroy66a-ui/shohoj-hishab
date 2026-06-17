import React from 'react'

const PlanComparison: React.FC = () => {
  return (
    <div className="plan-comparison">
      <table className="comparison-table">
        <thead>
          <tr>
            <th>Feature</th>
            <th>Trial</th>
            <th>Standard</th>
            <th>Advanced</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Price</td>
            <td>Free</td>
            <td>৳60/month</td>
            <td>৳199/month</td>
          </tr>
          <tr>
            <td>POS System</td>
            <td>✅</td>
            <td>✅</td>
            <td>✅</td>
          </tr>
          <tr>
            <td>Inventory</td>
            <td>✅</td>
            <td>✅</td>
            <td>✅</td>
          </tr>
          <tr>
            <td>Multi-Branch</td>
            <td>✅</td>
            <td>❌</td>
            <td>✅</td>
          </tr>
          <tr>
            <td>E-commerce</td>
            <td>✅</td>
            <td>❌</td>
            <td>✅</td>
          </tr>
          <tr>
            <td>Advanced Reports</td>
            <td>✅</td>
            <td>❌</td>
            <td>✅</td>
          </tr>
          <tr>
            <td>API Access</td>
            <td>✅</td>
            <td>❌</td>
            <td>✅</td>
          </tr>
        </tbody>
      </table>
    </div>
  )
}

export default PlanComparison
