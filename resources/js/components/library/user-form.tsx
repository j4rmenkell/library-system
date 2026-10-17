import { Form, Link } from '@inertiajs/react';
import { useState } from 'react';
import {
    capsButton,
    Field,
    FormActions,
    TextInput,
} from '@/components/library/form-controls';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import { index, store, update } from '@/routes/users';
import type { Role, User } from '@/types';

const ROLES: { value: Role; label: string; description: string }[] = [
    {
        value: 'librarian',
        label: 'Librarian',
        description: 'Books, categories, readers and borrowings.',
    },
    {
        value: 'admin',
        label: 'Admin',
        description:
            'Everything a librarian does, plus users and activity logs.',
    },
    {
        value: 'borrower',
        label: 'Reader',
        description: 'Browses the catalog and requests books.',
    },
];

// Shared by users/create and users/edit
export function UserForm({ user }: { user?: User }) {
    const [role, setRole] = useState<Role>(user?.role ?? 'librarian');
    const action = user ? update.form(user.id) : store.form();

    return (
        <Form
            {...action}
            disableWhileProcessing
            resetOnSuccess={['password', 'password_confirmation']}
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
                            defaultValue={user?.name}
                        />
                    </Field>
                    <div className="flex flex-wrap gap-5">
                        <Field
                            label="Username"
                            htmlFor="username"
                            error={errors.username}
                            className="flex-[1_1_200px]"
                        >
                            <TextInput
                                id="username"
                                name="username"
                                required
                                autoComplete="off"
                                defaultValue={user?.username}
                            />
                        </Field>
                        <Field
                            label="Email"
                            htmlFor="email"
                            error={errors.email}
                            className="flex-[1_1_240px]"
                        >
                            <TextInput
                                id="email"
                                name="email"
                                type="email"
                                required
                                autoComplete="off"
                                defaultValue={user?.email}
                            />
                        </Field>
                    </div>

                    <fieldset className="flex flex-col">
                        <legend className="mb-2 font-caps text-xs font-semibold tracking-[0.14em] text-copper uppercase">
                            Role
                        </legend>
                        {ROLES.map((option) => (
                            <label
                                key={option.value}
                                className="flex cursor-pointer items-start gap-3.5 border-b border-line py-3"
                            >
                                <input
                                    type="radio"
                                    name="role"
                                    value={option.value}
                                    checked={role === option.value}
                                    onChange={() => setRole(option.value)}
                                    className="mt-1.5 size-4 accent-copper"
                                />
                                <span className="flex flex-col">
                                    <span
                                        className={cn(
                                            'font-display font-semibold',
                                            role === option.value &&
                                                'text-copper-bright',
                                        )}
                                    >
                                        {option.label}
                                    </span>
                                    <span className="text-dust">
                                        {option.description}
                                    </span>
                                </span>
                            </label>
                        ))}
                        {errors.role && (
                            <p className="mt-2 text-status-overdue">
                                {errors.role}
                            </p>
                        )}
                    </fieldset>

                    <div className="flex flex-wrap gap-5">
                        <Field
                            label="Password"
                            htmlFor="password"
                            error={errors.password}
                            className="flex-[1_1_220px]"
                        >
                            <TextInput
                                id="password"
                                name="password"
                                type="password"
                                required={!user}
                                autoComplete="new-password"
                            />
                        </Field>
                        <Field
                            label="Type it again"
                            htmlFor="password_confirmation"
                            className="flex-[1_1_220px]"
                        >
                            <TextInput
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                required={!user}
                                autoComplete="new-password"
                            />
                        </Field>
                    </div>
                    {user && (
                        <p className="-mt-2 text-dust">
                            Leave both empty to keep their current password.
                        </p>
                    )}

                    <FormActions>
                        <Button
                            type="submit"
                            size="lg"
                            disabled={processing}
                            className={capsButton}
                        >
                            {user ? 'Save changes' : 'Create user'}
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
