import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useEffect, useState } from 'react';
import { useSubscriptionStore } from '../../store/subscriptionStore';
import './UserPages.css';
const UpgradePage = () => {
    const { plans, fetchPlans, upgradeSubscription, isLoading, error } = useSubscriptionStore();
    const [selectedPlan, setSelectedPlan] = useState(null);
    const [selectedDuration, setSelectedDuration] = useState(30);
    const [paymentMethod, setPaymentMethod] = useState('nagad');
    const [formData, setFormData] = useState({
        paymentNumber: '',
        transactionId: '',
    });
    useEffect(() => {
        fetchPlans();
    }, []);
    const durations = [
        { days: 30, label: 'Monthly', multiplier: 1 },
        { days: 180, label: '6 Months', multiplier: 5.85 },
        { days: 365, label: 'Yearly', multiplier: 11.58 },
    ];
    const currentPlan = plans.find((p) => p.id === selectedPlan);
    const selectedDurationOption = durations.find((d) => d.days === selectedDuration);
    const totalPrice = currentPlan ? currentPlan.price * (selectedDurationOption?.multiplier || 1) : 0;
    const handleSubmit = async (e) => {
        e.preventDefault();
        if (!selectedPlan) {
            alert('Please select a plan');
            return;
        }
        try {
            await upgradeSubscription(selectedPlan, paymentMethod, formData.paymentNumber, formData.transactionId);
            alert('Payment submitted successfully! Admin will review and activate your plan.');
            setFormData({ paymentNumber: '', transactionId: '' });
        }
        catch (err) {
            console.error('Payment error:', err);
        }
    };
    return (_jsxs("div", { className: "upgrade-page", children: [_jsxs("div", { className: "page-header", children: [_jsx("h1", { children: "Upgrade Your Plan" }), _jsx("p", { children: "Choose the perfect plan for your business" })] }), _jsx("div", { className: "plans-grid", children: plans.map((plan) => (_jsxs("div", { className: `plan-card ${selectedPlan === plan.id ? 'selected' : ''}`, onClick: () => setSelectedPlan(plan.id), children: [_jsxs("div", { className: "plan-header", children: [_jsx("h3", { children: plan.name }), _jsx("p", { className: "plan-type", children: plan.plan_type.toUpperCase() })] }), _jsxs("div", { className: "plan-price", children: [_jsxs("span", { className: "price", children: ["\u09F3", plan.price] }), _jsx("span", { className: "period", children: "/month" })] }), _jsx("p", { className: "plan-description", children: plan.description }), _jsx("ul", { className: "plan-features", children: plan.features?.slice(0, 5).map((feature, idx) => (_jsxs("li", { children: ["\u2705 ", feature] }, idx))) }), _jsx("button", { className: `btn ${selectedPlan === plan.id ? 'btn-primary' : 'btn-secondary'}`, children: selectedPlan === plan.id ? '✓ Selected' : 'Select Plan' })] }, plan.id))) }), selectedPlan && (_jsx("div", { className: "payment-form-container", children: _jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "Payment Details" }) }), _jsxs("form", { onSubmit: handleSubmit, className: "payment-form", children: [_jsxs("div", { className: "form-section", children: [_jsx("h3", { children: "Duration" }), _jsx("div", { className: "duration-options", children: durations.map((duration) => (_jsxs("label", { className: "radio-label", children: [_jsx("input", { type: "radio", value: duration.days, checked: selectedDuration === duration.days, onChange: (e) => setSelectedDuration(parseInt(e.target.value)) }), duration.label] }, duration.days))) })] }), _jsxs("div", { className: "form-section", children: [_jsx("h3", { children: "Payment Method" }), _jsx("div", { className: "payment-methods", children: ['nagad', 'bkash', 'rocket'].map((method) => (_jsxs("label", { className: "radio-label", children: [_jsx("input", { type: "radio", value: method, checked: paymentMethod === method, onChange: (e) => setPaymentMethod(e.target.value) }), method.toUpperCase()] }, method))) })] }), _jsxs("div", { className: "payment-instructions", children: [_jsx("h4", { children: "\uD83D\uDCF1 Payment Instructions" }), _jsxs("p", { children: ["Send ", _jsxs("strong", { children: ["\u09F3", totalPrice.toFixed(0)] }), " to ", _jsxs("strong", { children: [paymentMethod.toUpperCase(), ": 01763206165"] })] }), _jsx("p", { children: "Then enter your payment reference number below" })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Payment Number" }), _jsx("input", { type: "text", placeholder: "INV-001 or reference number from payment", value: formData.paymentNumber, onChange: (e) => setFormData({ ...formData, paymentNumber: e.target.value }), required: true })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Transaction ID" }), _jsx("input", { type: "text", placeholder: "TXN-NAGAD-2024-001 or transaction ID", value: formData.transactionId, onChange: (e) => setFormData({ ...formData, transactionId: e.target.value }), required: true })] }), _jsxs("div", { className: "price-summary", children: [_jsxs("div", { className: "summary-row", children: [_jsx("span", { children: "Plan Price:" }), _jsxs("strong", { children: ["\u09F3", currentPlan?.price] })] }), _jsxs("div", { className: "summary-row", children: [_jsx("span", { children: "Duration:" }), _jsx("strong", { children: selectedDurationOption?.label })] }), _jsxs("div", { className: "summary-row total", children: [_jsx("span", { children: "Total Amount:" }), _jsxs("strong", { children: ["\u09F3", totalPrice.toFixed(0)] })] })] }), error && _jsx("div", { className: "alert alert-danger", children: error }), _jsx("button", { type: "submit", className: "btn btn-primary btn-lg", disabled: isLoading, children: isLoading ? 'Processing...' : 'Submit Payment' })] })] }) }))] }));
};
export default UpgradePage;
