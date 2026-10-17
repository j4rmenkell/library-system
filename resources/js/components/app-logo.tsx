import { ColumnEmblem } from '@/components/library/column-emblem';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-9 items-center justify-center rounded-full border border-copper text-copper">
                <ColumnEmblem className="size-5" />
            </div>
            <div className="ml-1 grid flex-1 text-left font-display text-sm leading-tight font-semibold tracking-[0.04em] text-bone">
                <span className="truncate">Marguerite’s</span>
                <span className="truncate">Athenaeum</span>
            </div>
        </>
    );
}
