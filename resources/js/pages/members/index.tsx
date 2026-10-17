import { Head } from '@inertiajs/react';

export default function MembersIndex() {
    return (
        <>
            <Head title="Members" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Members */}
            </div>
        </>
    );
}

MembersIndex.layout = {
    breadcrumbs: [{ title: 'Members', href: '/members' }],
};
