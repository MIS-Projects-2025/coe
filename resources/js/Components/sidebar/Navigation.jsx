import { usePage } from "@inertiajs/react";
import SidebarLink from "@/Components/sidebar/SidebarLink";

import { ClipboardList, FileText, Table2, Box, Layers } from "lucide-react";
import Dropdown from "./DropDown";

export default function NavLinks({ isSidebarOpen }) {
    const { emp_data } = usePage().props;

    return (
        <nav
            className="flex flex-col flex-grow space-y-1 overflow-y-auto"
            style={{ scrollbarWidth: "none" }}
        >
            <SidebarLink
                href={route("coe-records.index")}
                label="COE Records"
                icon={<ClipboardList className="w-5 h-5" />}
                isSidebarOpen={isSidebarOpen}
            />
            <SidebarLink
                href={route("coe-records.create")}
                label="Request COE"
                icon={<FileText className="w-5 h-5" />}
                isSidebarOpen={isSidebarOpen}
            />
        </nav>
    );
}
