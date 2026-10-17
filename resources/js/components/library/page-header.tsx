import type { ReactNode } from 'react';

export function PageHeader({
    title,
    description,
    actions,
}: {
    title: string;
    description?: ReactNode;
    actions?: ReactNode;
}) {
    return (
        <div className="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 className="font-display text-3xl font-semibold tracking-[0.02em] text-balance">
                    {title}
                </h1>
                {description && (
                    <p className="mt-1.5 text-lg text-dust">{description}</p>
                )}
            </div>
            {actions && <div className="flex flex-wrap gap-2.5">{actions}</div>}
        </div>
    );
}

// Wraps every library page with the same padding and width
export function PageBody({ children }: { children: ReactNode }) {
    return (
        <div className="flex w-full max-w-6xl flex-col gap-7 p-6 md:p-8">
            {children}
        </div>
    );
}

export function SectionHeading({
    children,
    action,
    tone = 'default',
}: {
    children: ReactNode;
    action?: ReactNode;
    tone?: 'default' | 'overdue';
}) {
    return (
        <div className="flex items-baseline justify-between gap-4">
            <h2
                className={
                    tone === 'overdue'
                        ? 'font-display text-xl font-semibold text-status-overdue'
                        : 'font-display text-xl font-semibold'
                }
            >
                {children}
            </h2>
            {action}
        </div>
    );
}
