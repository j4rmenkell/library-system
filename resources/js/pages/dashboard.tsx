import { Head, Link, usePage } from '@inertiajs/react';
import { BookCover } from '@/components/library/book-cover';
import { capsButton } from '@/components/library/form-controls';
import { LedgerTable, SrOnly, Td, Th } from '@/components/library/ledger-table';
import { LoanSlots } from '@/components/library/loan-slots';
import {
    PageBody,
    PageHeader,
    SectionHeading,
} from '@/components/library/page-header';
import { ShelfGallery } from '@/components/library/shelf-gallery';
import { StatStrip } from '@/components/library/stat-strip';
import { StatusMarker } from '@/components/library/status-marker';
import { Button } from '@/components/ui/button';
import {
    daysLate,
    displayStatus,
    formatDate,
    formatDateTime,
    MAX_ACTIVE_LOANS,
} from '@/lib/library';
import { dashboard } from '@/routes';
import { create as createBook } from '@/routes/books';
import {
    approve,
    create as issueBook,
    index as borrowings,
    reject,
} from '@/routes/borrowings';
import { index as catalog } from '@/routes/catalog';
import { index as myBorrowings } from '@/routes/my-borrowings';
import type { Book, Borrowing } from '@/types';

type StaffData = {
    stats: { total: number; available: number; out: number; overdue: number };
    pending: Borrowing[];
    overdue: Borrowing[];
};

type ReaderData = {
    loans: Borrowing[];
    fresh: Book[];
};

const today = new Date().toLocaleDateString('en-US', {
    weekday: 'long',
    month: 'long',
    day: 'numeric',
});

function greeting(): string {
    const hour = new Date().getHours();

    if (hour < 12) {
        return 'Good morning';
    }

    return hour < 18 ? 'Good afternoon' : 'Good evening';
}

