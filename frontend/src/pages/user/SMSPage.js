import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useEffect, useState } from 'react';
import { smsService } from '../../services/smsService';
import { MessageSquare } from 'lucide-react';
import './UserPages.css';
const SMSPage = () => {
    const [smsTabs, setSmsTabs] = useState('logs');
    const [phoneNumber, setPhoneNumber] = useState('');
    const [message, setMessage] = useState('');
    const [smsLogs, setSmsLogs] = useState([]);
    const [loading, setLoading] = useState(false);
    useEffect(() => {
        if (smsTabs === 'logs') {
            loadSMSLogs();
        }
    }, [smsTabs]);
    const loadSMSLogs = async () => {
        setLoading(true);
        try {
            const data = await smsService.getSMSLogs();
            setSmsLogs(data);
        }
        catch (error) {
            console.error('Failed to load SMS logs', error);
        }
        setLoading(false);
    };
    const handleSendSMS = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            await smsService.sendTestSMS(phoneNumber, message);
            alert('SMS sent successfully!');
            setPhoneNumber('');
            setMessage('');
        }
        catch (error) {
            alert('Failed to send SMS: ' + (error.response?.data?.message || 'Unknown error'));
        }
        setLoading(false);
    };
    return (_jsxs("div", { className: "sms-page", children: [_jsxs("div", { className: "page-header", children: [_jsx("h1", { children: "SMS Notifications" }), _jsx("p", { children: "Manage your SMS communications" })] }), _jsxs("div", { className: "tabs", children: [_jsx("button", { className: `tab-btn ${smsTabs === 'logs' ? 'active' : ''}`, onClick: () => setSmsTabs('logs'), children: "\uD83D\uDCE8 SMS Logs" }), _jsx("button", { className: `tab-btn ${smsTabs === 'send' ? 'active' : ''}`, onClick: () => setSmsTabs('send'), children: "\u2709\uFE0F Send SMS" })] }), smsTabs === 'logs' && (_jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "SMS History" }) }), _jsx("div", { className: "card-body", children: smsLogs.length > 0 ? (_jsxs("table", { className: "table", children: [_jsx("thead", { children: _jsxs("tr", { children: [_jsx("th", { children: "Phone" }), _jsx("th", { children: "Message" }), _jsx("th", { children: "Provider" }), _jsx("th", { children: "Status" }), _jsx("th", { children: "Date" })] }) }), _jsx("tbody", { children: smsLogs.map((log) => (_jsxs("tr", { children: [_jsx("td", { children: log.phone_number }), _jsx("td", { className: "message-col", children: log.message }), _jsx("td", { children: log.provider }), _jsx("td", { children: _jsx("span", { className: `badge badge-${log.status}`, children: log.status }) }), _jsx("td", { children: new Date(log.created_at).toLocaleDateString() })] }, log.id))) })] })) : (_jsxs("div", { className: "empty-state", children: [_jsx(MessageSquare, { size: 48 }), _jsx("p", { children: "No SMS logs yet" })] })) })] })), smsTabs === 'send' && (_jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "Send Test SMS" }) }), _jsxs("form", { onSubmit: handleSendSMS, className: "form card-body", children: [_jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Phone Number" }), _jsx("input", { type: "tel", value: phoneNumber, onChange: (e) => setPhoneNumber(e.target.value), placeholder: "01700000000", required: true })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Message" }), _jsx("textarea", { value: message, onChange: (e) => setMessage(e.target.value), placeholder: "Enter your message...", rows: 5, required: true }), _jsxs("small", { children: [message.length, " characters"] })] }), _jsx("button", { type: "submit", className: "btn btn-primary", disabled: loading, children: loading ? 'Sending...' : 'Send SMS' })] })] }))] }));
};
export default SMSPage;
