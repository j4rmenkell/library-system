import { useId } from 'react';
import { cn } from '@/lib/utils';

// A repeating Greek key (meander) border, drawn as an SVG pattern
export function GreekKey({
    className,
    muted = false,
}: {
    className?: string;
    muted?: boolean;
}) {
    const id = useId();
    const stroke = muted ? 'var(--color-line-strong)' : 'var(--color-copper)';

    return (
        <svg aria-hidden="true" className={cn('block h-3 w-full', className)}>
            <defs>
                <pattern
                    id={id}
                    width="24"
                    height="12"
                    patternUnits="userSpaceOnUse"
                >
                    <path
                        d="M0 11.5h5V1.5h10v7.5H10V5.5"
                        fill="none"
                        stroke={stroke}
                        strokeWidth="1"
                    />
                    <path d="M15 11.5h9" stroke={stroke} strokeWidth="1" />
                </pattern>
            </defs>
            <rect width="100%" height="12" fill={`url(#${id})`} />
        </svg>
    );
}
