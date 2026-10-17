import { Head } from '@inertiajs/react';

export default function UsersEdit() {
    return (
        <>
            <Head title="Edit User" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Edit User */}
            </div>
        </>
    );
}

UsersEdit.layout = {
    breadcrumbs: [
        { title: 'Users', href: '/users' },
        { title: 'Edit User', href: '#' },
    ],
};
