import { useEffect } from "react";
import { create } from "zustand";
import { router } from "@inertiajs/react";

const DEFAULTS = {
    search: "",
    status: "",
    coe_type: "",
    tab: "pending",
    per_page: 10,
    sort_by: "id",
    sort_dir: "desc",
};

/**
 * Zustand store for COE Records — mirrors createFilterStore but uses the
 * base64 ?q= hash scheme that the COE controller expects.
 */
const useCoeStore = create((set, get) => ({
    filters: { ...DEFAULTS },

    hydrate(initial) {
        set({ filters: { ...DEFAULTS, ...initial } });
    },

    apply(changed) {
        const next = { ...get().filters, ...changed, page: 1 };
        set({ filters: next });
        navigate(next);
    },

    goToPage(page) {
        const next = { ...get().filters, page };
        // Don't reset page in filters state — keep it in sync
        set({ filters: next });
        navigate(next);
    },
}));

function navigate(params) {
    const hash = btoa(JSON.stringify(params));
    const url = `${window.location.pathname}?q=${hash}`;
    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
        only: ["records", "filters"],
        replace: false,
    });
}

/**
 * Filter state for CoeRecordIndex.
 * Hydrates from server-provided initialFilters on every navigation.
 */
export function useCoeFilters(initialFilters = {}) {
    const { filters, hydrate, apply, goToPage } = useCoeStore();

    // eslint-disable-next-line react-hooks/exhaustive-deps
    useEffect(() => {
        hydrate(initialFilters);
    }, [JSON.stringify(initialFilters)]);

    return {
        filters,
        applyFilters: apply,
        goToPage,
        clearFilters: () => apply({ search: "", status: "", coe_type: "" }),
    };
}
