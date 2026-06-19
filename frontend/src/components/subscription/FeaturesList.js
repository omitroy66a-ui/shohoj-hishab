import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
const FeaturesList = () => {
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
    ];
    return (_jsx("div", { className: "features-grid", children: features.map((feature, idx) => (_jsxs("div", { className: "feature-item", children: [_jsx("span", { className: "feature-icon", children: feature.icon }), _jsx("span", { className: "feature-name", children: feature.name })] }, idx))) }));
};
export default FeaturesList;
