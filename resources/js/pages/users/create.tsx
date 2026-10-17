import { Head } from '@inertiajs/react';

export default function UsersCreate() {
    return (
        <>
            <Head title="Add User" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Add User */}
            </div>
        </>
    );
}

UsersCreate.layout = {
    breadcrumbs: [
        { title: 'Users', href: '/users' },
        { title: 'Add User', href: '/users/create' },
    ],
};
