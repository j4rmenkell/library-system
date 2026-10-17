import { Head } from '@inertiajs/react';

export default function BooksIndex() {
    return (
        <>
            <Head title="Books" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Books */}
            </div>
        </>
    );
}

BooksIndex.layout = {
    breadcrumbs: [
        { title: 'Books', href: '/books' },
    ],
};
