import { ChevronUp, ChevronDown, ChevronsUpDown } from "lucide-react";
import { TableHead, TableRow, TableCell } from "@/Components/ui/table";
import { Skeleton } from "@/Components/ui/skeleton";

export function SortableHead({ column, sortBy, sortDir, onSort, children, className = "" }) {
    return (
        <TableHead
            className={`cursor-pointer select-none whitespace-nowrap ${className}`}
            onClick={() => onSort(column)}
        >
            <span className="inline-flex items-center gap-1">
                {children}
                {sortBy !== column ? (
                    <ChevronsUpDown className="h-3.5 w-3.5 text-muted-foreground" />
                ) : sortDir === "asc" ? (
                    <ChevronUp className="h-3.5 w-3.5" />
                ) : (
                    <ChevronDown className="h-3.5 w-3.5" />
                )}
            </span>
        </TableHead>
    );
}

export function TableSkeleton({ rows = 10, cols = 9 }) {
    return Array.from({ length: rows }).map((_, i) => (
        <TableRow key={i}>
            {Array.from({ length: cols }).map((_, j) => (
                <TableCell key={j}>
                    <Skeleton className="h-4 w-full" />
                </TableCell>
            ))}
        </TableRow>
    ));
}
