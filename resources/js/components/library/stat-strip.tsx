import { Fragment } from 'react';
import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export type Stat = {
    label: string;
    value: ReactNode;
    tone?: 'default' | 'overdue' | 'pending';
};

// A short double-ruled post with copper caps, used between figures
function Wall() {
    return (
        <span
            aria-hidden="true"
            className="flex flex-col items-center self-stretch"
        >
            <span className="h-0.5 w-[9px] bg-copper" />
            <span className="w-[3px] flex-1 border-x border-line-strong bg-line" />
            <span className="h-0.5 w-[9px] bg-copper" />
        </span>
    );
}

const tones = {
    default: '',
    overdue: 'text-status-overdue',
    pending: 'text-status-pending',
};

export function StatStrip({ stats }: { stats: Stat[] }) {
    return (
        <dl className="flex flex-wrap gap-y-4">
            {stats.map((stat, index) => (
                <Fragment key={stat.label}>
                    <div className="flex min-w-40 flex-1 items-center gap-5 pr-5">
                        {index > 0 && <Wall />}
                        <div className="flex flex-col-reverse gap-1 py-1.5">
                            <dt className="font-caps text-xs font-semibold tracking-[0.12em] text-dust uppercase">
                                {stat.label}
                            </dt>
                            <dd
                                className={cn(
                                    'font-display text-4xl font-semibold tabular-nums',
                                    tones[stat.tone ?? 'default'],
                                )}
                            >
                                {stat.value}
                            </dd>
                        </div>
                    </div>
                </Fragment>
            ))}
        </dl>
    );
}
