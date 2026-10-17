import { Head } from '@inertiajs/react';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { UserForm } from '@/components/library/user-form';
import { create, index } from '@/routes/users';

export default function UsersCreate() {
    return (
        <>
            <Head title="Add a user" />
            <PageBody>
                <PageHeader
                    title="Add a user"
                    description="For staff accounts. Readers usually sign up on their own."
                />
                <UserForm />
            </PageBody>
        </>
    );
}

UsersCreate.layout = {
    breadcrumbs: [
        { title: 'Users', href: index() },
        { title: 'Add a user', href: create() },
    ],
};
