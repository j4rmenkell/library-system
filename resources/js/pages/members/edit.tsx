import { Head } from '@inertiajs/react';
import { MemberForm } from '@/components/library/member-form';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { index } from '@/routes/members';
import type { Member } from '@/types';

export default function MembersEdit({ member }: { member: Member }) {
    return (
        <>
            <Head title={`Edit ${member.name}`} />
            <PageBody>
                <PageHeader
                    title={`Edit ${member.name}`}
                    description={
                        member.user
                            ? 'Their login details are managed on the Users page.'
                            : 'Walk-in reader, with no online account.'
                    }
                />
                <MemberForm member={member} />
            </PageBody>
        </>
    );
}

MembersEdit.layout = {
    breadcrumbs: [
        { title: 'Members', href: index() },
        { title: 'Edit reader', href: index() },
    ],
};
