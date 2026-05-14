import { getStatusInfo } from "../utils/statusHelpers";

export function StatusBadge({ status }) {
    const { label, color } = getStatusInfo(status);
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${color}`}>
            {label}
        </span>
    );
}
