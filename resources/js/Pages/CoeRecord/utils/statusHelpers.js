// ─── Constants ────────────────────────────────────────────────────────────────

export const COE_TYPE_MAP = {
    1: "Without Compensation",
    2: "Inactive",
    3: "With Compensation",
};

export const PER_PAGE_OPTIONS = [10, 20, 50, 100];

export const EMP_CLASS_MAP = {
    1: "Direct",
    2: "Non-Exempt",
    3: "Exempt",
    4: "Section Head",
    5: "Manager",
    6: "Senior Management",
};

// ─── Status helpers ───────────────────────────────────────────────────────────

export function getStatusInfo(status) {
    switch (Number(status)) {
        case 0: return { label: "For Approval",       color: "bg-yellow-500 text-white" };
        case 1: return { label: "Approved",            color: "bg-green-600 text-white" };
        case 2: return { label: "Generated",           color: "bg-blue-500 text-white" };
        case 3: return { label: "Disapproved",         color: "bg-red-600 text-white" };
        case 5: return { label: "Available for Claim", color: "bg-orange-500 text-white" };
        default: return { label: "Unknown",            color: "bg-gray-400 text-white" };
    }
}

export function isPending(status)  { return Number(status) === 0; }
export function isApproved(status) { return Number(status) === 1; }
export function isRejected(status) { return Number(status) === 3; }

/**
 * Display the emp_class label.
 * Handles both numeric IDs (legacy) and name strings (new records).
 */
export function formatEmpClass(empClass) {
    if (!empClass && empClass !== 0) return "—";
    const num = Number(empClass);
    // If it's a known numeric ID, look it up
    if (!isNaN(num) && EMP_CLASS_MAP[num]) return EMP_CLASS_MAP[num];
    // Already a name string
    return String(empClass);
}
