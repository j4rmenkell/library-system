import { Head } from '@inertiajs/react';

export default function BooksEdit() {
    return (
        <>
            <Head title="Edit Book" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Edit Book */}
            </div>
        </>
    );
}

BooksEdit.layout = {
    breadcrumbs: [
        { title: 'Books', href: '/books' },
        { title: 'Edit Book', href: '#' },
    ],
};
