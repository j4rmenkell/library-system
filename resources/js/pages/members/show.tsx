import { Head, Link } from '@inertiajs/react';
import { capsButton } from '@/components/library/form-controls';
import { LedgerTable, Td, Th } from '@/components/library/ledger-table';
import {
    PageBody,
    PageHeader,
    SectionHeading,
} from '@/components/library/page-header';
import { StatStrip } from '@/components/library/stat-strip';
import { StatusMarker } from '@/components/library/status-marker';
import { Button } from '@/components/ui/button';
import { displayStatus, formatDate, MAX_ACTIVE_LOANS } from '@/lib/library';
import { create as issueBook } from '@/routes/borrowings';
import { edit, index } from '@/routes/members';
import type { Borrowing, Member } from '@/types';

export default function MembersShow({
    member,
    borrowings,
}: {
    member: Member;
    borrowings: Borrowing[];
}) {
    const out = borrowings.filter((b) =>
        ['pending', 'borrowed'].includes(b.status),
    ).length;

    return (
        <>
            <Head title={member.name} />
            <PageBody>
                <PageHeader
                    title={member.name}
                    description={
                        member.user
                            ? `Reader since ${formatDate(member.created_at)} · signed up online`
                            : `Reader since ${formatDate(member.created_at)} · registered at the desk`
                    }
                    actions={
                        <>
                            <Button
                                asChild
                                variant="outline"
                                className={capsButton}
                            >
                                <Link href={edit(member.id)}>Edit details</Link>
                            </Button>
                            <Button asChild className={capsButton}>
                                <Link
                                    href={issueBook({
                                        query: { member: member.id },
                                    })}
                                >
                                    Issue a book
                                </Link>
                            </Button>
                        </>
                    }
                />

                <StatStrip
                    stats={[
                        {
                            label: 'Contact',
                            value: (
                                <span className="font-sans text-lg font-normal">
                                    {member.contact_number}
                                </span>
                            ),
                        },
                        {
                            label: 'Address',
                            value: (
                                <span className="font-sans text-lg font-normal">
                                    {member.address}
                                </span>
                            ),
                        },
                        {
                            label: 'Account',
                            value: (
                                <span className="font-sans text-lg font-normal">
                                    {member.user
                                        ? `@${member.user.username}`
                                        : 'None (walk-in)'}
                                </span>
                            ),
                        },
                        {
                            label: 'Books out',
                            value: `${out} of ${MAX_ACTIVE_LOANS}`,
                        },
                    ]}
                />

                <section className="flex flex-col gap-3">
                    <SectionHeading>Borrowing history</SectionHeading>
                    <LedgerTable minWidth={640}>
                        <thead>
                            <tr>
                                <Th>Book</Th>
                                <Th>Borrowed</Th>
                                <Th>Due</Th>
                                <Th>Returned</Th>
                                <Th>Status</Th>
                            </tr>
                        </thead>
                        <tbody>
                            {borrowings.map((borrowing) => (
                                <tr key={borrowing.id}>
                                    <Td className="font-semibold">
                                        {borrowing.book?.title}
                                    </Td>
                                    <Td className="whitespace-nowrap text-bone-soft">
                                        {formatDate(borrowing.borrow_date)}
                                    </Td>
                                    <Td className="whitespace-nowrap text-bone-soft">
                                        {formatDate(borrowing.due_date)}
                                    </Td>
                                    <Td className="whitespace-nowrap text-bone-soft">
                                        {formatDate(borrowing.return_date)}
                                    </Td>
                                    <Td>
                                        <StatusMarker
                                            status={displayStatus(borrowing)}
                                        />
                                    </Td>
                                </tr>
                            ))}
                        </tbody>
                    </LedgerTable>
                    {borrowings.length === 0 && (
                        <p className="text-dust italic">No borrowings yet.</p>
                    )}
                </section>
            </PageBody>
        </>
    );
}

MembersShow.layout = {
    breadcrumbs: [
        { title: 'Members', href: index() },
        { title: 'Reader details', href: index() },
    ],
};
