import { Head } from '@inertiajs/react';
import { BookForm } from '@/components/library/book-form';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { StatusMarker } from '@/components/library/status-marker';
import { index } from '@/routes/books';
import type { Book, Category } from '@/types';

export default function BooksEdit({
    book,
    categories,
}: {
    book: Book;
    categories: Category[];
}) {
    return (
        <>
            <Head title={`Edit ${book.title}`} />
            <PageBody>
                <PageHeader
                    title={book.title}
                    description={
                        <span className="flex items-center gap-3">
                            Status:
                            <StatusMarker status={book.availability_status} />
                        </span>
                    }
                />
                <BookForm book={book} categories={categories} />
            </PageBody>
        </>
    );
}

BooksEdit.layout = {
    breadcrumbs: [
        { title: 'Books', href: index() },
        { title: 'Edit book', href: index() },
    ],
};
