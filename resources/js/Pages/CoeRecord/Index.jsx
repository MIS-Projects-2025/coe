import { useRef } from "react";
import { router, usePage } from "@inertiajs/react";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Input } from "@/Components/ui/input";
import { Badge } from "@/Components/ui/badge";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Skeleton } from "@/Components/ui/skeleton";
import { ChevronUp, ChevronDown, ChevronsUpDown, Search } from "lucide-react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Pagination } from "@/Components/Pagination";
import { useCoeFilters } from "./hooks/useCoeFilter";

// ─── Constants ───────────────────────────────────────────────────────────────

const COE_TYPE_MAP = {
    1: "Employment",
    2: "Compensation",
    3: "Employment with Compensation",
};

const PER_PAGE_OPTIONS = [10, 20, 50, 100];

// ─── Helpers ─────────────────────────────────────────────────────────────────

function getStatusInfo(status, coeType, empClass, pcnStatus) {
    const statusNum = parseInt(status);
    const coeTypeNum = parseInt(coeType);
    const empClassNum = parseInt(empClass);
    const pcnStatusNum = parseInt(pcnStatus);

    if (statusNum === 0) return { label: "For Approval", variant: "warning" };
    if (statusNum === 1 && coeTypeNum !== 3)
        return { label: "Approved", variant: "success" };
    if (
        statusNum === 1 &&
        empClassNum === 1 &&
        pcnStatusNum !== 0 &&
        coeTypeNum === 3
    )
        return { label: "Approved", variant: "success" };
    if (statusNum === 1 && empClassNum !== 1)
        return { label: "For Processing", variant: "success" };
    if (
        statusNum === 1 &&
        empClassNum === 1 &&
        pcnStatusNum === 0 &&
        coeTypeNum === 3
    )
        return { label: "For Processing", variant: "success" };
    if (statusNum === 2) return { label: "Generated", variant: "info" };
    if (statusNum === 3)
        return { label: "Disapproved", variant: "destructive" };
    if (statusNum === 5)
        return { label: "Available for Claim", variant: "destructive" };

    return { label: "Unknown Status", variant: "secondary" };
}

function getBadgeVariant(variant) {
    switch (variant) {
        case "warning":
            return "outline";
        case "success":
            return "default";
        case "info":
            return "secondary";
        case "destructive":
            return "destructive";
        default:
            return "secondary";
    }
}

function StatusBadge({ status, coeType, empClass, pcnStatus }) {
    const { label, variant } = getStatusInfo(
        status,
        coeType,
        empClass,
        pcnStatus,
    );
    return (
        <Badge
            variant={getBadgeVariant(variant)}
            className={
                variant === "warning"
                    ? "bg-yellow-500 text-white hover:bg-yellow-600"
                    : ""
            }
        >
            {label}
        </Badge>
    );
}

