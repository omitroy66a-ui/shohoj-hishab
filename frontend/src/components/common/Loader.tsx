import React from 'react'
import './Loader.css'

const Loader: React.FC = () => {
  return (
    <div className="loader-container">
      <div className="loader">
        <div className="loader-spinner"></div>
        <p>Loading...</p>
      </div>
    </div>
  )
}

export default Loader
