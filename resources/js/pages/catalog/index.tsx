import { Form, Head, Link } from '@inertiajs/react';
import { BookCover } from '@/components/library/book-cover';
import { capsButton, TextInput } from '@/components/library/form-controls';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Pagination } from '@/components/library/pagination';
import { StatusMarker } from '@/components/library/status-marker';
import { Button } from '@/components/ui/button';
import { MAX_ACTIVE_LOANS } from '@/lib/library';
import { cn } from '@/lib/utils';
import { borrow, index } from '@/routes/catalog';
import type { Book, Category, Paginated } from '@/types';

export default function CatalogIndex({
    books,
    categories,
    filters,
    activeLoans,
    requestedBookIds,
}: {
    books: Paginated<Book>;
    categories: Category[];
    filters: { search?: string; category?: string };
    activeLoans: number;
    requestedBookIds: number[];
}) {
    const atLimit = activeLoans >= MAX_ACTIVE_LOANS;
    const left = MAX_ACTIVE_LOANS - activeLoans;
    const chips = [{ id: '', name: 'All' }, ...categories];

    return (
        <>
            <Head title="Book catalog" />
            <PageBody>
                <PageHeader
                    title="Find a book"
                    description={
                        <span
                            className={
                                atLimit ? 'text-status-pending' : undefined
                            }
                        >
                            {atLimit
                                ? `You have ${MAX_ACTIVE_LOANS} of ${MAX_ACTIVE_LOANS} books. Return one to borrow more.`
                                : `You have ${activeLoans} of ${MAX_ACTIVE_LOANS} books. You can borrow ${left} more.`}
                        </span>
                    }
                />

                <div className="flex flex-col gap-3.5">
                    <Form
                        {...index.form()}
                        options={{ preserveState: true }}
                        className="flex max-w-xl gap-2.5"
                    >
                        <label htmlFor="search" className="sr-only">
                            Search the catalog
                        </label>
                        <TextInput
                            id="search"
                            name="search"
                            type="search"
                            placeholder="Title, author or ISBN"
                            defaultValue={filters.search}
                            className="h-12 border-copper text-xl"
                        />
                        {filters.category && (
                            <input
                                type="hidden"
                                name="category"
                                value={filters.category}
                            />
                        )}
                        <Button
                            type="submit"
                            size="lg"
                            className={`${capsButton} h-12`}
                        >
                            Search
                        </Button>
                    </Form>

                    <nav
                        aria-label="Category"
                        className="flex flex-wrap gap-x-6 border-b border-line"
                    >
                        {chips.map((chip) => {
                            const active =
                                String(chip.id) === (filters.category ?? '');

                            return (
                                <Link
                                    key={chip.id}
                                    href={index({
                                        query: {
                                            ...filters,
                                            category: chip.id || undefined,
                                        },
                                    })}
                                    preserveState
                                    aria-current={active ? 'page' : undefined}
                                    className={cn(
                                        '-mb-px flex h-10 items-center border-b-2 font-caps text-xs font-semibold tracking-[0.12em] uppercase',
                                        active
                                            ? 'border-copper text-copper-bright'
                                            : 'border-transparent text-dust hover:text-bone',
                                    )}
                                >
                                    {chip.name}
                                </Link>
                            );
                        })}
                    </nav>
                </div>

                <ul className="grid grid-cols-[repeat(auto-fill,minmax(170px,1fr))] gap-x-5 gap-y-8">
                    {books.data.map((book) => {
                        const out = book.availability_status === 'borrowed';
                        const requested = requestedBookIds.includes(book.id);

                        return (
                            <li
                                key={book.id}
                                className="flex min-w-0 flex-col gap-2.5"
                            >
                                <BookCover
                                    book={book}
                                    className={cn(out && 'opacity-50')}
                                />
                                <div className="flex flex-col">
                                    <span className="leading-tight font-semibold">
                                        {book.title}
                                    </span>
                                    <span className="text-sm text-dust">
                                        {book.author}
                                    </span>
                                </div>
                                <div className="flex items-baseline justify-between gap-2">
                                    <span className="truncate text-sm text-dust">
                                        {book.category?.name}
                                    </span>
                                    <StatusMarker
                                        status={book.availability_status}
                                        label={out ? 'Out' : 'On shelf'}
                                        className={
                                            out
                                                ? 'text-status-closed'
                                                : undefined
                                        }
                                    />
                                </div>

                                {requested ? (
                                    <span className="flex h-10 items-center justify-center border border-dashed border-status-pending text-status-pending">
                                        Requested · collect at the desk
                                    </span>
                                ) : out ? (
                                    <span className="flex h-10 items-center justify-center text-faded italic">
                                        Out on loan
                                    </span>
                                ) : atLimit ? (
                                    <span className="flex h-10 items-center justify-center text-faded italic">
                                        You’re at your limit of three
                                    </span>
                                ) : (
                                    <Button asChild className={capsButton}>
                                        <Link
                                            href={borrow(book.id)}
                                            as="button"
                                            preserveScroll
                                        >
                                            Borrow
                                        </Link>
                                    </Button>
                                )}
                            </li>
                        );
                    })}
                </ul>

                {books.total === 0 && (
                    <p className="text-dust italic">
                        No books match that search.
                    </p>
                )}
                <Pagination page={books} noun="books" />
            </PageBody>
        </>
    );
}

CatalogIndex.layout = {
    breadcrumbs: [{ title: 'Book Catalog', href: index() }],
};
