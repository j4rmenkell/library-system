import { Head } from '@inertiajs/react';

export default function MyBorrowingsIndex() {
    return (
        <>
            <Head title="My Borrowed Books" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: My Borrowed Books */}
            </div>
        </>
    );
}

MyBorrowingsIndex.layout = {
    breadcrumbs: [{ title: 'My Borrowed Books', href: '/my-borrowings' }],
};
