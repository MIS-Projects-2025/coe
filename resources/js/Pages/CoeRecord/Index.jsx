import { useRef, useState } from "react";
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Button } from "@/Components/ui/button";
import { Search, CheckCircle, XCircle, Clock, History } from "lucide-react";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Pagination } from "@/Components/Pagination";

import { useCoeFilters }    from "./hooks/useCoeFilter";
import { useBulkSelection } from "./hooks/useBulkSelection";
import { GenerateCoeDialog } from "./components/GenerateCoeDialog";
import { ApproveDialog, DisapproveDialog, RemarksDialog } from "./components/ActionDialogs";
import { AttachmentModal }  from "./components/AttachmentModal";
import { RowActions }       from "./components/RowActions";
import { StatusBadge }      from "./components/StatusBadge";
import { SortableHead, TableSkeleton } from "./components/SortableHead";
import { COE_TYPE_MAP, PER_PAGE_OPTIONS, isPending, formatEmpClass } from "./utils/statusHelpers";

export default function CoeRecordIndex() {
    const { filters: serverFilters, records, isAdmin = false, appName = "", emp_data = {} } = usePage().props;
    const currentEmpId = emp_data?.emp_id ?? null;
    const prefix = appName ? `/${appName}` : "";

    const { filters, applyFilters, goToPage } = useCoeFilters(serverFilters);
    const {
        search   = "",
        coe_type = "",
        tab      = "pending",
        per_page = 10,
        sort_by  = "id",
        sort_dir = "desc",
    } = filters;

    const isLoading = records === undefined;
    const data      = records?.data ?? [];
    const meta      = records ?? {};

    // ── Bulk selection ────────────────────────────────────────────────────────
    const { selectedIds, allSelected, someSelected, indeterminate, toggleAll, toggleRow, clearSelection } =
        useBulkSelection(data, tab);

    // ── Dialog targets ────────────────────────────────────────────────────────
    const [generateTarget,   setGenerateTarget]   = useState(null);
    const [approveTarget,    setApproveTarget]    = useState(null);
    const [disapproveTarget, setDisapproveTarget] = useState(null);
    const [remarksTarget,    setRemarksTarget]    = useState(null);
    const [attachmentTarget, setAttachmentTarget] = useState(null);
    const [bulkAction,       setBulkAction]       = useState(null); // 'approve' | 'disapprove'

    // ── Debounced search ──────────────────────────────────────────────────────
    const debounceTimer = useRef(null);
    function handleSearchChange(e) {
        clearTimeout(debounceTimer.current);
        debounceTimer.current = setTimeout(() => applyFilters({ search: e.target.value }), 400);
    }

    // ── Filter / sort / tab handlers ──────────────────────────────────────────
    function handleSort(column) {
        applyFilters({
            sort_by:  column,
            sort_dir: sort_by === column && sort_dir === "asc" ? "desc" : "asc",
        });
    }
    function handleCoeTypeChange(value) { applyFilters({ coe_type: value === "all" ? "" : value }); }
    function handlePerPageChange(value) { applyFilters({ per_page: Number(value) }); }
    function handleTabChange(newTab)    { applyFilters({ tab: newTab, status: "", page: 1 }); }

    // ── Action handlers ───────────────────────────────────────────────────────
    function handleApproveSubmit(remarks, done) {
        router.put(
            `${prefix}/coe-record/${approveTarget.id}/status`,
            { status: "approved", ...(remarks && { remarks }) },
            { preserveScroll: true, only: ["records"], onSuccess: done, onError: done },
        );
    }

    function handleDisapproveSubmit(remarks, done) {
        router.put(
            `${prefix}/coe-record/${disapproveTarget.id}/status`,
            { status: "rejected", remarks },
            { preserveScroll: true, only: ["records"], onSuccess: done, onError: done },
        );
    }

    function handleBulkSubmit(remarks, done) {
        const status = bulkAction === "approve" ? "approved" : "rejected";
        router.put(
            `${prefix}/coe-records/bulk-status`,
            { ids: Array.from(selectedIds), status, ...(remarks && { remarks }) },
            {
                preserveScroll: true,
                only: ["records"],
                onSuccess: () => { clearSelection(); done(); },
                onError: done,
            },
        );
    }

    function handleGenerated() {
        router.reload({ only: ["records"] });
        setGenerateTarget(null);
    }

    const showCheckboxCol = isAdmin && tab === "pending";
    const colCount        = showCheckboxCol ? 10 : 9;

    return (
        <AuthenticatedLayout>
            <div className="p-6 space-y-4">
                {/* Header */}
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">COE Records</h1>
                    <p className="text-sm text-muted-foreground">Certificate of Employment requests</p>
                </div>

                {/* Tabs */}
                <div className="flex gap-1 border-b">
                    {[
                        { key: "pending", icon: Clock,   label: "Pending" },
                        { key: "history", icon: History, label: "History" },
                    ].map(({ key, icon: Icon, label }) => (
                        <button
                            key={key}
                            onClick={() => handleTabChange(key)}
                            className={`flex items-center gap-1.5 px-4 py-2 text-sm font-medium border-b-2 transition-colors ${
                                tab === key
                                    ? "border-primary text-primary"
                                    : "border-transparent text-muted-foreground hover:text-foreground"
                            }`}
                        >
                            <Icon className="h-4 w-4" />
                            {label}
                        </button>
                    ))}
                </div>

                {/* Bulk action toolbar */}
                {isAdmin && tab === "pending" && someSelected && (
                    <div className="flex items-center gap-2 rounded-md border bg-muted/50 px-4 py-2">
                        <span className="text-sm text-muted-foreground">{selectedIds.size} selected</span>
                        <div className="ml-auto flex gap-2">
                            <Button size="sm" onClick={() => setBulkAction("approve")}>
                                <CheckCircle className="h-3.5 w-3.5 mr-1.5" />
                                Approve Selected
                            </Button>
                            <Button size="sm" variant="destructive" onClick={() => setBulkAction("disapprove")}>
                                <XCircle className="h-3.5 w-3.5 mr-1.5" />
                                Disapprove Selected
                            </Button>
                        </div>
                    </div>
                )}

                {/* Toolbar */}
                <div className="flex flex-wrap items-center gap-3">
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

                    <Select value={String(coe_type) || "all"} onValueChange={handleCoeTypeChange}>
                        <SelectTrigger className="w-[210px]">
                            <SelectValue placeholder="COE Type" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">All Types</SelectItem>
                            {Object.entries(COE_TYPE_MAP).map(([k, v]) => (
                                <SelectItem key={k} value={k}>{v}</SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <div className="flex items-center gap-2 ml-auto">
                        <span className="text-sm text-muted-foreground whitespace-nowrap">Rows per page</span>
                        <Select value={String(per_page)} onValueChange={handlePerPageChange}>
                            <SelectTrigger className="w-[70px]"><SelectValue /></SelectTrigger>
                            <SelectContent>
                                {PER_PAGE_OPTIONS.map((n) => (
                                    <SelectItem key={n} value={String(n)}>{n}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                {/* Table */}
                <div className="rounded-md border overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                {showCheckboxCol && (
                                    <TableHead className="w-[40px]">
                                        <input
                                            type="checkbox"
                                            className="h-4 w-4 rounded border-gray-300"
                                            checked={allSelected}
                                            ref={(el) => { if (el) el.indeterminate = indeterminate; }}
                                            onChange={(e) => toggleAll(e.target.checked)}
                                        />
                                    </TableHead>
                                )}
                                <SortableHead column="id"           sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>#</SortableHead>
                                <SortableHead column="employid"     sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>Employee ID</SortableHead>
                                <SortableHead column="emp_position" sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>Position</SortableHead>
                                <SortableHead column="emp_class"    sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>Class</SortableHead>
                                <SortableHead column="purpose"      sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>Purpose</SortableHead>
                                <SortableHead column="date_request" sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>Date Requested</SortableHead>
                                <SortableHead column="coe_type"     sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>COE Type</SortableHead>
                                <SortableHead column="status"       sortBy={sort_by} sortDir={sort_dir} onSort={handleSort}>Status</SortableHead>
                                <TableHead className="w-[60px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {isLoading ? (
                                <TableSkeleton rows={per_page} cols={colCount} />
                            ) : data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={colCount} className="h-32 text-center text-muted-foreground">
                                        No records found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                data.map((row) => (
                                    <TableRow key={row.id} className={selectedIds.has(row.id) ? "bg-muted/40" : ""}>
                                        {showCheckboxCol && (
                                            <TableCell>
                                                {isPending(row.status) && (
                                                    <input
                                                        type="checkbox"
                                                        className="h-4 w-4 rounded border-gray-300"
                                                        checked={selectedIds.has(row.id)}
                                                        onChange={(e) => toggleRow(row.id, e.target.checked)}
                                                    />
                                                )}
                                            </TableCell>
                                        )}
                                        <TableCell className="tabular-nums text-muted-foreground">{row.id}</TableCell>
                                        <TableCell className="font-mono text-sm">{row.employid ?? "—"}</TableCell>
                                        <TableCell>{row.emp_position ?? "—"}</TableCell>
                                        <TableCell>{formatEmpClass(row.emp_class)}</TableCell>
                                        <TableCell className="max-w-[180px] truncate">{row.purpose ?? "—"}</TableCell>
                                        <TableCell className="whitespace-nowrap text-sm">
                                            {row.date_request
                                                ? new Date(row.date_request).toLocaleDateString("en-PH", {
                                                      year: "numeric", month: "short", day: "numeric",
                                                  })
                                                : "—"}
                                        </TableCell>
                                        <TableCell>{COE_TYPE_MAP[row.coe_type] ?? row.coe_type ?? "—"}</TableCell>
                                        <TableCell><StatusBadge status={row.status} /></TableCell>
                                        <TableCell>
                                            <RowActions
                                                row={row}
                                                isAdmin={isAdmin}
                                                currentEmpId={currentEmpId}
                                                onGenerate={setGenerateTarget}
                                                onApprove={setApproveTarget}
                                                onDisapprove={setDisapproveTarget}
                                                onViewRemarks={setRemarksTarget}
                                                onViewAttachment={setAttachmentTarget}
                                            />
                                        </TableCell>
                                    </TableRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                </div>

                {!isLoading && meta.last_page > 1 && (
                    <Pagination meta={meta} onPageChange={goToPage} />
                )}
            </div>

            <GenerateCoeDialog
                open={!!generateTarget}
                onOpenChange={(open) => !open && setGenerateTarget(null)}
                record={generateTarget}
                onGenerated={handleGenerated}
            />
            <ApproveDialog
                open={!!approveTarget}
                onOpenChange={(open) => !open && setApproveTarget(null)}
                onSubmit={handleApproveSubmit}
            />
            <DisapproveDialog
                open={!!disapproveTarget}
                onOpenChange={(open) => !open && setDisapproveTarget(null)}
                onSubmit={handleDisapproveSubmit}
            />
            <ApproveDialog
                open={bulkAction === "approve"}
                onOpenChange={(open) => !open && setBulkAction(null)}
                title={`Approve ${selectedIds.size} Selected Record(s)`}
                onSubmit={handleBulkSubmit}
            />
            <DisapproveDialog
                open={bulkAction === "disapprove"}
                onOpenChange={(open) => !open && setBulkAction(null)}
                title={`Disapprove ${selectedIds.size} Selected Record(s)`}
                onSubmit={handleBulkSubmit}
            />
            <RemarksDialog
                open={!!remarksTarget}
                onOpenChange={(open) => !open && setRemarksTarget(null)}
                remarks={remarksTarget?.remarks}
            />
            <AttachmentModal
                open={!!attachmentTarget}
                onOpenChange={(open) => !open && setAttachmentTarget(null)}
                recordId={attachmentTarget?.id}
                prefix={prefix}
            />
        </AuthenticatedLayout>
    );
}
