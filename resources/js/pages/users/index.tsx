import { Form, Head, Link, usePage } from '@inertiajs/react';
import {
    capsButton,
    SelectInput,
    TextInput,
} from '@/components/library/form-controls';
import { LedgerTable, SrOnly, Td, Th } from '@/components/library/ledger-table';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Pagination } from '@/components/library/pagination';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/library';
import { cn } from '@/lib/utils';
import { create, edit, index } from '@/routes/users';
import type { Paginated, Role, User } from '@/types';

const roleLabels: Record<Role, string> = {
    admin: 'Admin',
    librarian: 'Librarian',
    borrower: 'Reader',
};

export default function UsersIndex({
    users,
    filters,
}: {
    users: Paginated<User>;
    filters: { search?: string; role?: string };
}) {
    const { auth } = usePage().props;

    return (
        <>
            <Head title="Users" />
            <PageBody>
                <PageHeader
                    title="Users"
                    description="Who can sign in, and what each of them may do."
                    actions={
                        <Button asChild className={capsButton}>
                            <Link href={create()}>Add a user</Link>
                        </Button>
                    }
                />

                <Form
                    {...index.form()}
                    options={{ preserveState: true }}
                    className="flex flex-wrap gap-2.5"
                >
                    <label htmlFor="search" className="sr-only">
                        Search users
                    </label>
                    <TextInput
                        id="search"
                        name="search"
                        type="search"
                        placeholder="Name, username or email"
                        defaultValue={filters.search}
                        className="flex-[1_1_260px] md:max-w-md"
                    />
                    <label htmlFor="role" className="sr-only">
                        Role
                    </label>
                    <SelectInput
                        id="role"
                        name="role"
                        defaultValue={filters.role ?? ''}
                        className="flex-[0_1_180px]"
                    >
                        <option value="">All roles</option>
                        <option value="admin">Admins</option>
                        <option value="librarian">Librarians</option>
                        <option value="borrower">Readers</option>
                    </SelectInput>
                    <Button
                        type="submit"
                        variant="outline"
                        size="lg"
                        className={capsButton}
                    >
                        Filter
                    </Button>
                </Form>

                <LedgerTable minWidth={760}>
                    <thead>
                        <tr>
                            <Th>Full name</Th>
                            <Th>Username</Th>
                            <Th>Email</Th>
                            <Th>Role</Th>
                            <Th>Joined</Th>
                            <Th>
                                <SrOnly>Actions</SrOnly>
                            </Th>
                        </tr>
                    </thead>
                    <tbody>
                        {users.data.map((user) => (
                            <tr key={user.id}>
                                <Td className="font-semibold whitespace-nowrap">
                                    {user.name}
                                    {user.id === auth.user.id && (
                                        <span className="ml-1.5 text-sm font-normal text-dust">
                                            (you)
                                        </span>
                                    )}
                                </Td>
                                <Td className="text-bone-soft">
                                    @{user.username}
                                </Td>
                                <Td className="text-bone-soft">{user.email}</Td>
                                <Td
                                    className={cn(
                                        'font-caps text-xs font-semibold tracking-[0.1em] uppercase',
                                        user.role === 'borrower'
                                            ? 'text-dust'
                                            : 'text-copper-bright',
                                    )}
                                >
                                    {roleLabels[user.role]}
                                </Td>
                                <Td className="whitespace-nowrap text-bone-soft">
                                    {formatDate(user.created_at)}
                                </Td>
                                <Td align="right">
                                    <Link
                                        href={edit(user.id)}
                                        className="text-copper-bright underline underline-offset-3"
                                    >
                                        Edit
                                    </Link>
                                </Td>
                            </tr>
                        ))}
                    </tbody>
                </LedgerTable>

                <Pagination page={users} noun="users" />
            </PageBody>
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [{ title: 'Users', href: index() }],
};
