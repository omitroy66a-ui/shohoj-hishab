import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useEffect, useState } from 'react';
import { adminSMSService } from '../../services/smsService';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import './AdminPages.css';
const AdminDashboard = () => {
    const [dashboardData, setDashboardData] = useState({});
    const [loading, setLoading] = useState(true);
    const [smsStats, setSmsStats] = useState({});
    useEffect(() => {
        loadDashboardData();
    }, []);
    const loadDashboardData = async () => {
        try {
            setLoading(true);
            const smsData = await adminSMSService.getSMSStats();
            setSmsStats(smsData);
            setDashboardData({
                totalUsers: 150,
                activeSubscriptions: 87,
                totalRevenue: 15420,
                pendingPayments: 12,
            });
        }
        catch (error) {
            console.error('Failed to load dashboard', error);
        }
        finally {
            setLoading(false);
        }
    };
    const chartData = [
        { name: 'Trial', users: 45 },
        { name: 'Standard', users: 38 },
        { name: 'Advanced', users: 4 },
    ];
    return (_jsxs("div", { className: "admin-dashboard", children: [_jsxs("div", { className: "page-header", children: [_jsx("h1", { children: "Admin Dashboard" }), _jsx("p", { children: "System overview and key metrics" })] }), _jsxs("div", { className: "stats-grid", children: [_jsxs("div", { className: "stat-card", children: [_jsxs("div", { className: "stat-header", children: [_jsx("span", { className: "stat-icon", children: "\uD83D\uDC65" }), _jsx("h3", { children: "Total Users" })] }), _jsx("div", { className: "stat-value", children: dashboardData.totalUsers || 0 })] }), _jsxs("div", { className: "stat-card", children: [_jsxs("div", { className: "stat-header", children: [_jsx("span", { className: "stat-icon", children: "\u2705" }), _jsx("h3", { children: "Active Subscriptions" })] }), _jsx("div", { className: "stat-value", children: dashboardData.activeSubscriptions || 0 })] }), _jsxs("div", { className: "stat-card", children: [_jsxs("div", { className: "stat-header", children: [_jsx("span", { className: "stat-icon", children: "\uD83D\uDCB0" }), _jsx("h3", { children: "Total Revenue" })] }), _jsxs("div", { className: "stat-value", children: ["\u09F3", dashboardData.totalRevenue || 0] })] }), _jsxs("div", { className: "stat-card", children: [_jsxs("div", { className: "stat-header", children: [_jsx("span", { className: "stat-icon", children: "\u23F3" }), _jsx("h3", { children: "Pending Payments" })] }), _jsx("div", { className: "stat-value highlight", children: dashboardData.pendingPayments || 0 })] })] }), _jsxs("div", { className: "dashboard-grid", children: [_jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "Subscriptions by Plan" }) }), _jsx("div", { className: "card-body", children: _jsx(ResponsiveContainer, { width: "100%", height: 300, children: _jsxs(BarChart, { data: chartData, children: [_jsx(CartesianGrid, { strokeDasharray: "3 3" }), _jsx(XAxis, { dataKey: "name" }), _jsx(YAxis, {}), _jsx(Tooltip, {}), _jsx(Legend, {}), _jsx(Bar, { dataKey: "users", fill: "#8884d8" })] }) }) })] }), _jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "SMS Statistics" }) }), _jsxs("div", { className: "card-body", children: [_jsxs("div", { className: "stat-item", children: [_jsx("label", { children: "Total SMS Sent" }), _jsx("strong", { children: smsStats.total_sent || 0 })] }), _jsxs("div", { className: "stat-item", children: [_jsx("label", { children: "SMS Delivered" }), _jsx("strong", { children: smsStats.delivered || 0 })] }), _jsxs("div", { className: "stat-item", children: [_jsx("label", { children: "SMS Failed" }), _jsx("strong", { children: smsStats.failed || 0 })] })] })] })] })] }));
};
export default AdminDashboard;
