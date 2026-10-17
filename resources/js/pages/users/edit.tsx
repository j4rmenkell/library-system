import { Head, usePage } from '@inertiajs/react';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { UserForm } from '@/components/library/user-form';
import { index } from '@/routes/users';
import type { User } from '@/types';

export default function UsersEdit({ user }: { user: User }) {
    const { auth } = usePage().props;
    const isSelf = auth.user.id === user.id;

    return (
        <>
            <Head title={`Edit ${user.name}`} />
            <PageBody>
                <PageHeader
                    title={`Edit ${user.name}`}
                    description={
                        isSelf
                            ? 'This is you. You can’t change your own role, so the library always keeps an admin.'
                            : `@${user.username}`
                    }
                />
                <UserForm user={user} />
            </PageBody>
        </>
    );
}

UsersEdit.layout = {
    breadcrumbs: [
        { title: 'Users', href: index() },
        { title: 'Edit user', href: index() },
    ],
};
