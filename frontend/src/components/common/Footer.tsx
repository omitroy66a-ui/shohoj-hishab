import React from 'react'
import { Mail, Globe, Code } from 'lucide-react'
import './Footer.css'

const Footer: React.FC = () => {
  const currentYear = new Date().getFullYear()

  return (
    <footer className="footer">
      <div className="footer-container">
        <div className="footer-content">
          <div className="footer-section">
            <h3>Sohoj Hishab</h3>
            <p>Complete SaaS subscription management system for modern businesses</p>
          </div>

          <div className="footer-section">
            <h4>Quick Links</h4>
            <ul>
              <li><a href="/dashboard">Dashboard</a></li>
              <li><a href="/upgrade">Upgrade Plan</a></li>
              <li><a href="/settings">Settings</a></li>
            </ul>
          </div>

          <div className="footer-section">
            <h4>Follow Us</h4>
            <div className="social-links">
              <a href="#" title="Website"><Globe size={20} /></a>
              <a href="#" title="Code"><Code size={20} /></a>
              <a href="mailto:info@sohojhishab.com" title="Email"><Mail size={20} /></a>
            </div>
          </div>
        </div>

        <div className="footer-bottom">
          <p>&copy; {currentYear} Sohoj Hishab. All rights reserved.</p>
          <div className="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Contact</a>
          </div>
        </div>
      </div>
    </footer>
  )
}

export default Footer
