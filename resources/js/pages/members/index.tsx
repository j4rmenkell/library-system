import { Form, Head, Link } from '@inertiajs/react';
import { capsButton, TextInput } from '@/components/library/form-controls';
import { LedgerTable, SrOnly, Td, Th } from '@/components/library/ledger-table';
import { LoanSlots } from '@/components/library/loan-slots';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Pagination } from '@/components/library/pagination';
import { Button } from '@/components/ui/button';
import { create, edit, index, show } from '@/routes/members';
import type { Member, Paginated } from '@/types';

export default function MembersIndex({
    members,
    filters,
}: {
    members: Paginated<Member>;
    filters: { search?: string };
}) {
    return (
        <>
            <Head title="Members" />
            <PageBody>
                <PageHeader
                    title="Members"
                    description={`${members.total} readers.`}
                    actions={
                        <Button asChild className={capsButton}>
                            <Link href={create()}>Register a walk-in</Link>
                        </Button>
                    }
                />

                <Form
                    {...index.form()}
                    options={{ preserveState: true }}
                    className="flex max-w-lg gap-2.5"
                >
                    <label htmlFor="search" className="sr-only">
                        Search members
                    </label>
                    <TextInput
                        id="search"
                        name="search"
                        type="search"
                        placeholder="Name or contact number"
                        defaultValue={filters.search}
                    />
                    <Button
                        type="submit"
                        variant="outline"
                        size="lg"
                        className={capsButton}
                    >
                        Search
                    </Button>
                </Form>

                <LedgerTable minWidth={800}>
                    <thead>
                        <tr>
                            <Th>Name</Th>
                            <Th>Contact</Th>
                            <Th>Address</Th>
                            <Th>Joined via</Th>
                            <Th>Books out</Th>
                            <Th>
                                <SrOnly>Actions</SrOnly>
                            </Th>
                        </tr>
                    </thead>
                    <tbody>
                        {members.data.map((member) => (
                            <tr key={member.id}>
                                <Td>
                                    <Link
                                        href={show(member.id)}
                                        className="font-semibold whitespace-nowrap hover:text-copper-bright"
                                    >
                                        {member.name}
                                    </Link>
                                </Td>
                                <Td className="whitespace-nowrap text-bone-soft">
                                    {member.contact_number}
                                </Td>
                                <Td className="text-bone-soft">
                                    {member.address}
                                </Td>
                                <Td className="whitespace-nowrap text-dust">
                                    {member.user_id
                                        ? 'Online sign-up'
                                        : 'Library desk'}
                                </Td>
                                <Td>
                                    <LoanSlots
                                        count={
                                            member.active_borrowings_count ?? 0
                                        }
                                    />
                                </Td>
                                <Td align="right">
                                    <Link
                                        href={edit(member.id)}
                                        className="text-copper-bright underline underline-offset-3"
                                    >
                                        Edit
                                    </Link>
                                </Td>
                            </tr>
                        ))}
                    </tbody>
                </LedgerTable>

                <Pagination page={members} noun="readers" />
            </PageBody>
        </>
    );
}

MembersIndex.layout = {
    breadcrumbs: [{ title: 'Members', href: index() }],
};
