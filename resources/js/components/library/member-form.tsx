import { Form, Link } from '@inertiajs/react';
import {
    capsButton,
    Field,
    FormActions,
    TextArea,
    TextInput,
} from '@/components/library/form-controls';
import { Button } from '@/components/ui/button';
import { index, store, update } from '@/routes/members';
import type { Member } from '@/types';

// Shared by members/create and members/edit
export function MemberForm({ member }: { member?: Member }) {
    const action = member ? update.form(member.id) : store.form();

    return (
        <Form
            {...action}
            disableWhileProcessing
            className="flex max-w-xl flex-col gap-5 border-t border-copper pt-6"
        >
            {({ errors, processing }) => (
                <>
                    <Field label="Full name" htmlFor="name" error={errors.name}>
                        <TextInput
                            id="name"
                            name="name"
                            required
                            autoFocus
                            autoComplete="name"
                            defaultValue={member?.name}
                        />
                    </Field>
                    <Field
                        label="Contact number"
                        htmlFor="contact_number"
                        error={errors.contact_number}
                        help="We call this number when a book is overdue."
                    >
                        <TextInput
                            id="contact_number"
                            name="contact_number"
                            type="tel"
                            required
                            autoComplete="tel"
                            defaultValue={member?.contact_number}
                            className="max-w-64"
                        />
                    </Field>
                    <Field
                        label="Address"
                        htmlFor="address"
                        error={errors.address}
                    >
                        <TextArea
                            id="address"
                            name="address"
                            rows={3}
                            required
                            autoComplete="street-address"
                            defaultValue={member?.address}
                        />
                    </Field>
                    <FormActions>
                        <Button
                            type="submit"
                            size="lg"
                            disabled={processing}
                            className={capsButton}
                        >
                            {member ? 'Save changes' : 'Save reader'}
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
    );
}
