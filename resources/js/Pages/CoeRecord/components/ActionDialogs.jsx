import { useState } from "react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Textarea } from "@/Components/ui/textarea";
import { Label } from "@/Components/ui/label";

// ─── Approve Dialog (remarks optional) ───────────────────────────────────────

export function ApproveDialog({ open, onOpenChange, title = "Approve Request", onSubmit }) {
    const [remarks, setRemarks] = useState("");
    const [loading, setLoading] = useState(false);

    function handleSubmit() {
        setLoading(true);
        onSubmit(remarks.trim() || null, () => {
            setLoading(false);
            setRemarks("");
            onOpenChange(false);
        });
    }

    function handleClose() {
        setRemarks("");
        onOpenChange(false);
    }

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                </DialogHeader>
                <div className="space-y-3 py-2">
                    <Label htmlFor="approve-remarks">
                        Remarks <span className="text-muted-foreground text-xs">(optional)</span>
                    </Label>
                    <Textarea
                        id="approve-remarks"
                        value={remarks}
                        onChange={(e) => setRemarks(e.target.value)}
                        placeholder="Add a note (optional)…"
                        rows={3}
                    />
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={handleClose}>Cancel</Button>
                    <Button onClick={handleSubmit} disabled={loading}>
                        {loading ? "Approving…" : "Approve"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

// ─── Disapprove Dialog (remarks required) ────────────────────────────────────

export function DisapproveDialog({ open, onOpenChange, title = "Disapprove Request", onSubmit }) {
    const [remarks, setRemarks] = useState("");
    const [loading, setLoading] = useState(false);

    function handleSubmit() {
        if (!remarks.trim()) return;
        setLoading(true);
        onSubmit(remarks, () => {
            setLoading(false);
            setRemarks("");
            onOpenChange(false);
        });
    }

    function handleClose() {
        setRemarks("");
        onOpenChange(false);
    }

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>{title}</DialogTitle>
                </DialogHeader>
                <div className="space-y-3 py-2">
                    <Label htmlFor="disapprove-remarks">
                        Remarks <span className="text-destructive">*</span>
                    </Label>
                    <Textarea
                        id="disapprove-remarks"
                        value={remarks}
                        onChange={(e) => setRemarks(e.target.value)}
                        placeholder="Enter reason for disapproval…"
                        rows={3}
                    />
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={handleClose}>Cancel</Button>
                    <Button
                        variant="destructive"
                        onClick={handleSubmit}
                        disabled={loading || !remarks.trim()}
                    >
                        {loading ? "Submitting…" : "Disapprove"}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

// ─── Remarks View Dialog ──────────────────────────────────────────────────────

export function RemarksDialog({ open, onOpenChange, remarks }) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Disapproval Remarks</DialogTitle>
                </DialogHeader>
                <p className="text-sm whitespace-pre-wrap">{remarks || "No remarks."}</p>
                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
