import type { SVGAttributes } from 'react';
import { ColumnEmblem } from '@/components/library/column-emblem';

// The Athenaeum's column mark, used on the login and register pages.
// Inline styles win over the "fill-current text-black" classes the starter
// kit's layouts pass, so the line drawing stays copper and unfilled.
export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <ColumnEmblem
            {...props}
            style={{
                fill: 'none',
                color: 'var(--color-copper)',
                ...props.style,
            }}
        />
    );
}
