import { Form, Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { ConfirmDialog } from '@/components/library/confirm-dialog';
import {
    capsButton,
    Field,
    TextArea,
    TextInput,
} from '@/components/library/form-controls';
import { LedgerTable, SrOnly, Td, Th } from '@/components/library/ledger-table';
import { PageBody, PageHeader } from '@/components/library/page-header';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { destroy, index, store, update } from '@/routes/categories';
import type { Category } from '@/types';

type Editing = Category | 'new' | null;

function CategoryDialog({
    editing,
    onClose,
}: {
    editing: Editing;
    onClose: () => void;
}) {
    const category = editing === 'new' ? null : editing;
    const action = category ? update.form(category.id) : store.form();

    return (
        <Dialog
            open={editing !== null}
            onOpenChange={(open) => !open && onClose()}
        >
            <DialogContent className="border-copper bg-panel">
                <DialogTitle className="font-display text-xl">
                    {category ? 'Edit category' : 'Add a category'}
                </DialogTitle>
                <DialogDescription className="text-base text-dust">
                    Readers use categories to filter the catalog.
                </DialogDescription>
                <Form
                    {...action}
                    key={category?.id ?? 'new'}
                    onSuccess={onClose}
                    disableWhileProcessing
                    className="flex flex-col gap-5"
                >
                    {({ errors, processing }) => (
                        <>
                            <Field
                                label="Name"
                                htmlFor="name"
                                error={errors.name}
                            >
                                <TextInput
                                    id="name"
                                    name="name"
                                    required
                                    autoFocus
                                    defaultValue={category?.name}
                                />
                            </Field>
                            <Field
                                label="Description"
                                htmlFor="description"
                                optional
                                error={errors.description}
                            >
                                <TextArea
                                    id="description"
                                    name="description"
                                    rows={3}
                                    defaultValue={category?.description ?? ''}
                                />
                            </Field>
                            <div className="flex justify-end gap-3">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    onClick={onClose}
                                    className={capsButton}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing}
                                    className={capsButton}
                                >
                                    Save category
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </DialogContent>
        </Dialog>
    );
}

export default function CategoriesIndex({
    categories,
}: {
    categories: Category[];
}) {
    const [editing, setEditing] = useState<Editing>(null);
    const [deleting, setDeleting] = useState<Category | null>(null);
    const total = categories.reduce((sum, c) => sum + (c.books_count ?? 0), 0);

    return (
        <>
            <Head title="Categories" />
            <PageBody>
                <PageHeader
                    title="Categories"
                    description="A category can only be deleted once it holds no books."
                    actions={
                        <Button
                            onClick={() => setEditing('new')}
                            className={capsButton}
                        >
                            Add a category
                        </Button>
                    }
                />

                <LedgerTable minWidth={620}>
                    <thead>
                        <tr>
                            <Th>Name</Th>
                            <Th>Description</Th>
                            <Th align="right">Books</Th>
                            <Th>
                                <SrOnly>Actions</SrOnly>
                            </Th>
                        </tr>
                    </thead>
                    <tbody>
                        {categories.map((category) => (
                            <tr key={category.id}>
                                <Td className="font-display font-semibold whitespace-nowrap">
                                    {category.name}
                                </Td>
                                <Td className="text-bone-soft">
                                    {category.description}
                                </Td>
                                <Td align="right" className="tabular-nums">
                                    {category.books_count ?? 0}
                                </Td>
                                <Td align="right" className="whitespace-nowrap">
                                    <button
                                        type="button"
                                        onClick={() => setEditing(category)}
                                        className="text-copper-bright underline underline-offset-3"
                                    >
                                        Edit
                                    </button>
                                    {(category.books_count ?? 0) === 0 && (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setDeleting(category)
                                            }
                                            className="ml-4 text-status-overdue"
                                        >
                                            Delete
                                        </button>
                                    )}
                                </Td>
                            </tr>
                        ))}
                    </tbody>
                    <tfoot>
                        <tr>
                            <td
                                colSpan={2}
                                className="pt-3 font-caps text-[11px] font-semibold tracking-[0.14em] text-faded uppercase"
                            >
                                Total
                            </td>
                            <td className="px-3 pt-3 text-right font-semibold tabular-nums">
                                {total}
                            </td>
                            <td />
                        </tr>
                    </tfoot>
                </LedgerTable>
            </PageBody>

            <CategoryDialog
                editing={editing}
                onClose={() => setEditing(null)}
            />

            <ConfirmDialog
                open={deleting !== null}
                onOpenChange={(open) => !open && setDeleting(null)}
                title={`Delete ${deleting?.name}?`}
                description="This category has no books, so nothing else changes."
                confirmLabel="Delete category"
                onConfirm={() =>
                    deleting &&
                    router.delete(destroy.url(deleting.id), {
                        onFinish: () => setDeleting(null),
                    })
                }
            />
        </>
    );
}

CategoriesIndex.layout = {
    breadcrumbs: [{ title: 'Categories', href: index() }],
};
