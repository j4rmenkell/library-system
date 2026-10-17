import { cn } from '@/lib/utils';
import type { DisplayStatus } from '@/types';

const STATUSES: Record<DisplayStatus, { label: string; className: string }> = {
    available: { label: 'Available', className: 'text-status-available' },
    borrowed: { label: 'Borrowed', className: 'text-status-borrowed' },
    pending: { label: 'Pending', className: 'text-status-pending' },
    overdue: { label: 'Overdue', className: 'text-status-overdue' },
    returned: { label: 'Returned', className: 'text-status-closed' },
    rejected: { label: 'Rejected', className: 'text-status-closed' },
};

// A small diamond plus a word, used everywhere a status is shown
export function StatusMarker({
    status,
    label,
    className,
}: {
    status: DisplayStatus;
    label?: string;
    className?: string;
}) {
    const config = STATUSES[status];

    return (
        <span
            className={cn(
                'inline-flex items-center gap-2 font-caps text-[11px] font-semibold tracking-[0.1em] whitespace-nowrap uppercase',
                config.className,
                className,
            )}
        >
            <span
                aria-hidden="true"
                className="size-[7px] rotate-45 bg-current"
            />
            {label ?? config.label}
        </span>
    );
}
