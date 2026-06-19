import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
const SubscriptionCard = ({ subscription }) => {
    return (_jsx("div", { className: "subscription-card", children: subscription ? (_jsxs("div", { children: [_jsx("h3", { children: subscription.plan_name }), _jsxs("p", { children: ["Status: ", subscription.status] }), _jsxs("p", { children: ["Expires: ", subscription.expiry_date] })] })) : (_jsx("p", { children: "No active subscription" })) }));
};
export default SubscriptionCard;
