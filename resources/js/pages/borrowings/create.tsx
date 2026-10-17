import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    capsButton,
    Field,
    FormActions,
    SelectInput,
} from '@/components/library/form-controls';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Button } from '@/components/ui/button';
import { LOAN_DAYS, MAX_ACTIVE_LOANS } from '@/lib/library';
import { create, index, store } from '@/routes/borrowings';
import { create as registerMember } from '@/routes/members';
import type { Book, Member } from '@/types';

const today = new Date();
const dueDate = new Date(today.getTime() + LOAN_DAYS * 24 * 60 * 60 * 1000);

export default function BorrowingsCreate({
    members,
    books,
}: {
    members: Member[];
    books: Book[];
}) {
    const preselected =
        new URLSearchParams(window.location.search).get('member') ?? '';
    const [memberId, setMemberId] = useState(preselected);
    const member = members.find((m) => String(m.id) === memberId);
    const outAfter = (member?.active_borrowings_count ?? 0) + 1;

    return (
        <>
            <Head title="Issue a book" />
            <PageBody>
                <PageHeader
                    title="Issue a book"
                    description="For a reader standing at the desk. The loan starts now, with no request step."
                />

                <div className="flex flex-wrap items-start gap-12">
                    <Form
                        {...store.form()}
                        disableWhileProcessing
                        className="flex max-w-xl flex-[2_1_380px] flex-col gap-5 border-t border-copper pt-6"
                    >
                        {({ errors, processing }) => (
                            <>
                                <Field
                                    label="Reader"
                                    htmlFor="member_id"
                                    error={errors.member_id}
                                    help={
                                        <>
                                            New here?{' '}
                                            <Link
                                                href={registerMember()}
                                                className="text-copper-bright underline-offset-4 hover:underline"
                                            >
                                                Register them first
                                            </Link>
                                            .
                                        </>
                                    }
                                >
                                    <SelectInput
                                        id="member_id"
                                        name="member_id"
                                        required
                                        value={memberId}
                                        onChange={(e) =>
                                            setMemberId(e.target.value)
                                        }
                                    >
                                        <option value="" disabled>
                                            Choose a reader
                                        </option>
                                        {members.map((m) => {
                                            const count =
                                                m.active_borrowings_count ?? 0;
                                            const full =
                                                count >= MAX_ACTIVE_LOANS;

                                            return (
                                                <option
                                                    key={m.id}
                                                    value={m.id}
                                                    disabled={full}
                                                >
                                                    {m.name} ({count} of{' '}
                                                    {MAX_ACTIVE_LOANS} out
                                                    {full
                                                        ? ', limit reached'
                                                        : ''}
                                                    )
                                                </option>
                                            );
                                        })}
                                    </SelectInput>
                                </Field>
                                <Field
                                    label="Book"
                                    htmlFor="book_id"
                                    error={errors.book_id}
                                    help="Only books on the shelf with no waiting request are listed."
                                >
                                    <SelectInput
                                        id="book_id"
                                        name="book_id"
                                        required
                                        defaultValue=""
                                    >
                                        <option value="" disabled>
                                            Choose a book
                                        </option>
                                        {books.map((book) => (
                                            <option
                                                key={book.id}
                                                value={book.id}
                                            >
                                                {book.title}, {book.author}
                                            </option>
                                        ))}
                                    </SelectInput>
                                </Field>
                                <FormActions>
                                    <Button
                                        type="submit"
                                        size="lg"
                                        disabled={processing}
                                        className={capsButton}
                                    >
                                        Issue book
                                    </Button>
                                    <Link
                                        href={index()}
                                        className="text-lg text-copper-bright underline-offset-4 hover:underline"
                                    >
                                        Cancel
                                    </Link>
                                </FormActions>
                            </>
                        )}
                    </Form>

                    <aside
                        aria-label="Loan summary"
                        className="flex-[0_1_280px] border border-copper p-1.5"
                    >
                        <div className="flex flex-col gap-3.5 border border-line-strong p-6 text-center">
                            <span className="font-caps text-[11px] font-semibold tracking-[0.22em] text-copper uppercase">
                                Return by
                            </span>
                            <span className="font-display text-4xl font-semibold">
                                {dueDate.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                })}
                            </span>
                            <span className="text-dust">
                                {dueDate.toLocaleDateString('en-US', {
                                    weekday: 'long',
                                    year: 'numeric',
                                })}
                            </span>
                            <dl className="mt-1.5 flex flex-col gap-2 border-t border-line pt-3.5 text-left">
                                <div className="flex justify-between">
                                    <dt className="text-dust">Borrowed</dt>
                                    <dd>
                                        {today.toLocaleDateString('en-US', {
                                            month: 'short',
                                            day: 'numeric',
                                        })}
                                    </dd>
                                </div>
                                <div className="flex justify-between">
                                    <dt className="text-dust">Loan period</dt>
                                    <dd>{LOAN_DAYS} days</dd>
                                </div>
                                {member && (
                                    <div className="flex justify-between">
                                        <dt className="text-dust">
                                            Reader after this
                                        </dt>
                                        <dd>
                                            {outAfter} of {MAX_ACTIVE_LOANS} out
                                        </dd>
                                    </div>
                                )}
                            </dl>
                        </div>
                    </aside>
                </div>
            </PageBody>
        </>
    );
}

BorrowingsCreate.layout = {
    breadcrumbs: [
        { title: 'Borrowings', href: index() },
        { title: 'Issue a book', href: create() },
    ],
};
