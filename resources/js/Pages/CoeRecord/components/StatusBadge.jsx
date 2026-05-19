import { Badge } from "@/Components/ui/badge";
import { getStatusInfo } from "../utils/statusHelpers";

export function StatusBadge({ status }) {
    const { label, color } = getStatusInfo(status);
    return <Badge variant="outline" className={color}>{label}</Badge>;
}
