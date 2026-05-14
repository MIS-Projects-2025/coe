import { useState, useEffect } from "react";
import { isPending } from "../utils/statusHelpers";

/**
 * Manages checkbox selection state for bulk approve/disapprove on the pending tab.
 *
 * @param {Array}  data – current page rows
 * @param {string} tab  – active tab; resets selection when it changes
 */
export function useBulkSelection(data, tab) {
    const [selectedIds, setSelectedIds] = useState(new Set());

    // Reset whenever the tab switches or the data refreshes
    useEffect(() => {
        setSelectedIds(new Set());
    }, [tab, data]);

    const pendingRows      = data.filter((r) => isPending(r.status));
    const allSelected      = pendingRows.length > 0 && pendingRows.every((r) => selectedIds.has(r.id));
    const someSelected     = pendingRows.some((r) => selectedIds.has(r.id));
    const indeterminate    = someSelected && !allSelected;

    function toggleAll(checked) {
        setSelectedIds(checked ? new Set(pendingRows.map((r) => r.id)) : new Set());
    }

    function toggleRow(id, checked) {
        setSelectedIds((prev) => {
            const next = new Set(prev);
            checked ? next.add(id) : next.delete(id);
            return next;
        });
    }

    function clearSelection() {
        setSelectedIds(new Set());
    }

    return { selectedIds, allSelected, someSelected, indeterminate, toggleAll, toggleRow, clearSelection };
}
