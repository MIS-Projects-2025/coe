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
import { Pagination } from "@/Components/Pagination";
import { Search, Plus, Pencil, Trash2 } from "lucide-react";

export default function PurposeIndex() {
    const { records, filters, flash } = usePage().props;
    const [search, setSearch]         = useState(filters.search ?? "");
    const [addOpen, setAddOpen]       = useState(false);
    const [editTarget, setEditTarget] = useState(null);
    const [delTarget, setDelTarget]   = useState(null);
    const [form, setForm]             = useState("");
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
            router.get(route("admin.purposes.index"), { search: val, per_page: filters.per_page }, { preserveState: true, replace: true });
        }, 400);
    }

    function handleAdd() {
        setLoading(true);
        router.post(route("admin.purposes.store"), { purpose: form.trim() }, {
            preserveScroll: true,
            onSuccess: () => { setAddOpen(false); setForm(""); setLoading(false); },
            onError:   () => setLoading(false),
        });
    }

    function handleEdit() {
        setLoading(true);
        router.put(route("admin.purposes.update", editTarget.id), { purpose: form.trim() }, {
            preserveScroll: true,
            onSuccess: () => { setEditTarget(null); setForm(""); setLoading(false); },
            onError:   () => setLoading(false),
        });
    }

    function handleDelete() {
        setLoading(true);
        router.delete(route("admin.purposes.destroy", delTarget.id), {
            preserveScroll: true,
            onSuccess: () => { setDelTarget(null); setLoading(false); },
            onError:   () => setLoading(false),
        });
    }

    function openEdit(row) { setEditTarget(row); setForm(row.purpose); }

    return (
        <AuthenticatedLayout>
            <div className="p-6 space-y-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Purpose Types</h1>
                    <p className="text-sm text-muted-foreground">Manage COE request purpose options</p>
                </div>

                {/* Toolbar */}
                <div className="flex items-center gap-3">
                    <div className="relative flex-1 min-w-[200px] max-w-sm">
                        <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={handleSearch}
                            placeholder="Search purposes…"
                            className="pl-8"
                        />
                    </div>
                    <Button className="ml-auto" onClick={() => { setForm(""); setAddOpen(true); }}>
                        <Plus className="h-4 w-4 mr-1.5" />
                        Add Purpose
                    </Button>
                </div>

                {/* Table */}
                <div className="rounded-md border overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead className="w-[60px]">#</TableHead>
                                <TableHead>Purpose</TableHead>
                                <TableHead className="w-[180px]">Date Created</TableHead>
                                <TableHead className="w-[90px]">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {data.length === 0 ? (
                                <TableRow>
                                    <TableCell colSpan={4} className="h-32 text-center text-muted-foreground">
                                        No purposes found.
                                    </TableCell>
                                </TableRow>
                            ) : data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="tabular-nums text-muted-foreground">{row.id}</TableCell>
                                    <TableCell>{row.purpose}</TableCell>
                                    <TableCell className="text-sm text-muted-foreground whitespace-nowrap">
                                        {row.date_created
                                            ? new Date(row.date_created).toLocaleDateString("en-PH", { year: "numeric", month: "short", day: "numeric" })
                                            : "—"}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center gap-1">
                                            <Button size="icon" variant="ghost" className="h-7 w-7" onClick={() => openEdit(row)}>
                                                <Pencil className="h-3.5 w-3.5" />
                                            </Button>
                                            <Button size="icon" variant="ghost" className="h-7 w-7 text-destructive hover:text-destructive" onClick={() => setDelTarget(row)}>
                                                <Trash2 className="h-3.5 w-3.5" />
                                            </Button>
                                        </div>
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
                            router.get(route("admin.purposes.index"), { ...filters, page }, { preserveState: true })
                        }
                    />
                )}
            </div>

            {/* Add Dialog */}
            <Dialog open={addOpen} onOpenChange={setAddOpen}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Add Purpose</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-2 py-2">
                        <Label htmlFor="purpose-add">Purpose</Label>
                        <Input
                            id="purpose-add"
                            value={form}
                            onChange={(e) => setForm(e.target.value)}
                            placeholder="Enter purpose…"
                            maxLength={500}
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setAddOpen(false)}>Cancel</Button>
                        <Button onClick={handleAdd} disabled={loading || !form.trim()}>
                            {loading ? "Saving…" : "Save"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Edit Dialog */}
            <Dialog open={!!editTarget} onOpenChange={(open) => !open && setEditTarget(null)}>
                <DialogContent className="max-w-md">
                    <DialogHeader>
                        <DialogTitle>Edit Purpose</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-2 py-2">
                        <Label htmlFor="purpose-edit">Purpose</Label>
                        <Input
                            id="purpose-edit"
                            value={form}
                            onChange={(e) => setForm(e.target.value)}
                            placeholder="Enter purpose…"
                            maxLength={500}
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setEditTarget(null)}>Cancel</Button>
                        <Button onClick={handleEdit} disabled={loading || !form.trim()}>
                            {loading ? "Saving…" : "Update"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!delTarget} onOpenChange={(open) => !open && setDelTarget(null)}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Delete Purpose</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        Are you sure you want to delete{" "}
                        <span className="font-medium text-foreground">"{delTarget?.purpose}"</span>?
                        This cannot be undone.
                    </p>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDelTarget(null)}>Cancel</Button>
                        <Button variant="destructive" onClick={handleDelete} disabled={loading}>
                            {loading ? "Deleting…" : "Delete"}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AuthenticatedLayout>
    );
}
