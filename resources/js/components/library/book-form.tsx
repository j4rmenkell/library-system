import { Form, Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { ChangeEvent } from 'react';
import { BookCover } from '@/components/library/book-cover';
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
import { LIMITS } from '@/lib/limits';

// Shared by books/create and books/edit
export function BookForm({
    book,
    categories,
}: {
    book?: Book;
    categories: Category[];
}) {
    const action = book ? update.form(book.id) : store.form();

    // a local preview of the picture before it is uploaded
    const [preview, setPreview] = useState<string | null>(null);

    // free the preview's memory when it is replaced or the page closes
    useEffect(() => {
        return () => {
            if (preview) {
                URL.revokeObjectURL(preview);
            }
        };
    }, [preview]);

    const pickCover = (event: ChangeEvent<HTMLInputElement>) => {
        const file = event.target.files?.[0];
        setPreview(file ? URL.createObjectURL(file) : null);
    };

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
                            maxLength={LIMITS.title}
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
                            maxLength={LIMITS.author}
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
                                maxLength={LIMITS.isbn}
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
                                min={-3000}
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
                    <Field
                        label="Cover image"
                        htmlFor="cover"
                        optional
                        error={errors.cover}
                        help={
                            book?.cover_url
                                ? 'JPG, PNG or WebP, up to 2 MB. Pick one to replace the current cover.'
                                : 'JPG, PNG or WebP, up to 2 MB.'
                        }
                    >
                        <div className="flex items-start gap-4">
                            <div className="w-20 shrink-0">
                                {preview ? (
                                    <img
                                        src={preview}
                                        alt="Preview of the new cover"
                                        className="aspect-[2/3] w-full border border-line-strong object-cover"
                                    />
                                ) : (
                                    <BookCover
                                        size="xs"
                                        book={{
                                            id: book?.id ?? 0,
                                            title: book?.title ?? 'New book',
                                            author: book?.author ?? '',
                                            cover_url: book?.cover_url ?? null,
                                        }}
                                    />
                                )}
                            </div>
                            <TextInput
                                id="cover"
                                name="cover"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                onChange={pickCover}
                                aria-invalid={errors.cover ? true : undefined}
                                className="h-auto py-2 text-base file:mr-3 file:border-0 file:bg-transparent file:font-caps file:text-xs file:font-semibold file:tracking-[0.1em] file:text-copper-bright file:uppercase"
                            />
                        </div>
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
