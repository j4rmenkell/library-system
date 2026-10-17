import { Head } from '@inertiajs/react';

export default function BorrowingsCreate() {
    return (
        <>
            <Head title="Issue Book" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Issue Book */}
            </div>
        </>
    );
}

BorrowingsCreate.layout = {
    breadcrumbs: [
        { title: 'Borrowings', href: '/borrowings' },
        { title: 'Issue Book', href: '/borrowings/create' },
    ],
};
