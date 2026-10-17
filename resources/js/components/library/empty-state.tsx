import type { ReactNode } from 'react';
import { ColumnEmblem } from '@/components/library/column-emblem';

// Shown when a list has nothing in it yet
export function EmptyState({
    title,
    children,
}: {
    title: string;
    children?: ReactNode;
}) {
    return (
        <div className="flex flex-col items-center gap-3 border border-dashed border-line-strong px-6 py-12 text-center">
            <ColumnEmblem className="size-9 text-copper" />
            <p className="font-display text-lg font-semibold">{title}</p>
            {children && (
                <div className="max-w-md text-base text-dust">{children}</div>
            )}
        </div>
    );
}
