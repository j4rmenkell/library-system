import { Head, Link } from '@inertiajs/react';
import { capsButton } from '@/components/library/form-controls';
import { LedgerTable, SrOnly, Td, Th } from '@/components/library/ledger-table';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Pagination } from '@/components/library/pagination';
import { StatusMarker } from '@/components/library/status-marker';
import { Button } from '@/components/ui/button';
import {
    daysLate,
    displayStatus,
    formatDate,
    formatDateTime,
    LOAN_DAYS,
} from '@/lib/library';
import { cn } from '@/lib/utils';
import {
    approve,
    create,
    index,
    markReturned,
    reject,
} from '@/routes/borrowings';
import { show as showMember } from '@/routes/members';
import type { Borrowing, Paginated } from '@/types';

type Tab = 'pending' | 'borrowed' | 'overdue' | 'returned' | 'rejected';

const TABS: { key: Tab; label: string }[] = [
    { key: 'pending', label: 'Waiting' },
    { key: 'borrowed', label: 'Out on loan' },
    { key: 'overdue', label: 'Overdue' },
    { key: 'returned', label: 'Returned' },
    { key: 'rejected', label: 'Rejected' },
];

function datesFor(borrowing: Borrowing): string {
    switch (displayStatus(borrowing)) {
        case 'pending':
        case 'rejected':
            return `Asked ${formatDateTime(borrowing.created_at)}`;
        case 'overdue':
            return `Due ${formatDate(borrowing.due_date, false)} · ${daysLate(borrowing)} days late`;
        case 'returned':
            return `${formatDate(borrowing.borrow_date, false)} → back ${formatDate(borrowing.return_date, false)}`;
        default:
            return `${formatDate(borrowing.borrow_date, false)} → ${formatDate(borrowing.due_date, false)}`;
    }
}

export default function BorrowingsIndex({
    borrowings,
    status,
    counts,
}: {
    borrowings: Paginated<Borrowing>;
    status: Tab;
    counts: Record<Tab, number>;
}) {
    return (
        <>
            <Head title="Borrowings" />
            <PageBody>
                <PageHeader
                    title="Borrowings"
                    description={`Approve a request when the reader is at the desk. Loans run ${LOAN_DAYS} days.`}
                    actions={
                        <Button asChild className={capsButton}>
                            <Link href={create()}>Issue to a walk-in</Link>
                        </Button>
                    }
                />

                <nav
                    aria-label="Filter by status"
                    className="flex flex-wrap gap-x-7 border-b border-line"
                >
                    {TABS.map((tab) => {
                        const active = tab.key === status;

                        return (
                            <Link
                                key={tab.key}
                                href={index({ query: { status: tab.key } })}
                                preserveScroll
                                aria-current={active ? 'page' : undefined}
                                className={cn(
                                    '-mb-px flex h-11 items-center gap-2 border-b-2 font-caps text-xs font-semibold tracking-[0.12em] uppercase',
                                    active
                                        ? 'border-copper text-copper-bright'
                                        : 'border-transparent text-dust hover:text-bone',
                                )}
                            >
                                {tab.label}
                                <span
                                    className={cn(
                                        'font-sans text-base tracking-normal normal-case',
                                        tab.key === 'overdue'
                                            ? 'text-status-overdue'
                                            : 'text-faded',
                                    )}
                                >
                                    {counts[tab.key]}
                                </span>
                            </Link>
                        );
                    })}
                </nav>

                <LedgerTable minWidth={820}>
                    <thead>
                        <tr>
                            <Th>No.</Th>
                            <Th>Book</Th>
                            <Th>Reader</Th>
                            <Th>Dates</Th>
                            <Th>Status</Th>
                            <Th>
                                <SrOnly>Actions</SrOnly>
                            </Th>
                        </tr>
                    </thead>
                    <tbody>
                        {borrowings.data.map((borrowing) => {
                            const shown = displayStatus(borrowing);

                            return (
                                <tr key={borrowing.id}>
                                    <Td className="text-faded tabular-nums">
                                        {borrowing.id}
                                    </Td>
                                    <Td>
                                        <div className="font-semibold">
                                            {borrowing.book?.title}
                                        </div>
                                        <div className="text-sm text-dust">
                                            {borrowing.book?.author}
                                        </div>
                                    </Td>
                                    <Td>
                                        <Link
                                            href={showMember(
                                                borrowing.member_id,
                                            )}
                                            className="hover:text-copper-bright"
                                        >
                                            {borrowing.member?.name}
                                        </Link>
                                    </Td>
                                    <Td
                                        className={cn(
                                            'whitespace-nowrap',
                                            shown === 'overdue'
                                                ? 'text-status-overdue'
                                                : 'text-bone-soft',
                                        )}
                                    >
                                        {datesFor(borrowing)}
                                    </Td>
                                    <Td>
                                        <StatusMarker status={shown} />
                                    </Td>
                                    <Td
                                        align="right"
                                        className="whitespace-nowrap"
                                    >
                                        {borrowing.status === 'pending' && (
                                            <>
                                                <Button
                                                    asChild
                                                    size="sm"
                                                    className={capsButton}
                                                >
                                                    <Link
                                                        href={approve(
                                                            borrowing.id,
                                                        )}
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
                                                        href={reject(
                                                            borrowing.id,
                                                        )}
                                                        as="button"
                                                        preserveScroll
                                                    >
                                                        Reject
                                                    </Link>
                                                </Button>
                                            </>
                                        )}
                                        {borrowing.status === 'borrowed' && (
                                            <Button
                                                asChild
                                                size="sm"
                                                variant="outline"
                                                className={capsButton}
                                            >
                                                <Link
                                                    href={markReturned(
                                                        borrowing.id,
                                                    )}
                                                    as="button"
                                                    preserveScroll
                                                >
                                                    Mark returned
                                                </Link>
                                            </Button>
                                        )}
                                    </Td>
                                </tr>
                            );
                        })}
                    </tbody>
                </LedgerTable>

                {borrowings.total === 0 && (
                    <p className="text-dust italic">Nothing here right now.</p>
                )}
                <Pagination page={borrowings} noun="borrowings" />
            </PageBody>
        </>
    );
}

BorrowingsIndex.layout = {
    breadcrumbs: [{ title: 'Borrowings', href: index() }],
};
