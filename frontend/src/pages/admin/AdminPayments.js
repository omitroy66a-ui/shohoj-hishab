import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useEffect, useState } from 'react';
import { adminSubscriptionService } from '../../services/subscriptionService';
import { Check, X } from 'lucide-react';
import './AdminPages.css';
const AdminPayments = () => {
    const [payments, setPayments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedPaymentId, setSelectedPaymentId] = useState(null);
    const [discountData, setDiscountData] = useState({ amount: 0, type: 'flat' });
    useEffect(() => {
        loadPayments();
    }, []);
    const loadPayments = async () => {
        try {
            setLoading(true);
            const data = await adminSubscriptionService.getPendingPayments();
            setPayments(data);
        }
        catch (error) {
            console.error('Failed to load payments', error);
        }
        finally {
            setLoading(false);
        }
    };
    const handleApprove = async (paymentId) => {
        try {
            await adminSubscriptionService.approvePayment(paymentId);
            alert('Payment approved successfully!');
            loadPayments();
        }
        catch (error) {
            alert('Failed to approve: ' + error.response?.data?.message);
        }
    };
    const handleReject = async (paymentId) => {
        const reason = prompt('Enter rejection reason:');
        if (reason) {
            try {
                await adminSubscriptionService.rejectPayment(paymentId, reason);
                alert('Payment rejected!');
                loadPayments();
            }
            catch (error) {
                alert('Failed to reject: ' + error.response?.data?.message);
            }
        }
    };
    const handleApplyDiscount = async (paymentId) => {
        try {
            await adminSubscriptionService.applyDiscount(paymentId, discountData.amount, discountData.type);
            alert('Discount applied!');
            setSelectedPaymentId(null);
            loadPayments();
        }
        catch (error) {
            alert('Failed to apply discount: ' + error.response?.data?.message);
        }
    };
    return (_jsxs("div", { className: "admin-payments", children: [_jsxs("div", { className: "page-header", children: [_jsx("h1", { children: "Payment Management" }), _jsx("p", { children: "Review and approve pending payments" })] }), loading ? (_jsx("div", { className: "loading", children: "Loading payments..." })) : (_jsxs(_Fragment, { children: [payments.length > 0 ? (_jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsxs("h2", { children: ["Pending Payments (", payments.length, ")"] }) }), _jsx("div", { className: "card-body", children: _jsxs("table", { className: "table table-responsive", children: [_jsx("thead", { children: _jsxs("tr", { children: [_jsx("th", { children: "ID" }), _jsx("th", { children: "User" }), _jsx("th", { children: "Plan" }), _jsx("th", { children: "Amount" }), _jsx("th", { children: "Gateway" }), _jsx("th", { children: "Payment #" }), _jsx("th", { children: "Transaction ID" }), _jsx("th", { children: "Status" }), _jsx("th", { children: "Actions" })] }) }), _jsx("tbody", { children: payments.map((payment) => (_jsxs("tr", { children: [_jsxs("td", { children: ["#", payment.id] }), _jsx("td", { children: payment.user_name }), _jsx("td", { children: payment.plan_name }), _jsxs("td", { children: ["\u09F3", payment.amount] }), _jsx("td", { children: payment.gateway.toUpperCase() }), _jsx("td", { children: payment.payment_number }), _jsx("td", { children: payment.transaction_id }), _jsx("td", { children: _jsx("span", { className: `badge badge-${payment.status}`, children: payment.status }) }), _jsx("td", { children: _jsxs("div", { className: "action-buttons", children: [_jsx("button", { onClick: () => handleApprove(payment.id), className: "btn btn-sm btn-success", title: "Approve", children: _jsx(Check, { size: 16 }) }), _jsx("button", { onClick: () => handleReject(payment.id), className: "btn btn-sm btn-danger", title: "Reject", children: _jsx(X, { size: 16 }) }), _jsx("button", { onClick: () => setSelectedPaymentId(payment.id), className: "btn btn-sm btn-secondary", title: "Apply Discount", children: "\uD83D\uDCB0" })] }) })] }, payment.id))) })] }) })] })) : (_jsx("div", { className: "empty-state", children: _jsx("p", { children: "\u2705 No pending payments" }) })), selectedPaymentId && (_jsx("div", { className: "modal", children: _jsxs("div", { className: "modal-content", children: [_jsx("h3", { children: "Apply Discount" }), _jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Discount Amount" }), _jsx("input", { type: "number", value: discountData.amount, onChange: (e) => setDiscountData({ ...discountData, amount: parseFloat(e.target.value) }) })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Discount Type" }), _jsxs("select", { value: discountData.type, onChange: (e) => setDiscountData({ ...discountData, type: e.target.value }), children: [_jsx("option", { value: "flat", children: "Flat (\u09F3)" }), _jsx("option", { value: "percentage", children: "Percentage (%)" })] })] }), _jsxs("div", { className: "modal-actions", children: [_jsx("button", { onClick: () => handleApplyDiscount(selectedPaymentId), className: "btn btn-primary", children: "Apply" }), _jsx("button", { onClick: () => setSelectedPaymentId(null), className: "btn btn-secondary", children: "Cancel" })] })] }) }))] }))] }));
};
export default AdminPayments;
