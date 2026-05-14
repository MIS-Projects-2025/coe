import { formatDate, formatRequestDate, parseName, getGenderPrefix, getHisHer, getCompanyName } from "../utils/coeHelpers";

const SERIF = "'Times New Roman', Times, serif";
const INDENT = "\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0\u00a0";

export const PORTAL_STYLE = `
#coe-print-portal {
    position: absolute;
    left: -9999px;
    top: 0;
    width: 750px;
    visibility: hidden;
    pointer-events: none;
    background: white;
    font-family: 'Times New Roman', Times, serif;
}
@page { margin: 0.5in; }
@media print {
    body > * { visibility: hidden !important; }
    #coe-print-portal {
        visibility: visible !important;
        position: fixed !important;
        inset: 0 !important;
        width: auto !important;
        background: white !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
    }
    #coe-print-portal * { visibility: visible !important; }
    #coe-print-portal > div {
        flex: 1 !important;
        display: flex !important;
        flex-direction: column !important;
    }
    #coe-print-portal > div > div:first-child {
        flex: 1 !important;
    }
}
`;

function CoeFooter() {
    return (
        <div style={{ padding: "4px 24px 6px", fontFamily: SERIF, fontSize: "0.62em", color: "#333" }}>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "center", gap: 5, marginBottom: 3 }}>
                <span>A member of</span>
                <img src="/logo.png" alt="ASTI" style={{ maxHeight: 20 }} onError={(e) => (e.target.style.display = "none")} />
            </div>
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: 8 }}>
                <div style={{ lineHeight: 1.5, color: "#444" }}>
                    <p style={{ margin: 0 }}>Linares St. Gateway Business Park, SEPZ, Brgy. Javalera Gen. Trias, Cavite 4107, Philippines</p>
                    <p style={{ margin: 0 }}>Tel Nos. ++(63) (46) 433-0536 * Fax No. ++(63) (46) 433-0529</p>
                </div>
                <div style={{ flexShrink: 0 }}>
                    <img src="/footer.jpg" alt="TÜV Certification" style={{ maxHeight: 42 }} onError={(e) => (e.target.style.display = "none")} />
                </div>
            </div>
        </div>
    );
}

function CoeDoc({ children, companyName }) {
    return (
        <div style={{ fontFamily: SERIF, display: "flex", flexDirection: "column" }}>
            <div style={{ flex: 1, maxWidth: 680, width: "100%", margin: "0 auto", padding: "32px 24px" }}>
                <div style={{ textAlign: "center", marginBottom: 24 }}>
                    <img src="/logo.png" alt="Company Logo" style={{ maxWidth: 120, display: "block", margin: "0 auto" }} onError={(e) => (e.target.style.display = "none")} />
                    <p style={{ marginTop: 8, fontSize: "1.1em" }}>{companyName}</p>
                </div>
                <h1 style={{ textAlign: "center", fontSize: 22, marginBottom: 28, fontFamily: SERIF }}>
                    <strong>CERTIFICATE OF EMPLOYMENT</strong>
                </h1>
                {children}
                <div style={{ marginTop: 48 }}>
                    <img src="/signature1.png" alt="Authorized Signature" style={{ maxWidth: 260 }} onError={(e) => (e.target.style.display = "none")} />
                </div>
            </div>
            <CoeFooter />
        </div>
    );
}

function Para({ children }) {
    return (
        <p style={{ textAlign: "justify", lineHeight: 1.9, fontSize: "1.15em", marginBottom: 16, fontFamily: SERIF }}>
            {children}
        </p>
    );
}

export function CoeWithoutComp({ data }) {
    const { formatted: name, lastName } = parseName(data.emp_name);
    const prefix = getGenderPrefix(data.emp_sex);
    const company = getCompanyName(data.prodline);
    return (
        <CoeDoc companyName={company}>
            <Para>
                {INDENT}This is to certify that <strong>{prefix}</strong>{" "}
                <strong>{name}</strong> is an employee of Telford Svc. Phils. Inc.
                from <strong>{formatDate(data.date_hired)}</strong> up to present
                with the position of <strong>{data.emp_position}</strong>.
            </Para>
            <Para>
                This certification is being issued upon the request of{" "}
                <em>{prefix} {lastName}</em> for {data.purpose}.
            </Para>
            <Para>
                Done this {formatRequestDate(data.date_request)} at {company}{" "}
                Gateway Business Park, Brgy. Javalera, General Trias, Cavite.
            </Para>
        </CoeDoc>
    );
}

export function CoeInactive({ data }) {
    const { formatted: name, lastName } = parseName(data.emp_name);
    const prefix = getGenderPrefix(data.emp_sex);
    return (
        <CoeDoc companyName="Telford Svc. Philippines Inc.">
            <Para>
                {INDENT}This is to certify that {prefix}{" "}
                <strong>{name}</strong> is an employee of Telford Svc. Phils. Inc.
                from <strong>{formatDate(data.date_hired)}</strong> up to{" "}
                <strong>{formatDate(data.sep_date)}</strong> with the position of{" "}
                <strong>{data.emp_position}</strong>.
            </Para>
            <Para>
                This certification is being issued upon the request of{" "}
                <em>{prefix} {lastName}</em> for whatever legal purpose(s) it may serve.
            </Para>
            <Para>
                Done this {formatRequestDate(data.date_request)} at Telford Svc
                Phils., Inc. Gateway Business Park, Brgy. Javalera, General Trias, Cavite.
            </Para>
        </CoeDoc>
    );
}

export function CoeWithComp({ data }) {
    const { formatted: name, lastName } = parseName(data.emp_name);
    const prefix = getGenderPrefix(data.emp_sex);
    const hisHer = getHisHer(data.emp_sex);
    const company = getCompanyName(data.prodline);
    const salary = data.salary_data ?? {};
    return (
        <CoeDoc companyName={company}>
            <Para>
                {INDENT}This is to certify that <strong>{prefix}</strong>{" "}
                <strong>{name}</strong> is an employee of Telford Svc. Phils. Inc.
                from <strong>{formatDate(data.date_hired)}</strong> up to{" "}
                <strong>present</strong> with the position of{" "}
                <strong>{data.emp_position}</strong>. {hisHer} gross annual compensation is{" "}
                {salary.annual_salary_words ?? "—"} (Php {salary.annual_salary ?? "—"}) including the 13th Month Pay.
            </Para>
            <Para>Breakdown of Gross Monthly Compensation is as follows:</Para>
            <div style={{ marginLeft: 32, fontSize: "1.1em", lineHeight: 2.2, fontFamily: SERIF }}>
                <div style={{ display: "flex" }}>
                    <span style={{ minWidth: 220 }}>Basic Pay</span>
                    <span style={{ minWidth: 60 }}>-</span>
                    <span>Php {salary.basic_pay ?? "—"}</span>
                </div>
                <div style={{ display: "flex" }}>
                    <span style={{ minWidth: 220 }}>13th Month Pay</span>
                    <span style={{ minWidth: 60 }}>-</span>
                    <span>Php {salary.thirteenth_month ?? "—"}</span>
                </div>
            </div>
            <br />
            <Para>
                This certification is being issued upon the request of{" "}
                <em>{prefix} {lastName}</em> for {data.purpose}.
            </Para>
            <Para>
                Done this {formatRequestDate(data.date_request)} at Telford Svc
                Phils., Inc. Gateway Business Park, Brgy. Javalera, General Trias, Cavite.
            </Para>
        </CoeDoc>
    );
}
