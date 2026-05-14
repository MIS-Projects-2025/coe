export function formatDate(dateStr) {
    if (!dateStr) return "—";
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    return d.toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" });
}

function getOrdinalSuffix(day) {
    if (day > 3 && day < 21) return "th";
    switch (day % 10) {
        case 1: return "st";
        case 2: return "nd";
        case 3: return "rd";
        default: return "th";
    }
}

export function formatRequestDate(dateStr) {
    if (!dateStr) return "—";
    const d = new Date(dateStr);
    if (isNaN(d)) return dateStr;
    const day = d.getDate();
    const month = d.toLocaleDateString("en-US", { month: "long" });
    const year = d.getFullYear();
    return `${day}${getOrdinalSuffix(day)} day of ${month} ${year}`;
}

/** "DELA CRUZ, JUAN M" → { formatted: "JUAN M DELA CRUZ", lastName: "DELA CRUZ" } */
export function parseName(fullName) {
    if (!fullName) return { formatted: "", lastName: "" };
    const idx = fullName.indexOf(",");
    if (idx !== -1) {
        const lastName = fullName.slice(0, idx).trim();
        const firstMiddle = fullName.slice(idx + 1).trim();
        return { formatted: `${firstMiddle} ${lastName}`, lastName };
    }
    return { formatted: fullName, lastName: fullName };
}

export function getGenderPrefix(sex) {
    const s = String(sex).toLowerCase();
    if (s === "1" || s === "m" || s === "male") return "Mr.";
    if (s === "2" || s === "f" || s === "female") return "Ms.";
    return "";
}

export function getHisHer(sex) {
    const s = String(sex).toLowerCase();
    if (s === "1" || s === "m" || s === "male") return "His";
    return "Her";
}

export function getCompanyName(prodline) {
    return prodline === "PL5 (TPMI)"
        ? "Telford Property Management Inc."
        : "Telford Svc. Philippines Inc.";
}

/** Builds the print filename: "DELA_CRUZ_JUAN_M_COE_2025-01-15" */
export function buildPrintFilename(empName, employid) {
    const raw = empName ?? employid ?? "Employee";
    const clean = raw.replace(/,/g, "").replace(/\s+/g, "_").toUpperCase();
    const dateStr = new Date().toISOString().split("T")[0];
    return `${clean}_COE_${dateStr}`;
}
