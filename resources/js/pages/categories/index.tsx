import { Head } from '@inertiajs/react';

export default function CategoriesIndex() {
    return (
        <>
            <Head title="Categories" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Categories */}
            </div>
        </>
    );
}

CategoriesIndex.layout = {
    breadcrumbs: [
        { title: 'Categories', href: '/categories' },
    ],
};
