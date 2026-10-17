import type { ComponentProps } from 'react';
import { cn } from '@/lib/utils';

// Ledger-style table: a copper rule under the headers, thin lines between rows
export function LedgerTable({
    className,
    minWidth = 720,
    tone = 'default',
    ...props
}: ComponentProps<'table'> & {
    minWidth?: number;
    tone?: 'default' | 'overdue';
}) {
    return (
        <div className="overflow-x-auto">
            <table
                className={cn(
                    'w-full border-collapse text-base',
                    tone === 'overdue' && '[&_th]:border-status-overdue',
                    className,
                )}
                style={{ minWidth }}
                {...props}
            />
        </div>
    );
}

export function Th({
    className,
    align = 'left',
    ...props
}: ComponentProps<'th'> & { align?: 'left' | 'right' }) {
    return (
        <th
            className={cn(
                'border-b border-copper px-3 py-2 font-caps text-[11px] font-semibold tracking-[0.14em] whitespace-nowrap text-copper uppercase first:pl-0 last:pr-0',
                align === 'right' ? 'text-right' : 'text-left',
                className,
            )}
            {...props}
        />
    );
}

export function Td({
    className,
    align = 'left',
    ...props
}: ComponentProps<'td'> & { align?: 'left' | 'right' }) {
    return (
        <td
            className={cn(
                'border-b border-line px-3 py-3 align-middle first:pl-0 last:pr-0',
                align === 'right' && 'text-right',
                className,
            )}
            {...props}
        />
    );
}

// Visually hidden header text, for action columns
export function SrOnly({ children }: { children: string }) {
    return <span className="sr-only">{children}</span>;
}
