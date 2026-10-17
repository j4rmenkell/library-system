import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { BookCover } from '@/components/library/book-cover';
import { ConfirmDialog } from '@/components/library/confirm-dialog';
import { LedgerTable, Td, Th } from '@/components/library/ledger-table';
import {
    PageBody,
    PageHeader,
    SectionHeading,
} from '@/components/library/page-header';
import { StatusMarker } from '@/components/library/status-marker';
import {
    daysLate,
    displayStatus,
    formatDate,
    formatDateTime,
    MAX_ACTIVE_LOANS,
} from '@/lib/library';
import { destroy, index } from '@/routes/my-borrowings';
import type { Borrowing } from '@/types';

function currentLabel(borrowing: Borrowing): {
    status: string;
    detail: string;
} {
    switch (displayStatus(borrowing)) {
        case 'pending':
            return {
                status: 'Waiting for the librarian',
                detail: `Asked ${formatDateTime(borrowing.created_at)}`,
            };
        case 'overdue':
            return {
                status: `Overdue by ${daysLate(borrowing)} days`,
                detail: `Was due ${formatDate(borrowing.due_date)}. Please bring it back.`,
            };
        default:
            return {
                status: `Due ${formatDate(borrowing.due_date, false)}`,
                detail: `Borrowed ${formatDate(borrowing.borrow_date)}`,
            };
    }
}

export default function MyBorrowingsIndex({
    borrowings,
}: {
    borrowings: Borrowing[];
}) {
    const [cancelling, setCancelling] = useState<Borrowing | null>(null);
    const current = borrowings.filter((b) =>
        ['pending', 'borrowed'].includes(b.status),
    );
    const earlier = borrowings.filter((b) =>
        ['returned', 'rejected'].includes(b.status),
    );

    return (
        <>
            <Head title="My borrowed books" />
            <PageBody>
                <PageHeader
                    title="My borrowed books"
                    description={`${current.length} of ${MAX_ACTIVE_LOANS} books out. Bring books back to the library desk.`}
                />

                <section className="flex flex-col gap-3">
                    <SectionHeading>Right now</SectionHeading>
                    <ul className="border-t border-copper">
                        {current.map((borrowing) => {
                            const label = currentLabel(borrowing);

                            return (
                                <li
                                    key={borrowing.id}
                                    className="flex flex-wrap items-center gap-5 border-b border-line py-4"
                                >
                                    {borrowing.book && (
                                        <BookCover
                                            book={borrowing.book}
                                            size="xs"
                                            className="w-11 flex-none"
                                        />
                                    )}
                                    <span className="flex min-w-0 flex-[1_1_200px] flex-col">
                                        <span className="font-display text-lg font-semibold">
                                            {borrowing.book?.title}
                                        </span>
                                        <span className="text-dust">
                                            {borrowing.book?.author}
                                        </span>
                                    </span>
                                    <span className="flex flex-[1_1_220px] flex-col gap-1">
                                        <StatusMarker
                                            status={displayStatus(borrowing)}
                                            label={label.status}
                                        />
                                        <span className="text-bone-soft">
                                            {label.detail}
                                        </span>
                                    </span>
                                    {borrowing.status === 'pending' && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setCancelling(borrowing)
                                            }
                                            className="text-lg text-status-overdue"
                                        >
                                            Cancel request
                                        </button>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                    {current.length === 0 && (
                        <p className="text-dust italic">
                            You have no books out right now.
                        </p>
                    )}
                </section>

                <section className="flex flex-col gap-3">
                    <SectionHeading>Earlier</SectionHeading>
                    <LedgerTable minWidth={620}>
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
                            {earlier.map((borrowing) => (
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
                                            status={borrowing.status}
                                        />
                                    </Td>
                                </tr>
                            ))}
                        </tbody>
                    </LedgerTable>
                    {earlier.length === 0 && (
                        <p className="text-dust italic">
                            Your returned books will show up here.
                        </p>
                    )}
                </section>
            </PageBody>

            <ConfirmDialog
                open={cancelling !== null}
                onOpenChange={(open) => !open && setCancelling(null)}
                title={`Cancel your request for ${cancelling?.book?.title}?`}
                description="The book goes back to the shelf for other readers."
                confirmLabel="Cancel request"
                onConfirm={() =>
                    cancelling &&
                    router.delete(destroy.url(cancelling.id), {
                        preserveScroll: true,
                        onFinish: () => setCancelling(null),
                    })
                }
            />
        </>
    );
}

MyBorrowingsIndex.layout = {
    breadcrumbs: [{ title: 'My Borrowed Books', href: index() }],
};
