import axios from "axios";
import { useState, useEffect } from "react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Skeleton } from "@/Components/ui/skeleton";
import { Paperclip, Eye } from "lucide-react";

function formatFileSize(bytes) {
    if (!bytes) return "—";
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function AttachmentModal({ open, onOpenChange, recordId, prefix }) {
    const [loading,     setLoading]     = useState(false);
    const [attachments, setAttachments] = useState([]);
    const [error,       setError]       = useState(null);

    useEffect(() => {
        if (!open || !recordId) return;
        setLoading(true);
        setError(null);
        setAttachments([]);

        axios.get(`${prefix}/coe-record/${recordId}/attachments`)
            .then(({ data }) => setAttachments(data.attachments ?? []))
            .catch((err) => setError(err.response?.data?.error ?? err.message))
            .finally(() => setLoading(false));
    }, [open, recordId]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Paperclip className="h-4 w-4" />
                        Request Attachments
                    </DialogTitle>
                </DialogHeader>

                {loading && (
                    <div className="space-y-2 py-4">
                        <Skeleton className="h-12 w-full" />
                        <Skeleton className="h-12 w-full" />
                    </div>
                )}

                {error && (
                    <p className="text-sm text-destructive py-2">{error}</p>
                )}

                {!loading && !error && attachments.length === 0 && (
                    <p className="text-sm text-muted-foreground py-4 text-center">
                        No attachments found for this request.
                    </p>
                )}

                {!loading && attachments.length > 0 && (
                    <ul className="divide-y">
                        {attachments.map((att) => (
                            <li key={att.id} className="flex items-center justify-between py-3 gap-3">
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium truncate">{att.file_name}</p>
                                    <p className="text-xs text-muted-foreground">
                                        {att.file_type ?? "—"} · {formatFileSize(att.file_size)}
                                    </p>
                                </div>
                                {att.url ? (
                                    <a href={att.url} target="_blank" rel="noopener noreferrer" className="shrink-0">
                                        <Button variant="outline" size="sm">
                                            <Eye className="h-3.5 w-3.5 mr-1" />
                                            View
                                        </Button>
                                    </a>
                                ) : (
                                    <span className="text-xs text-muted-foreground">Unavailable</span>
                                )}
                            </li>
                        ))}
                    </ul>
                )}

                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>Close</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
