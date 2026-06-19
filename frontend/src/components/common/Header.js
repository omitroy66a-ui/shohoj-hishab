import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { Menu, X, LogOut, BarChart3, MessageSquare, Settings } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import './Header.css';
const Header = () => {
    const [isOpen, setIsOpen] = React.useState(false);
    const { user, logout } = useAuthStore();
    const location = useLocation();
    const isAdmin = user?.role === 'admin';
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
        ];
    const handleLogout = () => {
        logout();
        window.location.href = '/login';
    };
    return (_jsx("header", { className: "header", children: _jsxs("div", { className: "header-container", children: [_jsxs(Link, { to: "/dashboard", className: "logo", children: [_jsx("span", { className: "logo-icon", children: "\uD83D\uDCCA" }), "Sohoj Hishab"] }), _jsx("nav", { className: `nav ${isOpen ? 'nav-open' : ''}`, children: navLinks.map((link) => (_jsx(Link, { to: link.path, className: `nav-link ${location.pathname === link.path ? 'active' : ''}`, children: link.label }, link.path))) }), _jsxs("div", { className: "header-actions", children: [_jsxs("div", { className: "user-info", children: [_jsx("span", { className: "user-name", children: user?.name }), _jsx("span", { className: "user-role", children: user?.role === 'admin' ? '👑 Admin' : '👤 User' })] }), _jsx("button", { onClick: handleLogout, className: "logout-btn", title: "Logout", children: _jsx(LogOut, { size: 20 }) })] }), _jsx("button", { className: "mobile-menu-btn", onClick: () => setIsOpen(!isOpen), children: isOpen ? _jsx(X, { size: 24 }) : _jsx(Menu, { size: 24 }) })] }) }));
};
export default Header;
