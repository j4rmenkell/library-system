import { Head } from '@inertiajs/react';
import { BookForm } from '@/components/library/book-form';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { create, index } from '@/routes/books';
import type { Category } from '@/types';

export default function BooksCreate({
    categories,
}: {
    categories: Category[];
}) {
    return (
        <>
            <Head title="Add a book" />
            <PageBody>
                <PageHeader
                    title="Add a book"
                    description="It goes on the shelf as Available."
                />
                <BookForm categories={categories} />
            </PageBody>
        </>
    );
}

BooksCreate.layout = {
    breadcrumbs: [
        { title: 'Books', href: index() },
        { title: 'Add a book', href: create() },
    ],
};
