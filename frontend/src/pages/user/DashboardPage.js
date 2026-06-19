import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useEffect } from 'react';
import { useSubscriptionStore } from '../../store/subscriptionStore';
import { useAuthStore } from '../../store/authStore';
import PlanComparison from '../../components/subscription/PlanComparison';
import FeaturesList from '../../components/subscription/FeaturesList';
import './UserPages.css';
const DashboardPage = () => {
    const { subscription, fetchSubscription, isLoading } = useSubscriptionStore();
    const { user } = useAuthStore();
    useEffect(() => {
        fetchSubscription();
    }, []);
    const getStatusColor = (status) => {
        switch (status) {
            case 'active':
                return 'success';
            case 'expired':
                return 'danger';
            case 'cancelled':
                return 'warning';
            default:
                return 'info';
        }
    };
    return (_jsxs("div", { className: "dashboard-page", children: [_jsxs("div", { className: "page-header", children: [_jsx("h1", { children: "Dashboard" }), _jsxs("p", { children: ["Welcome back, ", user?.name, "! \uD83D\uDC4B"] })] }), _jsxs("div", { className: "dashboard-grid", children: [_jsxs("div", { className: "card subscription-card", children: [_jsxs("div", { className: "card-header", children: [_jsx("h2", { children: "Current Subscription" }), _jsx("span", { className: `badge badge-${subscription?.status || 'info'}`, children: subscription?.status?.toUpperCase() || 'NO PLAN' })] }), _jsx("div", { className: "card-body", children: subscription ? (_jsxs(_Fragment, { children: [_jsxs("div", { className: "subscription-info", children: [_jsxs("div", { className: "info-row", children: [_jsx("label", { children: "Plan:" }), _jsx("strong", { children: subscription.plan_name })] }), _jsxs("div", { className: "info-row", children: [_jsx("label", { children: "Plan Type:" }), _jsx("strong", { children: subscription.plan_type.toUpperCase() })] }), _jsxs("div", { className: "info-row", children: [_jsx("label", { children: "Price:" }), _jsxs("strong", { children: ["\u09F3", subscription.price, "/month"] })] }), _jsxs("div", { className: "info-row", children: [_jsx("label", { children: "Start Date:" }), _jsx("strong", { children: new Date(subscription.start_date).toLocaleDateString() })] }), _jsxs("div", { className: "info-row", children: [_jsx("label", { children: "Expiry Date:" }), _jsx("strong", { children: new Date(subscription.expiry_date).toLocaleDateString() })] }), _jsxs("div", { className: "info-row highlight", children: [_jsx("label", { children: "Days Remaining:" }), _jsxs("strong", { children: [subscription.daysRemaining, " days"] })] })] }), subscription.daysRemaining <= 7 && (_jsx("div", { className: "alert alert-warning", children: "\u26A0\uFE0F Your subscription will expire soon! Consider upgrading to continue using all features." }))] })) : (_jsx("div", { className: "alert alert-info", children: "\uD83D\uDCCC You don't have an active subscription. Start with a free trial or upgrade to a premium plan." })) })] }), _jsxs("div", { className: "card stats-card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "Quick Stats" }) }), _jsxs("div", { className: "card-body", children: [_jsxs("div", { className: "stat-item", children: [_jsx("div", { className: "stat-value", children: "3" }), _jsx("div", { className: "stat-label", children: "Available Plans" })] }), _jsxs("div", { className: "stat-item", children: [_jsx("div", { className: "stat-value", children: "\u221E" }), _jsx("div", { className: "stat-label", children: "Features Unlocked" })] }), _jsxs("div", { className: "stat-item", children: [_jsx("div", { className: "stat-value", children: "24/7" }), _jsx("div", { className: "stat-label", children: "Support" })] })] })] })] }), _jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "Available Features" }) }), _jsx("div", { className: "card-body", children: _jsx(FeaturesList, {}) })] }), _jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "Plan Comparison" }) }), _jsx("div", { className: "card-body", children: _jsx(PlanComparison, {}) })] })] }));
};
export default DashboardPage;
