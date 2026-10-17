import { Head } from '@inertiajs/react';

export default function LogsIndex() {
    return (
        <>
            <Head title="Activity Logs" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Activity Logs */}
            </div>
        </>
    );
}

LogsIndex.layout = {
    breadcrumbs: [{ title: 'Activity Logs', href: '/logs' }],
};
