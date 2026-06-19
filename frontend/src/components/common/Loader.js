import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import './Loader.css';
const Loader = () => {
    return (_jsx("div", { className: "loader-container", children: _jsxs("div", { className: "loader", children: [_jsx("div", { className: "loader-spinner" }), _jsx("p", { children: "Loading..." })] }) }));
};
export default Loader;
