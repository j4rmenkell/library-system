import type { SVGAttributes } from 'react';

// The Athenaeum's temple-column mark
export function ColumnEmblem(props: SVGAttributes<SVGElement>) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="1.5"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            {...props}
        >
            <path d="M3 21h18M5 18h14M7 18V9M10.3 18V9M13.7 18V9M17 18V9M4 9h16M4 9l8-6 8 6" />
        </svg>
    );
}
