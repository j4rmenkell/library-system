import { Head } from '@inertiajs/react';

export default function CatalogIndex() {
    return (
        <>
            <Head title="Book Catalog" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {/* TODO: Book Catalog */}
            </div>
        </>
    );
}

CatalogIndex.layout = {
    breadcrumbs: [{ title: 'Book Catalog', href: '/catalog' }],
};
