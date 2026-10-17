import { MAX_ACTIVE_LOANS } from '@/lib/library';
import { cn } from '@/lib/utils';

const HEIGHTS = ['h-[18px]', 'h-[15px]', 'h-[17px]'];

// Three little book spines: filled for each book a reader has out
export function LoanSlots({ count }: { count: number }) {
    const full = count >= MAX_ACTIVE_LOANS;

    return (
        <span
            className="inline-flex items-end gap-1"
            aria-label={`${count} of ${MAX_ACTIVE_LOANS} books out`}
        >
            {Array.from({ length: MAX_ACTIVE_LOANS }, (_, index) => (
                <span
                    key={index}
                    aria-hidden="true"
                    className={cn(
                        'w-2 border',
                        HEIGHTS[index],
                        index < count
                            ? full
                                ? 'border-status-pending bg-status-pending'
                                : 'border-copper bg-copper'
                            : 'border-line-strong',
                    )}
                />
            ))}
            <span
                aria-hidden="true"
                className={cn(
                    'ml-2 text-sm',
                    full ? 'text-status-pending' : 'text-dust',
                )}
            >
                {count} of {MAX_ACTIVE_LOANS}
            </span>
        </span>
    );
}
