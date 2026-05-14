import axios from "axios";
import { useState, useEffect, useRef } from "react";
import { usePage } from "@inertiajs/react";
import { buildPrintFilename } from "../utils/coeHelpers";
import { PORTAL_STYLE } from "../components/CoeDocument";

export function useGenerateCoe({ open, record, onGenerated }) {
    const { appName = "" } = usePage().props;
    const prefix = appName ? `/${appName}` : "";

    const [loading, setLoading] = useState(false);
    const [empData, setEmpData] = useState(null);
    const [error, setError] = useState(null);
    const portalRef = useRef(null);

    // Mount print portal + styles once for the lifetime of the hook
    useEffect(() => {
        const style = document.createElement("style");
        style.id = "__coe_portal_style__";
        style.textContent = PORTAL_STYLE;
        document.head.appendChild(style);

        const div = document.createElement("div");
        div.id = "coe-print-portal";
        document.body.appendChild(div);
        portalRef.current = div;

        return () => {
            document.head.removeChild(style);
            document.body.removeChild(div);
            portalRef.current = null;
        };
    }, []);

    // Fetch COE data when the dialog opens for a record
    useEffect(() => {
        if (!open || !record?.id) return;

        setLoading(true);
        setError(null);
        setEmpData(null);

        axios.get(`${prefix}/coe-record/${record.id}/generate-data`)
            .then(({ data }) => setEmpData(data))
            .catch((err) => setError(err.response?.data?.error ?? err.message))
            .finally(() => setLoading(false));
    }, [open, record?.id]);

    function handlePrint() {
        const originalTitle = document.title;
        document.title = buildPrintFilename(empData?.emp_name, record?.employid);

        window.print();

        document.title = originalTitle;

        if (!record?.id) return;

        axios.put(`${prefix}/coe-record/${record.id}/status`, { status: "generated" })
            .then(() => onGenerated?.(record.id))
            .catch((err) => {
                console.error("Failed to mark COE as generated:", err);
                onGenerated?.(record.id);
            });
    }

    return { loading, empData, error, portalRef, handlePrint };
}
