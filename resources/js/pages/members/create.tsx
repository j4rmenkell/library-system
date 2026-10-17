import { Head } from '@inertiajs/react';

export default function MembersCreate() {
    return (
        <>
            <Head title="Add Member" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Add Member */}
            </div>
        </>
    );
}

MembersCreate.layout = {
    breadcrumbs: [
        { title: 'Members', href: '/members' },
        { title: 'Add Member', href: '/members/create' },
    ],
};
