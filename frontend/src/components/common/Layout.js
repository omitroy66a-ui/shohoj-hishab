import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import Header from './Header';
import Footer from './Footer';
import './Layout.css';
const Layout = ({ children }) => {
    return (_jsxs("div", { className: "layout", children: [_jsx(Header, {}), _jsx("main", { className: "main-content", children: children }), _jsx(Footer, {})] }));
};
export default Layout;
