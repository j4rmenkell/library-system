import { Head } from '@inertiajs/react';
import { MemberForm } from '@/components/library/member-form';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { create, index } from '@/routes/members';

export default function MembersCreate() {
    return (
        <>
            <Head title="Register a walk-in reader" />
            <PageBody>
                <PageHeader
                    title="Register a walk-in reader"
                    description="For readers who borrow at the desk without an account. Anyone who signs up online is added automatically."
                />
                <MemberForm />
            </PageBody>
        </>
    );
}

MembersCreate.layout = {
    breadcrumbs: [
        { title: 'Members', href: index() },
        { title: 'Register a walk-in', href: create() },
    ],
};
