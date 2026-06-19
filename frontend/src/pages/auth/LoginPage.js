import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { Mail, Lock, AlertCircle, Loader as LoaderIcon } from 'lucide-react';
import { useAuthStore } from '../../store/authStore';
import './AuthPage.css';
const LoginPage = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [errors, setErrors] = useState({});
    const { login, isLoading, error } = useAuthStore();
    const navigate = useNavigate();
    const validateForm = () => {
        const newErrors = {};
        if (!email)
            newErrors.email = 'Email is required';
        if (!password)
            newErrors.password = 'Password is required';
        return newErrors;
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        const newErrors = validateForm();
        setErrors(newErrors);
        if (Object.keys(newErrors).length === 0) {
            try {
                await login(email, password);
                navigate('/dashboard');
            }
            catch (err) {
                console.error('Login error:', err);
            }
        }
    };
    return (_jsx("div", { className: "auth-container", children: _jsxs("div", { className: "auth-card", children: [_jsxs("div", { className: "auth-header", children: [_jsx("div", { className: "auth-logo", children: "\uD83D\uDCCA" }), _jsx("h1", { children: "Sohoj Hishab" }), _jsx("p", { children: "Welcome Back!" })] }), _jsxs("form", { onSubmit: handleSubmit, className: "auth-form", children: [error && (_jsxs("div", { className: "error-banner", children: [_jsx(AlertCircle, { size: 20 }), error] })), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "email", children: "Email Address" }), _jsxs("div", { className: "input-group", children: [_jsx(Mail, { size: 20 }), _jsx("input", { id: "email", type: "email", value: email, onChange: (e) => setEmail(e.target.value), placeholder: "your@email.com", className: errors.email ? 'error' : '' })] }), errors.email && _jsx("span", { className: "error-text", children: errors.email })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "password", children: "Password" }), _jsxs("div", { className: "input-group", children: [_jsx(Lock, { size: 20 }), _jsx("input", { id: "password", type: "password", value: password, onChange: (e) => setPassword(e.target.value), placeholder: "\u2022\u2022\u2022\u2022\u2022\u2022\u2022\u2022", className: errors.password ? 'error' : '' })] }), errors.password && _jsx("span", { className: "error-text", children: errors.password })] }), _jsx("button", { type: "submit", className: "btn btn-primary", disabled: isLoading, children: isLoading ? (_jsxs(_Fragment, { children: [_jsx(LoaderIcon, { size: 18, className: "spinner" }), "Logging in..."] })) : ('Login') })] }), _jsx("div", { className: "auth-footer", children: _jsxs("p", { children: ["Don't have an account?", ' ', _jsx(Link, { to: "/register", className: "link", children: "Register here" })] }) })] }) }));
};
export default LoginPage;
