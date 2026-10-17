import { Head } from '@inertiajs/react';

export default function MembersShow() {
    return (
        <>
            <Head title="Member Details" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Member Details */}
            </div>
        </>
    );
}

MembersShow.layout = {
    breadcrumbs: [
        { title: 'Members', href: '/members' },
        { title: 'Member Details', href: '#' },
    ],
};
