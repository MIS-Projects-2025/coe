import { Button } from "@/Components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { MoreHorizontal, FileText, CheckCircle, XCircle, Eye, Paperclip } from "lucide-react";
import { isPending, isApproved, isRejected } from "../utils/statusHelpers";

export function RowActions({ row, isAdmin, currentEmpId, onGenerate, onApprove, onDisapprove, onViewRemarks, onViewAttachment }) {
    const actions = [];

    if (isAdmin && isPending(row.status)) {
        actions.push(
            <DropdownMenuItem key="approve" onClick={() => onApprove(row)}>
                <CheckCircle className="h-4 w-4 mr-2 text-green-600" />
                Approve
            </DropdownMenuItem>,
            <DropdownMenuItem
                key="disapprove"
                onClick={() => onDisapprove(row)}
                className="text-destructive focus:text-destructive"
            >
                <XCircle className="h-4 w-4 mr-2" />
                Disapprove
            </DropdownMenuItem>,
            <DropdownMenuItem key="attachment" onClick={() => onViewAttachment(row)}>
                <Paperclip className="h-4 w-4 mr-2" />
                View Attachment
            </DropdownMenuItem>,
        );
    }

    const isRequestor = String(row.employid) === String(currentEmpId);
    if (isRequestor && (isApproved(row.status) || Number(row.status) === 2)) {
        actions.push(
            <DropdownMenuItem key="generate" onClick={() => onGenerate(row)}>
                <FileText className="h-4 w-4 mr-2" />
                Generate COE
            </DropdownMenuItem>,
        );
    }

    if (isRejected(row.status)) {
        actions.push(
            <DropdownMenuItem key="remarks" onClick={() => onViewRemarks(row)}>
                <Eye className="h-4 w-4 mr-2" />
                View Remarks
            </DropdownMenuItem>,
        );
    }

    if (actions.length === 0) {
        return <span className="text-muted-foreground text-xs">—</span>;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" className="h-7 w-7">
                    <MoreHorizontal className="h-4 w-4" />
                    <span className="sr-only">Row actions</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {actions}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
