import { useState, useEffect, useRef } from "react";
import { router, usePage } from "@inertiajs/react";
import { toast } from "sonner";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import {
    Table, TableBody, TableCell, TableHead, TableHeader, TableRow,
} from "@/Components/ui/table";
import { Button } from "@/Components/ui/button";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";
import {
    Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter,
} from "@/Components/ui/dialog";
import { Combobox } from "@/Components/ui/combobox";
import { Pagination } from "@/Components/Pagination";
import { Search, Plus, Trash2 } from "lucide-react";

async function loadEmployees(search, page) {
    try {
        const url = new URL(route("admin.admin-list.employees"), window.location.origin);
        url.searchParams.set("search", search);
        url.searchParams.set("page", page);
        const res = await fetch(url.toString(), {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        if (!res.ok) return { options: [], hasMore: false };
        const data = await res.json();
        return {
            options: Array.isArray(data.options) ? data.options : [],
            hasMore: data.hasMore ?? false,
        };
    } catch {
        return { options: [], hasMore: false };
    }
}

export default function AdminListIndex() {
    const { records, filters, flash } = usePage().props;
    const [search, setSearch]         = useState(filters.search ?? "");
    const [addOpen, setAddOpen]       = useState(false);
    const [delTarget, setDelTarget]   = useState(null);
    const [selected, setSelected]     = useState(undefined); // { value: emp_id, label }
    const [errors, setErrors]         = useState({});
    const [loading, setLoading]       = useState(false);
    const debounce                    = useRef(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error)   toast.error(flash.error);
    }, [flash]);

    const data = records?.data ?? [];
    const meta = records ?? {};

    function handleSearch(e) {
        const val = e.target.value;
        setSearch(val);
        clearTimeout(debounce.current);
        debounce.current = setTimeout(() => {
            router.get(
                route("admin.admin-list.index"),
                { search: val, per_page: filters.per_page },
                { preserveState: true, replace: true },
            );
        }, 400);
    }

    function handleAdd() {
        if (!selected) return;
        setLoading(true);
        setErrors({});
        router.post(route("admin.admin-list.store"), { admin_id: selected }, {
            preserveScroll: true,
            onSuccess: () => { setAddOpen(false); setSelected(undefined); setLoading(false); },
            onError:   (errs) => { setErrors(errs); setLoading(false); },
        });
    }

    function handleRemove() {
        setLoading(true);
        router.delete(route("admin.admin-list.destroy", delTarget.id), {
            preserveScroll: true,
            onSuccess: () => { setDelTarget(null); setLoading(false); },
            onError:   () => setLoading(false),
        });
    }

    function openAdd() {
        setSelected(undefined);
        setErrors({});
        setAddOpen(true);
    }

    return (
        <AuthenticatedLayout>
            <div className="p-6 space-y-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Admin List</h1>
                    <p className="text-sm text-muted-foreground">Manage COE system administrators</p>
                </div>

                {/* Toolbar */}
                <div className="flex items-center gap-3">
                    <div className="relative flex-1 min-w-[200px] max-w-sm">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={handleSearch}
                            placeholder="Search by Employee ID…"
                            className="pl-8"
                        />
                    </div>
                    <Button className="ml-auto" onClick={openAdd}>
                        <Plus className="h-4 w-4 mr-1.5" />
                        Add Admin
                    </Button>
                </div>

                {/* Table */}
                <div className="rounded-md border overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-[60px]">#</TableHead>
                                <TableHead className="w-[120px]">Employee ID</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Department</TableHead>
                                <TableHead className="w-[160px]">Date Added</TableHead>
                                <TableHead className="w-[80px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={7} className="h-32 text-center text-muted-foreground">
                                        No admins found.
                                    </TableCell>
                                </TableRow>
                            ) : data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="tabular-nums text-muted-foreground">{row.id}</TableCell>
                                    <TableCell className="font-mono text-sm">{row.admin_id}</TableCell>
                                    <TableCell>{row.emp_name}</TableCell>
                                    <TableCell>{row.emp_position}</TableCell>
                                    <TableCell>{row.emp_dept}</TableCell>
                                    <TableCell className="text-sm text-muted-foreground whitespace-nowrap">
                                        {row.date_created
                                            ? new Date(row.date_created).toLocaleDateString("en-PH", { year: "numeric", month: "short", day: "numeric" })
                                            : "—"}
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            size="icon"
                                            variant="ghost"
                                            className="h-7 w-7 text-destructive hover:text-destructive"
                                            onClick={() => setDelTarget(row)}
                                        >
                                            <Trash2 className="h-3.5 w-3.5" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                {meta.last_page > 1 && (
                    <Pagination
                        meta={meta}
                        onPageChange={(page) =>
                            router.get(route("admin.admin-list.index"), { ...filters, page }, { preserveState: true })
                        }
                    />
                )}
            </div>

            {/* Add Dialog */}
            <Dialog open={addOpen} onOpenChange={(open) => { setAddOpen(open); if (!open) setErrors({}); }}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Add Admin</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-2 py-2">
                        <Label>Employee</Label>
                        <Combobox
                            value={selected}
                            onChange={setSelected}
                            loadOptions={loadEmployees}
                            placeholder="Search active employee…"
                            clearable
                            modal
                        />
                        {errors.admin_id && (
                            <p className="text-xs text-destructive">{errors.admin_id}</p>
                        )}
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setAddOpen(false)}>Cancel</Button>
                        <Button onClick={handleAdd} disabled={loading || !selected}>
                            {loading ? "Adding…" : "Add"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Remove Confirmation Dialog */}
            <Dialog open={!!delTarget} onOpenChange={(open) => !open && setDelTarget(null)}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Remove Admin</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        Are you sure you want to remove{" "}
                        <span className="font-medium text-foreground">
                            {delTarget?.emp_name && delTarget.emp_name !== "—"
                                ? delTarget.emp_name
                                : delTarget?.admin_id}
                        </span>{" "}
                        from the admin list?
                    </p>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDelTarget(null)}>Cancel</Button>
                        <Button variant="destructive" onClick={handleRemove} disabled={loading}>
                            {loading ? "Removing…" : "Remove"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
