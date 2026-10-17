import { Head } from '@inertiajs/react';

export default function BooksCreate() {
    return (
        <>
            <Head title="Add Book" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Add Book */}
            </div>
        </>
    );
}

BooksCreate.layout = {
    breadcrumbs: [
        { title: 'Books', href: '/books' },
        { title: 'Add Book', href: '/books/create' },
    ],
};
