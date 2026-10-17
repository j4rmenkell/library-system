import { Form, Link } from '@inertiajs/react';
import {
    capsButton,
    Field,
    FormActions,
    SelectInput,
    TextInput,
} from '@/components/library/form-controls';
import { Button } from '@/components/ui/button';
import { index, store, update } from '@/routes/books';
import { index as categoriesIndex } from '@/routes/categories';
import type { Book, Category } from '@/types';

// Shared by books/create and books/edit
export function BookForm({
    book,
    categories,
}: {
    book?: Book;
    categories: Category[];
}) {
    const action = book ? update.form(book.id) : store.form();

    return (
        <Form
            {...action}
            disableWhileProcessing
            className="flex max-w-xl flex-col gap-5 border-t border-copper pt-6"
        >
            {({ errors, processing }) => (
                <>
                    <Field label="Title" htmlFor="title" error={errors.title}>
                        <TextInput
                            id="title"
                            name="title"
                            required
                            autoFocus
                            defaultValue={book?.title}
                        />
                    </Field>
                    <Field
                        label="Author"
                        htmlFor="author"
                        error={errors.author}
                    >
                        <TextInput
                            id="author"
                            name="author"
                            required
                            defaultValue={book?.author}
                        />
                    </Field>
                    <div className="flex flex-wrap gap-5">
                        <Field
                            label="ISBN"
                            htmlFor="isbn"
                            error={errors.isbn}
                            help="The 10 or 13 digits on the back cover."
                            className="flex-[1_1_240px]"
                        >
                            <TextInput
                                id="isbn"
                                name="isbn"
                                required
                                inputMode="numeric"
                                defaultValue={book?.isbn}
                                aria-invalid={errors.isbn ? true : undefined}
                            />
                        </Field>
                        <Field
                            label="Year published"
                            htmlFor="publication_year"
                            optional
                            error={errors.publication_year}
                            className="flex-[1_1_160px]"
                        >
                            <TextInput
                                id="publication_year"
                                name="publication_year"
                                type="number"
                                max={new Date().getFullYear()}
                                defaultValue={book?.publication_year ?? ''}
                            />
                        </Field>
                    </div>
                    <Field
                        label="Category"
                        htmlFor="category_id"
                        error={errors.category_id}
                        help={
                            <>
                                Not listed?{' '}
                                <Link
                                    href={categoriesIndex()}
                                    className="text-copper-bright underline-offset-4 hover:underline"
                                >
                                    Add a category
                                </Link>{' '}
                                first.
                            </>
                        }
                    >
                        <SelectInput
                            id="category_id"
                            name="category_id"
                            required
                            defaultValue={book?.category_id ?? ''}
                        >
                            <option value="" disabled>
                                Choose a category
                            </option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
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
                            {book ? 'Save changes' : 'Save book'}
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
