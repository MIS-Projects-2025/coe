import { createPortal } from "react-dom";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from "@/Components/ui/dialog";
import { Button } from "@/Components/ui/button";
import { Skeleton } from "@/Components/ui/skeleton";
import { Alert, AlertDescription } from "@/Components/ui/alert";
import { Printer, AlertCircle } from "lucide-react";
import { useGenerateCoe } from "../hooks/useGenerateCoe";
import { CoeWithoutComp, CoeInactive, CoeWithComp } from "./CoeDocument";

const TYPE_LABELS = {
    1: "Without Compensation",
    2: "Inactive",
    3: "With Compensation",
};

function CoePreview({ coeType, data }) {
    if (coeType === 1) return <CoeWithoutComp data={data} />;
    if (coeType === 2) return <CoeInactive data={data} />;
    if (coeType === 3) return <CoeWithComp data={data} />;
    return (
        <p className="text-muted-foreground text-sm p-6">
            Unsupported COE type: {coeType}
        </p>
    );
}

export function GenerateCoeDialog({ open, onOpenChange, record, onGenerated }) {
    const { loading, empData, error, portalRef, handlePrint } = useGenerateCoe({
        open,
        record,
        onGenerated,
    });

    const coeType = Number(record?.coe_type);
    const merged  = empData ? { ...record, ...empData } : null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        Certificate of Employment
                        <span className="text-muted-foreground font-normal text-sm">
                            — {TYPE_LABELS[coeType] ?? "Unknown"}
                        </span>
                    </DialogTitle>
                </DialogHeader>

                {/* Loading skeleton */}
                {loading && (
                    <div className="space-y-4 py-6 px-2">
                        <Skeleton className="h-28 w-28 mx-auto rounded" />
                        <Skeleton className="h-5 w-52 mx-auto" />
                        <div className="space-y-2 mt-8">
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-11/12" />
                            <Skeleton className="h-4 w-full" />
                            <Skeleton className="h-4 w-4/5" />
                        </div>
                    </div>
                )}

                {/* Error */}
                {error && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{error}</AlertDescription>
                    </Alert>
                )}

                {/* COE preview (modal) */}
                {merged && (
                    <div className="border rounded-md bg-white overflow-hidden">
                        <CoePreview coeType={coeType} data={merged} />
                    </div>
                )}

                {/* Print portal — renders into a direct body child to escape dialog CSS transforms */}
                {merged && portalRef.current &&
                    createPortal(<CoePreview coeType={coeType} data={merged} />, portalRef.current)
                }

                {/* Footer */}
                {!loading && (
                    <DialogFooter className="gap-2">
                        <Button variant="outline" onClick={() => onOpenChange(false)}>
                            Close
                        </Button>
                        {merged && (
                            <Button onClick={handlePrint}>
                                <Printer className="h-4 w-4 mr-2" />
                                Print COE
                            </Button>
                        )}
                    </DialogFooter>
                )}
            </DialogContent>
        </Dialog>
    );
}
