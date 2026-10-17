import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { Paginated } from '@/types';

// Laravel labels the arrows "&laquo; Previous" and "Next &raquo;"
function cleanLabel(label: string): string {
    return label.replace('&laquo;', '').replace('&raquo;', '').trim();
}

export function Pagination({
    page,
    noun,
}: {
    page: Paginated<unknown>;
    noun: string;
}) {
    if (page.total === 0) {
        return null;
    }

    return (
        <nav
            aria-label="Pages"
            className="flex flex-wrap items-center justify-between gap-3 text-base text-dust"
        >
            <span>
                Showing {page.from}–{page.to} of {page.total} {noun}
            </span>
            {page.last_page > 1 && (
                <div className="flex items-center gap-1">
                    {page.links.map((link, index) => {
                        const label = cleanLabel(link.label);
                        const isWord = Number.isNaN(Number(label));

                        if (!link.url) {
                            return (
                                <span key={index} className="px-2 text-faded">
                                    {label}
                                </span>
                            );
                        }

                        return (
                            <Link
                                key={index}
                                href={link.url}
                                preserveScroll
                                aria-current={link.active ? 'page' : undefined}
                                className={cn(
                                    'inline-flex h-9 items-center justify-center no-underline',
                                    isWord
                                        ? 'px-2 text-copper-bright hover:underline'
                                        : 'w-9 font-display text-sm text-bone-soft',
                                    link.active &&
                                        'border border-copper text-copper-bright',
                                )}
                            >
                                {label}
                            </Link>
                        );
                    })}
                </div>
            )}
        </nav>
    );
}
