import { ColumnEmblem } from '@/components/library/column-emblem';
import { cn } from '@/lib/utils';
import type { Book } from '@/types';

// Dark cloth colours for books without a cover image
const CLOTH = [
    '#2b1d14',
    '#1d2a2b',
    '#3a1f1f',
    '#2e2a1a',
    '#1f2433',
    '#33231a',
];

export function clothFor(book: Pick<Book, 'id'>): string {
    return CLOTH[book.id % CLOTH.length];
}

// Shows the real cover when there is one, otherwise a framed placeholder
export function BookCover({
    book,
    size = 'md',
    className,
}: {
    book: Pick<Book, 'id' | 'title' | 'author' | 'cover_url'>;
    size?: 'xs' | 'md';
    className?: string;
}) {
    if (book.cover_url) {
        return (
            <img
                src={book.cover_url}
                alt={`Cover of ${book.title}`}
                className={cn(
                    'aspect-[2/3] w-full border border-line-strong object-cover',
                    className,
                )}
            />
        );
    }

    if (size === 'xs') {
        return (
            <span
                aria-hidden="true"
                className={cn(
                    'flex aspect-[2/3] border border-line-strong p-[3px]',
                    className,
                )}
                style={{ backgroundColor: clothFor(book) }}
            >
                <span className="flex-1 border border-copper" />
            </span>
        );
    }

    return (
        <div
            role="img"
            aria-label={`${book.title} by ${book.author}`}
            className={cn(
                'flex aspect-[2/3] w-full border border-line-strong p-1.5',
                className,
            )}
            style={{ backgroundColor: clothFor(book) }}
        >
            <div className="flex flex-1 flex-col items-center justify-between border border-copper px-2.5 py-3.5 text-center">
                <ColumnEmblem className="size-7 text-copper" />
                <span className="font-display text-base leading-tight font-semibold text-bone">
                    {book.title}
                </span>
                <span className="font-caps text-[9px] tracking-[0.16em] text-dust uppercase">
                    {book.author}
                </span>
            </div>
        </div>
    );
}
