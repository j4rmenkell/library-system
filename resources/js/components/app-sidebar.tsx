import { Link, usePage } from '@inertiajs/react';
import {
    ArrowLeftRight,
    Bookmark,
    BookOpen,
    Folder,
    LayoutGrid,
    ScrollText,
    Search,
    UserCog,
    Users,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { GreekKey } from '@/components/library/greek-key';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as books } from '@/routes/books';
import { index as borrowings } from '@/routes/borrowings';
import { index as catalog } from '@/routes/catalog';
import { index as categories } from '@/routes/categories';
import { index as logs } from '@/routes/logs';
import { index as members } from '@/routes/members';
import { index as myBorrowings } from '@/routes/my-borrowings';
import { index as users } from '@/routes/users';
import type { NavItem, Role } from '@/types';

// Admins and librarians run the library
const staffNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Books', href: books(), icon: BookOpen },
    { title: 'Categories', href: categories(), icon: Folder },
    { title: 'Members', href: members(), icon: Users },
    { title: 'Borrowings', href: borrowings(), icon: ArrowLeftRight },
];

// Only admins manage accounts and read the logs
const adminNavItems: NavItem[] = [
    { title: 'Users', href: users(), icon: UserCog },
    { title: 'Activity Logs', href: logs(), icon: ScrollText },
];

// Borrowers (shown as "Readers") browse and borrow
const readerNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Book Catalog', href: catalog(), icon: Search },
    { title: 'My Borrowed Books', href: myBorrowings(), icon: Bookmark },
];

const roleLabels: Record<Role, string> = {
    admin: 'Admin',
    librarian: 'Librarian',
    borrower: 'Reader',
};

export function AppSidebar() {
    const { auth } = usePage().props;
    const role = auth.user.role;
    const isReader = role === 'borrower';

    return (
        <Sidebar collapsible="icon" variant="sidebar">
            <SidebarHeader className="gap-4 pt-4">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <GreekKey
                    muted
                    className="group-data-[collapsible=icon]:hidden"
                />
            </SidebarHeader>

            <SidebarContent className="gap-4">
                <NavMain
                    label={roleLabels[role]}
                    items={isReader ? readerNavItems : staffNavItems}
                />
                {role === 'admin' && (
                    <NavMain label="Administration" items={adminNavItems} />
                )}
            </SidebarContent>

            <SidebarFooter className="border-t border-sidebar-border">
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