function StaffDashboard({ data, name }: { data: StaffData; name: string }) {
    const waiting = data.pending.length;

    return (
        <PageBody>
            <PageHeader
                title={`${greeting()}, ${name}`}
                description={`${today}. ${waiting === 0 ? 'No requests are waiting.' : `${waiting} ${waiting === 1 ? 'reader is' : 'readers are'} waiting on a request.`}`}
                actions={
                    <>
                        <Button
                            asChild
                            variant="outline"
                            className={capsButton}
                        >
                            <Link href={createBook()}>Add a book</Link>
                        </Button>
                        <Button asChild className={capsButton}>
                            <Link href={issueBook()}>Issue a book</Link>
                        </Button>
                    </>
                }
            />

            <StatStrip
                stats={[
                    {
                        label: 'Books in the collection',
                        value: data.stats.total,
                    },
                    { label: 'On the shelf', value: data.stats.available },
                    { label: 'Out on loan', value: data.stats.out },
                    {
                        label: 'Overdue',
                        value: data.stats.overdue,
                        tone: data.stats.overdue > 0 ? 'overdue' : 'default',
                    },
                ]}
            />

            <div className="flex flex-wrap items-start gap-10">
                <section className="flex min-w-0 flex-[3_1_440px] flex-col gap-3">
                    <SectionHeading
                        action={
                            <Link
                                href={borrowings()}
                                className="text-copper-bright underline-offset-4 hover:underline"
                            >
                                All borrowings
                            </Link>
                        }
                    >
                        Waiting for approval
                    </SectionHeading>
                    <LedgerTable minWidth={480}>
                        <thead>
                            <tr>
                                <Th>Book</Th>
                                <Th>Reader</Th>
                                <Th>Asked</Th>
                                <Th>
                                    <SrOnly>Actions</SrOnly>
                                </Th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.pending.map((borrowing) => (
                                <tr key={borrowing.id}>
                                    <Td>
                                        <div className="font-semibold">
                                            {borrowing.book?.title}
                                        </div>
                                        <div className="text-sm text-dust">
                                            {borrowing.book?.author}
                                        </div>
                                    </Td>
                                    <Td>{borrowing.member?.name}</Td>
                                    <Td className="whitespace-nowrap text-dust">
                                        {formatDateTime(borrowing.created_at)}
                                    </Td>
                                    <Td
                                        align="right"
                                        className="whitespace-nowrap"
                                    >
                                        <Button
                                            asChild
                                            size="sm"
                                            className={capsButton}
                                        >
                                            <Link
                                                href={approve(borrowing.id)}
                                                as="button"
                                                preserveScroll
                                            >
                                                Approve
                                            </Link>
                                        </Button>
                                        <Button
                                            asChild
                                            size="sm"
                                            variant="ghost"
                                            className={`${capsButton} text-dust`}
                                        >
                                            <Link
                                                href={reject(borrowing.id)}
                                                as="button"
                                                preserveScroll
                                            >
                                                Reject
                                            </Link>
                                        </Button>
                                    </Td>
                                </tr>
                            ))}
                        </tbody>
                    </LedgerTable>
                    {data.pending.length === 0 && (
                        <p className="text-dust italic">
                            Nothing to approve. Enjoy the quiet.
                        </p>
                    )}
                </section>

                <section className="flex min-w-0 flex-[2_1_280px] flex-col gap-3">
                    <SectionHeading tone="overdue">
                        Overdue · {data.overdue.length}
                    </SectionHeading>
                    <ul className="border-t border-status-overdue">
                        {data.overdue.map((borrowing) => (
                            <li
                                key={borrowing.id}
                                className="flex justify-between gap-3 border-b border-line py-3"
                            >
                                <span className="flex min-w-0 flex-col gap-0.5">
                                    <span className="font-semibold">
                                        {borrowing.book?.title}
                                    </span>
                                    <span className="text-sm text-dust">
                                        {borrowing.member?.name} ·{' '}
                                        {borrowing.member?.contact_number}
                                    </span>
                                </span>
                                <span className="font-caps text-sm font-semibold whitespace-nowrap text-status-overdue">
                                    {daysLate(borrowing)} days late
                                </span>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </PageBody>
    );
}

function ReaderDashboard({ data, name }: { data: ReaderData; name: string }) {
    const out = data.loans.length;
    const left = Math.max(0, MAX_ACTIVE_LOANS - out);
    const nextDue = data.loans
        .filter((loan) => loan.status === 'borrowed' && loan.due_date)
        .sort((a, b) => (a.due_date! < b.due_date! ? -1 : 1))[0];
    const waiting = data.loans.filter((loan) => loan.status === 'pending');

    return (
        <PageBody>
            <PageHeader
                title={`Welcome back, ${name}`}
                description={
                    left > 0
                        ? `You can borrow ${left} more ${left === 1 ? 'book' : 'books'} right now.`
                        : 'You have three books out. Return one to borrow more.'
                }
                actions={
                    <Button asChild className={capsButton}>
                        <Link href={catalog()}>Find a book</Link>
                    </Button>
                }
            />

            <StatStrip
                stats={[
                    {
                        label: 'Books you have out',
                        value: <LoanSlots count={out} />,
                    },
                    {
                        label: nextDue
                            ? `Next due · ${nextDue.book?.title}`
                            : 'Next due',
                        value: nextDue
                            ? formatDate(nextDue.due_date, false)
                            : '—',
                    },
                    {
                        label: 'Waiting for the librarian',
                        value: waiting.length,
                        tone: waiting.length > 0 ? 'pending' : 'default',
                    },
                ]}
            />

            <section className="flex flex-col gap-3">
                <SectionHeading
                    action={
                        <Link
                            href={myBorrowings()}
                            className="text-copper-bright underline-offset-4 hover:underline"
                        >
                            Full history
                        </Link>
                    }
                >
                    Your books
                </SectionHeading>
                <ul className="border-t border-copper">
                    {data.loans.map((loan) => (
                        <li
                            key={loan.id}
                            className="flex flex-wrap items-center gap-5 border-b border-line py-3.5"
                        >
                            {loan.book && (
                                <BookCover
                                    book={loan.book}
                                    size="xs"
                                    className="w-10"
                                />
                            )}
                            <span className="flex min-w-0 flex-[1_1_200px] flex-col">
                                <span className="text-lg font-semibold">
                                    {loan.book?.title}
                                </span>
                                <span className="text-dust">
                                    {loan.book?.author}
                                </span>
                            </span>
                            <span className="text-bone-soft">
                                {loan.status === 'pending'
                                    ? `Asked ${formatDateTime(loan.created_at)}`
                                    : `${formatDate(loan.borrow_date, false)} → ${formatDate(loan.due_date, false)}`}
                            </span>
                            <StatusMarker
                                status={displayStatus(loan)}
                                className="min-w-24"
                            />
                        </li>
                    ))}
                </ul>
                {out === 0 && (
                    <p className="text-dust italic">
                        No books out yet. The catalog is a good place to start.
                    </p>
                )}
            </section>

            <section className="flex flex-col gap-3.5">
                <SectionHeading
                    action={
                        <Link
                            href={catalog()}
                            className="text-copper-bright underline-offset-4 hover:underline"
                        >
                            Open the catalog
                        </Link>
                    }
                >
                    New on the shelf
                </SectionHeading>
                <ShelfGallery books={data.fresh} />
            </section>
        </PageBody>
    );
}

export default function Dashboard({
    staff,
    reader,
}: {
    staff?: StaffData;
    reader?: ReaderData;
}) {
    const { auth } = usePage().props;
    const firstName = auth.user.name.split(' ')[0];

    return (
        <>
            <Head title="Dashboard" />
            {staff && <StaffDashboard data={staff} name={firstName} />}
            {reader && <ReaderDashboard data={reader} name={firstName} />}
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
