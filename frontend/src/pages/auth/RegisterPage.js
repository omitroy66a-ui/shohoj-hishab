import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { User, Mail, Phone, Building, Lock, AlertCircle, Loader as LoaderIcon } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import './AuthPage.css';
const RegisterPage = () => {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        phone: '',
        business_name: '',
        password: '',
        confirmPassword: '',
    });
    const [errors, setErrors] = useState({});
    const { register, isLoading, error } = useAuthStore();
    const navigate = useNavigate();
    const validateForm = () => {
        const newErrors = {};
        if (!formData.name)
            newErrors.name = 'Full name is required';
        if (!formData.email)
            newErrors.email = 'Email is required';
        if (!formData.phone)
            newErrors.phone = 'Phone number is required';
        if (!formData.business_name)
            newErrors.business_name = 'Business name is required';
        if (!formData.password)
            newErrors.password = 'Password is required';
        if (formData.password.length < 6)
            newErrors.password = 'Password must be at least 6 characters';
        if (formData.password !== formData.confirmPassword)
            newErrors.confirmPassword = 'Passwords do not match';
        return newErrors;
    };
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: value,
        }));
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        const newErrors = validateForm();
        setErrors(newErrors);
        if (Object.keys(newErrors).length === 0) {
            try {
                await register(formData);
                navigate('/dashboard');
            }
            catch (err) {
                console.error('Register error:', err);
            }
        }
    };
    return (_jsx("div", { className: "auth-container", children: _jsxs("div", { className: "auth-card auth-card-large", children: [_jsxs("div", { className: "auth-header", children: [_jsx("div", { className: "auth-logo", children: "\uD83D\uDCCA" }), _jsx("h1", { children: "Sohoj Hishab" }), _jsx("p", { children: "Create Your Account" })] }), _jsxs("form", { onSubmit: handleSubmit, className: "auth-form", children: [error && (_jsxs("div", { className: "error-banner", children: [_jsx(AlertCircle, { size: 20 }), error] })), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "name", children: "Full Name" }), _jsxs("div", { className: "input-group", children: [_jsx(User, { size: 20 }), _jsx("input", { id: "name", name: "name", type: "text", value: formData.name, onChange: handleChange, placeholder: "Your Name", className: errors.name ? 'error' : '' })] }), errors.name && _jsx("span", { className: "error-text", children: errors.name })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "email", children: "Email Address" }), _jsxs("div", { className: "input-group", children: [_jsx(Mail, { size: 20 }), _jsx("input", { id: "email", name: "email", type: "email", value: formData.email, onChange: handleChange, placeholder: "your@email.com", className: errors.email ? 'error' : '' })] }), errors.email && _jsx("span", { className: "error-text", children: errors.email })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "phone", children: "Phone Number" }), _jsxs("div", { className: "input-group", children: [_jsx(Phone, { size: 20 }), _jsx("input", { id: "phone", name: "phone", type: "tel", value: formData.phone, onChange: handleChange, placeholder: "01700000000", className: errors.phone ? 'error' : '' })] }), errors.phone && _jsx("span", { className: "error-text", children: errors.phone })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "business_name", children: "Business Name" }), _jsxs("div", { className: "input-group", children: [_jsx(Building, { size: 20 }), _jsx("input", { id: "business_name", name: "business_name", type: "text", value: formData.business_name, onChange: handleChange, placeholder: "Your Business Name", className: errors.business_name ? 'error' : '' })] }), errors.business_name && _jsx("span", { className: "error-text", children: errors.business_name })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "password", children: "Password" }), _jsxs("div", { className: "input-group", children: [_jsx(Lock, { size: 20 }), _jsx("input", { id: "password", name: "password", type: "password", value: formData.password, onChange: handleChange, placeholder: "\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022", className: errors.password ? 'error' : '' })] }), errors.password && _jsx("span", { className: "error-text", children: errors.password })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "confirmPassword", children: "Confirm Password" }), _jsxs("div", { className: "input-group", children: [_jsx(Lock, { size: 20 }), _jsx("input", { id: "confirmPassword", name: "confirmPassword", type: "password", value: formData.confirmPassword, onChange: handleChange, placeholder: "\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022", className: errors.confirmPassword ? 'error' : '' })] }), errors.confirmPassword && _jsx("span", { className: "error-text", children: errors.confirmPassword })] }), _jsx("button", { type: "submit", className: "btn btn-primary", disabled: isLoading, children: isLoading ? (_jsxs(_Fragment, { children: [_jsx(LoaderIcon, { size: 18, className: "spinner" }), "Creating Account..."] })) : ('Register') })] }), _jsx("div", { className: "auth-footer", children: _jsxs("p", { children: ["Already have an account?", ' ', _jsx(Link, { to: "/login", className: "link", children: "Login here" })] }) })] }) }));
};
export default RegisterPage;
