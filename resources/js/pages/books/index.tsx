import { Form, Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { BookCover } from '@/components/library/book-cover';
import { ConfirmDialog } from '@/components/library/confirm-dialog';
import {
    capsButton,
    SelectInput,
    TextInput,
} from '@/components/library/form-controls';
import { LedgerTable, SrOnly, Td, Th } from '@/components/library/ledger-table';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Pagination } from '@/components/library/pagination';
import { StatusMarker } from '@/components/library/status-marker';
import { Button } from '@/components/ui/button';
import { create, destroy, edit, index } from '@/routes/books';
import type { Book, Category, Paginated } from '@/types';

type Filters = { search?: string; category?: string; status?: string };

export default function BooksIndex({
    books,
    categories,
    filters,
}: {
    books: Paginated<Book>;
    categories: Category[];
    filters: Filters;
}) {
    const [deleting, setDeleting] = useState<Book | null>(null);

    return (
        <>
            <Head title="Books" />
            <PageBody>
                <PageHeader
                    title="Books"
                    description={`${books.total} in the collection.`}
                    actions={
                        <Button asChild className={capsButton}>
                            <Link href={create()}>Add a book</Link>
                        </Button>
                    }
                />

                {/* A GET form: submitting puts the filters in the URL */}
                <Form
                    {...index.form()}
                    options={{ preserveState: true }}
                    className="flex flex-wrap gap-2.5"
                >
                    <label htmlFor="search" className="sr-only">
                        Search books
                    </label>
                    <TextInput
                        id="search"
                        name="search"
                        type="search"
                        placeholder="Title, author or ISBN"
                        defaultValue={filters.search}
                        className="flex-[1_1_260px]"
                    />
                    <label htmlFor="category" className="sr-only">
                        Category
                    </label>
                    <SelectInput
                        id="category"
                        name="category"
                        defaultValue={filters.category ?? ''}
                        className="flex-[0_1_220px]"
                    >
                        <option value="">All categories</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </SelectInput>
                    <label htmlFor="status" className="sr-only">
                        Status
                    </label>
                    <SelectInput
                        id="status"
                        name="status"
                        defaultValue={filters.status ?? ''}
                        className="flex-[0_1_170px]"
                    >
                        <option value="">Any status</option>
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                    </SelectInput>
                    <Button
                        type="submit"
                        variant="outline"
                        size="lg"
                        className={capsButton}
                    >
                        Filter
                    </Button>
                </Form>

                <LedgerTable minWidth={780}>
                    <thead>
                        <tr>
                            <Th>Title</Th>
                            <Th>ISBN</Th>
                            <Th>Category</Th>
                            <Th>Year</Th>
                            <Th>Status</Th>
                            <Th>
                                <SrOnly>Actions</SrOnly>
                            </Th>
                        </tr>
                    </thead>
                    <tbody>
                        {books.data.map((book) => (
                            <tr key={book.id}>
                                <Td>
                                    <div className="flex items-center gap-3">
                                        <BookCover
                                            book={book}
                                            size="xs"
                                            className="w-8 flex-none"
                                        />
                                        <div>
                                            <div className="font-semibold">
                                                {book.title}
                                            </div>
                                            <div className="text-sm text-dust">
                                                {book.author}
                                            </div>
                                        </div>
                                    </div>
                                </Td>
                                <Td className="whitespace-nowrap text-bone-soft tabular-nums">
                                    {book.isbn}
                                </Td>
                                <Td>{book.category?.name}</Td>
                                <Td className="text-bone-soft">
                                    {book.publication_year ?? '—'}
                                </Td>
                                <Td>
                                    <StatusMarker
                                        status={book.availability_status}
                                    />
                                </Td>
                                <Td align="right" className="whitespace-nowrap">
                                    <Link
                                        href={edit(book.id)}
                                        className="text-copper-bright underline underline-offset-3"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => setDeleting(book)}
                                        className="ml-4 text-status-overdue"
                                    >
                                        Delete
                                    </button>
                                </Td>
                            </tr>
                        ))}
                    </tbody>
                </LedgerTable>

                <Pagination page={books} noun="books" />
            </PageBody>

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={`Delete ${deleting?.title}?`}
                description="Books with borrowing history can't be deleted, so their records stay intact."
                confirmLabel="Delete book"
                onConfirm={() =>
                    deleting &&
                    router.delete(destroy.url(deleting.id), {
                        onFinish: () => setDeleting(null),
                    })
                }
            />
        </>
    );
}

BooksIndex.layout = {
    breadcrumbs: [{ title: 'Books', href: index() }],
};
