import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { BookCover, clothFor } from '@/components/library/book-cover';
import { cn } from '@/lib/utils';
import { index as catalog } from '@/routes/catalog';
import type { Book } from '@/types';

// A row of book spines; hovering or focusing one pulls that book off the
// shelf. Same idea as the ReactBits Accordion Gallery.
export function ShelfGallery({ books }: { books: Book[] }) {
    const [active, setActive] = useState(0);

    return (
        <div className="flex flex-col gap-3">
            <ul className="flex h-80 gap-2 overflow-x-auto">
                {books.map((book, index) => {
                    const open = index === active;

                    return (
                        <li
                            key={book.id}
                            onMouseEnter={() => setActive(index)}
                            className={cn(
                                'flex min-w-16 overflow-hidden border p-1.5 transition-[flex-grow] duration-500 ease-out',
                                open
                                    ? 'grow-[6] border-copper'
                                    : 'grow-0 border-line',
                            )}
                            style={{ backgroundColor: clothFor(book) }}
                        >
                            {open ? (
                                <div className="flex flex-1 items-center gap-5 border border-line-strong p-5">
                                    <BookCover
                                        book={book}
                                        className="w-36 flex-none"
                                    />
                                    <div className="flex min-w-0 flex-col gap-2">
                                        <span className="font-caps text-[11px] font-semibold tracking-[0.16em] text-copper uppercase">
                                            {book.category?.name}
                                        </span>
                                        <span className="font-display text-2xl leading-tight font-semibold">
                                            {book.title}
                                        </span>
                                        <span className="text-lg text-bone-soft">
                                            {book.author}
                                        </span>
                                        <Link
                                            href={catalog({
                                                query: { search: book.title },
                                            })}
                                            className="mt-1 text-copper-bright underline-offset-4 hover:underline"
                                        >
                                            View in the catalog
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <button
                                    type="button"
                                    onFocus={() => setActive(index)}
                                    onClick={() => setActive(index)}
                                    aria-label={`Show ${book.title}`}
                                    className="flex flex-1 items-center justify-center border border-line-strong"
                                >
                                    <span className="rotate-180 font-display text-sm font-semibold tracking-[0.08em] whitespace-nowrap text-copper-bright [writing-mode:vertical-rl]">
                                        {book.title}
                                    </span>
                                </button>
                            )}
                        </li>
                    );
                })}
            </ul>
            <p className="text-base text-faded italic">
                Hover a spine to pull the book off the shelf.
            </p>
        </div>
    );
}
