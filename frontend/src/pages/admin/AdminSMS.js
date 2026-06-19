import { jsx as _jsx, jsxs as _jsxs, Fragment as _Fragment } from "react/jsx-runtime";
import { useEffect, useState } from 'react';
import { adminSMSService, smsService } from '../../services/smsService';
import { Send, Loader as LoaderIcon } from 'lucide-react';
import './AdminPages.css';
const AdminSMS = () => {
    const [activeTab, setActiveTab] = useState('campaigns');
    const [campaigns, setCampaigns] = useState([]);
    const [smsStats, setSmsStats] = useState({});
    const [loading, setLoading] = useState(false);
    const [formData, setFormData] = useState({
        name: '',
        message: '',
        recipients: '',
    });
    useEffect(() => {
        loadData();
    }, [activeTab]);
    const loadData = async () => {
        setLoading(true);
        try {
            if (activeTab === 'campaigns') {
                const data = await adminSMSService.getSMSCampaigns();
                setCampaigns(data);
            }
            else if (activeTab === 'stats') {
                const data = await smsService.getSMSStats();
                setSmsStats(data);
            }
        }
        catch (error) {
            console.error('Failed to load data', error);
        }
        finally {
            setLoading(false);
        }
    };
    const handleSendCampaign = async (e) => {
        e.preventDefault();
        setLoading(true);
        try {
            const recipients = formData.recipients.split('\n').filter((r) => r.trim());
            await adminSMSService.createCampaign(formData.name, formData.message, recipients);
            alert('Campaign sent successfully!');
            setFormData({ name: '', message: '', recipients: '' });
            loadData();
        }
        catch (error) {
            alert('Failed to send campaign: ' + error.response?.data?.message);
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsxs("div", { className: "admin-sms", children: [_jsxs("div", { className: "page-header", children: [_jsx("h1", { children: "SMS Management" }), _jsx("p", { children: "Send bulk SMS and manage campaigns" })] }), _jsxs("div", { className: "tabs", children: [_jsx("button", { className: `tab-btn ${activeTab === 'campaigns' ? 'active' : ''}`, onClick: () => setActiveTab('campaigns'), children: "\uD83D\uDCE7 Campaigns" }), _jsx("button", { className: `tab-btn ${activeTab === 'send' ? 'active' : ''}`, onClick: () => setActiveTab('send'), children: "\u2709\uFE0F Send SMS" }), _jsx("button", { className: `tab-btn ${activeTab === 'stats' ? 'active' : ''}`, onClick: () => setActiveTab('stats'), children: "\uD83D\uDCCA Statistics" })] }), activeTab === 'campaigns' && (_jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "SMS Campaigns" }) }), _jsx("div", { className: "card-body", children: campaigns.length > 0 ? (_jsxs("table", { className: "table", children: [_jsx("thead", { children: _jsxs("tr", { children: [_jsx("th", { children: "Name" }), _jsx("th", { children: "Recipients" }), _jsx("th", { children: "Sent" }), _jsx("th", { children: "Failed" }), _jsx("th", { children: "Status" }), _jsx("th", { children: "Date" })] }) }), _jsx("tbody", { children: campaigns.map((campaign) => (_jsxs("tr", { children: [_jsx("td", { children: campaign.name }), _jsx("td", { children: campaign.recipients_count }), _jsx("td", { children: campaign.sent_count }), _jsx("td", { children: campaign.failed_count }), _jsx("td", { children: _jsx("span", { className: `badge badge-${campaign.status}`, children: campaign.status }) }), _jsx("td", { children: new Date(campaign.created_at).toLocaleDateString() })] }, campaign.id))) })] })) : (_jsx("div", { className: "empty-state", children: _jsx("p", { children: "No campaigns yet" }) })) })] })), activeTab === 'send' && (_jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "Send Bulk SMS Campaign" }) }), _jsxs("form", { onSubmit: handleSendCampaign, className: "card-body", children: [_jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Campaign Name" }), _jsx("input", { type: "text", value: formData.name, onChange: (e) => setFormData({ ...formData, name: e.target.value }), placeholder: "e.g., Trial Expiry Reminder", required: true })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Message" }), _jsx("textarea", { value: formData.message, onChange: (e) => setFormData({ ...formData, message: e.target.value }), placeholder: "Enter your SMS message...", rows: 5, required: true }), _jsxs("small", { children: [formData.message.length, " characters"] })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { children: "Recipients (One per line)" }), _jsx("textarea", { value: formData.recipients, onChange: (e) => setFormData({ ...formData, recipients: e.target.value }), placeholder: "01700000000\n01800000000\n01900000000", rows: 8, required: true }), _jsxs("small", { children: [formData.recipients.split('\n').filter((r) => r.trim()).length, " recipients"] })] }), _jsx("button", { type: "submit", className: "btn btn-primary", disabled: loading, children: loading ? (_jsxs(_Fragment, { children: [_jsx(LoaderIcon, { size: 18, className: "spinner" }), "Sending..."] })) : (_jsxs(_Fragment, { children: [_jsx(Send, { size: 18 }), "Send Campaign"] })) })] })] })), activeTab === 'stats' && (_jsxs("div", { className: "card", children: [_jsx("div", { className: "card-header", children: _jsx("h2", { children: "SMS Statistics" }) }), _jsx("div", { className: "card-body", children: _jsxs("div", { className: "stats-grid", children: [_jsxs("div", { className: "stat-item", children: [_jsx("label", { children: "Total SMS Sent" }), _jsx("div", { className: "stat-value", children: smsStats.total_sent || 0 })] }), _jsxs("div", { className: "stat-item", children: [_jsx("label", { children: "Successfully Delivered" }), _jsx("div", { className: "stat-value success", children: smsStats.delivered || 0 })] }), _jsxs("div", { className: "stat-item", children: [_jsx("label", { children: "Failed" }), _jsx("div", { className: "stat-value danger", children: smsStats.failed || 0 })] }), _jsxs("div", { className: "stat-item", children: [_jsx("label", { children: "Success Rate" }), _jsxs("div", { className: "stat-value", children: [smsStats.total_sent > 0
                                                    ? ((smsStats.delivered / smsStats.total_sent) * 100).toFixed(1)
                                                    : 0, "%"] })] })] }) })] }))] }));
};
export default AdminSMS;
