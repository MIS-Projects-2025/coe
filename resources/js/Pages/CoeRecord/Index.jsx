import { useEffect, useState, useCallback, useTransition } from "react";
import { router, usePage } from "@inertiajs/react";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import { Skeleton } from "@/components/ui/skeleton";
import {
    ChevronUp,
    ChevronDown,
    ChevronsUpDown,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    Search,
    SlidersHorizontal,
} from "lucide-react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

// ─── Constants ───────────────────────────────────────────────────────────────

const STATUS_MAP = {
    1: { label: "Pending", variant: "outline" },
    2: { label: "Approved", variant: "default" },
    3: { label: "Rejected", variant: "destructive" },
};

const COE_TYPE_MAP = {
    1: "Employment",
    2: "Compensation",
    3: "Employment with Compensation",
};

const PER_PAGE_OPTIONS = [10, 20, 50, 100];

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Encode filter state as base64 JSON for the ?q= hash param.
 */
function encodeParams(params) {
    return btoa(JSON.stringify(params));
}

/**
 * Build the URL with the encoded ?q= param.
 */
function buildUrl(params) {
    const hash = encodeParams(params);
    return `${window.location.pathname}?q=${hash}`;
}

// ─── Sub-components ──────────────────────────────────────────────────────────

function SortIcon({ column, sortBy, sortDir }) {
    if (sortBy !== column)
        return (
            <ChevronsUpDown className="ml-1 h-3.5 w-3.5 text-muted-foreground" />
        );
    return sortDir === "asc" ? (
        <ChevronUp className="ml-1 h-3.5 w-3.5" />
    ) : (
        <ChevronDown className="ml-1 h-3.5 w-3.5" />
    );
}

function StatusBadge({ status }) {
    const s = STATUS_MAP[status] ?? {
        label: status ?? "—",
        variant: "secondary",
    };
    return <Badge variant={s.variant}>{s.label}</Badge>;
}

function TableSkeleton({ rows = 10, cols = 8 }) {
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

// ─── Main Page ───────────────────────────────────────────────────────────────

export default function CoeRecordIndex() {
    const { filters, records } = usePage().props;

    // Local filter state — drives the UI controls
    const [search, setSearch] = useState(filters.search ?? "");
    const [status, setStatus] = useState(filters.status ?? "");
    const [coeType, setCoeType] = useState(filters.coe_type ?? "");
    const [perPage, setPerPage] = useState(filters.per_page ?? 10);
    const [sortBy, setSortBy] = useState(filters.sort_by ?? "id");
    const [sortDir, setSortDir] = useState(filters.sort_dir ?? "desc");

    const [isPending, startTransition] = useTransition();
    const isLoading = isPending || records === undefined;

    // ── Navigate with encoded params ─────────────────────────────────────────
    const navigate = useCallback(
        (overrides = {}) => {
            const params = {
                search,
                status,
                coe_type: coeType,
                per_page: perPage,
                sort_by: sortBy,
                sort_dir: sortDir,
                page: 1, // reset to page 1 on any filter change
                ...overrides,
            };

            startTransition(() => {
                router.visit(buildUrl(params), {
                    preserveScroll: true,
                    preserveState: true,
                    only: ["records", "filters"],
                });
            });
        },
        [search, status, coeType, perPage, sortBy, sortDir],
    );

    // ── Debounced search ─────────────────────────────────────────────────────
    useEffect(() => {
        const t = setTimeout(() => navigate({ search, page: 1 }), 400);
        return () => clearTimeout(t);
    }, [search]);

    // ── Sort handler ─────────────────────────────────────────────────────────
    function handleSort(column) {
        const newDir = sortBy === column && sortDir === "asc" ? "desc" : "asc";
        setSortBy(column);
        setSortDir(newDir);
        navigate({ sort_by: column, sort_dir: newDir, page: 1 });
    }

    // ── Pagination ───────────────────────────────────────────────────────────
    function goToPage(page) {
        navigate({ page });
    }

    // ── Column header button ─────────────────────────────────────────────────
    function SortableHead({ column, children }) {
        return (
            <TableHead
                className="cursor-pointer select-none whitespace-nowrap"
                onClick={() => handleSort(column)}
            >
                <span className="inline-flex items-center">
                    {children}
                    <SortIcon
                        column={column}
                        sortBy={sortBy}
                        sortDir={sortDir}
                    />
                </span>
            </TableHead>
        );
    }

    const data = records?.data ?? [];
    const meta = records ?? {};
    const lastPage = meta.last_page ?? 1;
    const total = meta.total ?? 0;
    const from = meta.from ?? 0;
    const to = meta.to ?? 0;
    const page = filters.page ?? 1;

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
                            placeholder="Search by Employee ID or purpose…"
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="pl-8"
                        />
                    </div>

                    {/* Status filter */}
                    <Select
                        value={status || "all"}
                        onValueChange={(v) => {
                            const val = v === "all" ? "" : v;
                            setStatus(val);
                            navigate({ status: val, page: 1 });
                        }}
                    >
                        <SelectTrigger className="w-[140px]">
                            <SelectValue placeholder="Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Status</SelectItem>
                            {Object.entries(STATUS_MAP).map(([k, v]) => (
                                <SelectItem key={k} value={k}>
                                    {v.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {/* COE Type filter */}
                    <Select
                        value={coeType || "all"}
                        onValueChange={(v) => {
                            const val = v === "all" ? "" : v;
                            setCoeType(val);
                            navigate({ coe_type: val, page: 1 });
                        }}
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
                            value={String(perPage)}
                            onValueChange={(v) => {
                                const val = Number(v);
                                setPerPage(val);
                                navigate({ per_page: val, page: 1 });
                            }}
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
                                <SortableHead column="id">#</SortableHead>
                                <SortableHead column="empid">
                                    Employee ID
                                </SortableHead>
                                <SortableHead column="purpose">
                                    Purpose
                                </SortableHead>
                                <SortableHead column="date_request">
                                    Date Requested
                                </SortableHead>
                                <SortableHead column="coe_type">
                                    COE Type
                                </SortableHead>
                                <TableHead>Approver 1</TableHead>
                                <TableHead>Approver 2</TableHead>
                                <SortableHead column="status">
                                    Status
                                </SortableHead>
                                <SortableHead column="pcn_status">
                                    PCN Status
                                </SortableHead>
                                <TableHead>Remarks</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {isLoading ? (
                                <TableSkeleton rows={perPage} cols={10} />
                            ) : data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={10}
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
                                            {row.empid ?? "—"}
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
                                            <StatusBadge status={row.status} />
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
                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        {isLoading
                            ? "Loading…"
                            : total > 0
                              ? `Showing ${from}–${to} of ${total} records`
                              : "No records"}
                    </span>

                    <div className="flex items-center gap-1">
                        <Button
                            variant="outline"
                            size="icon"
                            disabled={isLoading || page <= 1}
                            onClick={() => goToPage(1)}
                        >
                            <ChevronsLeft className="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            disabled={isLoading || page <= 1}
                            onClick={() => goToPage(page - 1)}
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>

                        <span className="px-3 tabular-nums">
                            Page {page} of {lastPage}
                        </span>

                        <Button
                            variant="outline"
                            size="icon"
                            disabled={isLoading || page >= lastPage}
                            onClick={() => goToPage(page + 1)}
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                        <Button
                            variant="outline"
                            size="icon"
                            disabled={isLoading || page >= lastPage}
                            onClick={() => goToPage(lastPage)}
                        >
                            <ChevronsRight className="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
