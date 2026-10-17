import type { User } from "./auth";

export type BookStatus = "available" | "borrowed";

export type BorrowingStatus = "pending" | "borrowed" | "returned" | "rejected";

export type Category = {
    id: number;
    name: string;
    description: string | null;
    books_count?: number;
    created_at: string;
    updated_at: string;
};

export type Book = {
    id: number;
    category_id: number;
    title: string;
    author: string;
    isbn: string;
    publication_date: string | null;
    availability_status: BookStatus;
    category?: Category;
    created_at: string;
    updated_at: string;
};

export type Member = {
    id: number;
    user_id: number | null;
    name: string;
    contact_number: string;
    address: string;
    user?: User | null;
    active_borrowings_count?: number;
    created_at: string;
    updated_at: string;
};

export type Borrowing = {
    id: number;
    book_id: number;
    member_id: number;
    borrow_date: string | null;
    due_date: string | null;
    return_date: string | null;
    status: BorrowingStatus;
    book?: Book;
    member?: Member;
    created_at: string;
    updated_at: string;
};

export type ActivityLog = {
    id: number;
    user_id: number | null;
    action: string;
    user?: User | null;
    created_at: string;
    updated_at: string;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
};
