import { Head } from '@inertiajs/react';

export default function MembersEdit() {
    return (
        <>
            <Head title="Edit Member" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Edit Member */}
            </div>
        </>
    );
}

MembersEdit.layout = {
    breadcrumbs: [
        { title: 'Members', href: '/members' },
        { title: 'Edit Member', href: '#' },
    ],
};
