import { usePage } from "@inertiajs/react";
import SidebarLink from "@/Components/sidebar/SidebarLink";
import { Separator } from "@/Components/ui/separator";
import { ClipboardList, FileText, ListChecks, ShieldCheck } from "lucide-react";

export default function NavLinks({ isSidebarOpen }) {
    const { isAdmin } = usePage().props;

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

            {isAdmin && (
                <>
                    <div className="px-3 pt-3 pb-1">
                        <Separator />
                        {isSidebarOpen && (
                            <p className="mt-2 px-1 text-[10px] font-semibold uppercase tracking-widest text-muted-foreground">
                                Admin
                            </p>
                        )}
                    </div>
                    <SidebarLink
                        href={route("admin.purposes.index")}
                        label="Purpose Types"
                        icon={<ListChecks className="w-5 h-5" />}
                        isSidebarOpen={isSidebarOpen}
                    />
                    <SidebarLink
                        href={route("admin.admin-list.index")}
                        label="Admin List"
                        icon={<ShieldCheck className="w-5 h-5" />}
                        isSidebarOpen={isSidebarOpen}
                    />
                </>
            )}
        </nav>
    );
}