function TableSkeleton({ rows = 10, cols = 12 }) {
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

function SortableHead({ column, sortBy, sortDir, onSort, children }) {
    return (
        <TableHead
            className="cursor-pointer select-none whitespace-nowrap"
            onClick={() => onSort(column)}
        >
            <span className="inline-flex items-center">
                {children}
                {sortBy !== column ? (
                    <ChevronsUpDown className="ml-1 h-3.5 w-3.5 text-muted-foreground" />
                ) : sortDir === "asc" ? (
                    <ChevronUp className="ml-1 h-3.5 w-3.5" />
                ) : (
                    <ChevronDown className="ml-1 h-3.5 w-3.5" />
                )}
            </span>
        </TableHead>
    );
}

// ─── Main Page ───────────────────────────────────────────────────────────────

export default function CoeRecordIndex() {
    const { filters: serverFilters, records } = usePage().props;

    // useCoeFilters hydrates the Zustand store from server filters on every
    // Inertia navigation, so filters are always fresh — no stale closure risk.
    const { filters, applyFilters, goToPage } = useCoeFilters(serverFilters);

    const {
        search = "",
        status = "",
        coe_type = "",
        per_page = 10,
        sort_by = "id",
        sort_dir = "desc",
    } = filters;

    const isLoading = records === undefined;
    const data = records?.data ?? [];
    const meta = records ?? {};

    // ── Debounced search ──────────────────────────────────────────────────────
    const debounceTimer = useRef(null);

    function handleSearchChange(e) {
        const value = e.target.value;
        clearTimeout(debounceTimer.current);
        debounceTimer.current = setTimeout(() => {
            applyFilters({ search: value });
        }, 400);
    }

    // ── Handlers ──────────────────────────────────────────────────────────────

    function handleSort(column) {
        const newDir =
            sort_by === column && sort_dir === "asc" ? "desc" : "asc";
        applyFilters({ sort_by: column, sort_dir: newDir });
    }

    function handleCoeTypeChange(value) {
        applyFilters({ coe_type: value === "all" ? "" : value });
    }

    function handlePerPageChange(value) {
        applyFilters({ per_page: Number(value) });
    }

    return (
        <AuthenticatedLayout>
            <div className="p-6 space-y-4">
                {/* ── Header ── */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">
                            COE Records
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Certificate of Employment requests
                        </p>
                    </div>
                </div>

                {/* ── Toolbar ── */}
                <div className="flex flex-wrap items-center gap-3">
                    {/* Search */}
                    <div className="relative flex-1 min-w-[200px] max-w-sm">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            key={search}
                            placeholder="Search by Employee ID or purpose…"
                            defaultValue={search}
                            onChange={handleSearchChange}
                            className="pl-8"
                        />
                    </div>

                    {/* COE Type filter */}
                    <Select
                        value={String(coe_type) || "all"}
                        onValueChange={handleCoeTypeChange}
                    >
                        <SelectTrigger className="w-[210px]">
                            <SelectValue placeholder="COE Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Types</SelectItem>
                            {Object.entries(COE_TYPE_MAP).map(([k, v]) => (
                                <SelectItem key={k} value={k}>
                                    {v}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {/* Rows per page */}
                    <div className="flex items-center gap-2 ml-auto">
                        <span className="text-sm text-muted-foreground whitespace-nowrap">
                            Rows per page
                        </span>
                        <Select
                            value={String(per_page)}
                            onValueChange={handlePerPageChange}
                        >
                            <SelectTrigger className="w-[70px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {PER_PAGE_OPTIONS.map((n) => (
                                    <SelectItem key={n} value={String(n)}>
                                        {n}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* ── Table ── */}
                <div className="rounded-md border overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <SortableHead
                                    column="id"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    #
                                </SortableHead>
                                <SortableHead
                                    column="employid"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    Employee ID
                                </SortableHead>
                                <SortableHead
                                    column="emp_position"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    Position
                                </SortableHead>
                                <SortableHead
                                    column="emp_class"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    Class
                                </SortableHead>
                                <SortableHead
                                    column="purpose"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    Purpose
                                </SortableHead>
                                <SortableHead
                                    column="date_request"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    Date Requested
                                </SortableHead>
                                <SortableHead
                                    column="coe_type"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    COE Type
                                </SortableHead>
                                <TableHead>Approver 1</TableHead>
                                <TableHead>Approver 2</TableHead>
                                <SortableHead
                                    column="status"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    Status
                                </SortableHead>
                                <SortableHead
                                    column="pcn_status"
                                    sortBy={sort_by}
                                    sortDir={sort_dir}
                                    onSort={handleSort}
                                >
                                    PCN Status
                                </SortableHead>
                                <TableHead>Remarks</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {isLoading ? (
                                <TableSkeleton rows={per_page} cols={12} />
                            ) : data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={12}
                                        className="h-32 text-center text-muted-foreground"
                                    >
                                        No records found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell className="tabular-nums text-muted-foreground">
                                            {row.id}
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">
                                            {row.employid ?? "—"}
                                        </TableCell>
                                        <TableCell>
                                            {row.emp_position ?? "—"}
                                        </TableCell>
                                        <TableCell>
                                            {row.emp_class ?? "—"}
                                        </TableCell>
                                        <TableCell>
                                            {row.purpose ?? "—"}
                                        </TableCell>
                                        <TableCell className="whitespace-nowrap text-sm">
                                            {row.date_request
                                                ? new Date(
                                                    row.date_request,
                                                ).toLocaleDateString(
                                                    "en-PH",
                                                    {
                                                        year: "numeric",
                                                        month: "short",
                                                        day: "numeric",
                                                    },
                                                )
                                                : "—"}
                                        </TableCell>
                                        <TableCell>
                                            {COE_TYPE_MAP[row.coe_type] ??
                                                row.coe_type ??
                                                "—"}
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">
                                            {row.approver1_emp_num ?? "—"}
                                        </TableCell>
                                        <TableCell className="font-mono text-sm">
                                            {row.approver2_emp_num ?? "—"}
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge
                                                status={row.status}
                                                coeType={row.coe_type}
                                                empClass={row.emp_class}
                                                pcnStatus={row.pcn_status}
                                            />
                                        </TableCell>
                                        <TableCell>
                                            {row.pcn_status ?? "—"}
                                        </TableCell>
                                        <TableCell className="max-w-[200px] truncate text-sm text-muted-foreground">
                                            {row.remarks ?? "—"}
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {/* ── Pagination ── */}
                {!isLoading && meta.last_page > 1 && (
                    <Pagination meta={meta} onPageChange={goToPage} />
                )}
            </div>
        </AuthenticatedLayout>
    );
}
