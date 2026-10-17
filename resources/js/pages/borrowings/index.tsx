import { Head } from '@inertiajs/react';

export default function BorrowingsIndex() {
    return (
        <>
            <Head title="Borrowings" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Borrowings */}
            </div>
        </>
    );
}

BorrowingsIndex.layout = {
    breadcrumbs: [
        { title: 'Borrowings', href: '/borrowings' },
    ],
};
