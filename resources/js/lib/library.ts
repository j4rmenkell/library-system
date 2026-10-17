import type { Borrowing, DisplayStatus } from '@/types';

// Loan rules, mirrored from config/library.php
export const MAX_ACTIVE_LOANS = 3;
export const LOAN_DAYS = 14;

const DAY_MS = 24 * 60 * 60 * 1000;

function startOfDay(date: Date): number {
    return new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate(),
    ).getTime();
}

// Laravel sends dates as "2026-10-24" or "2026-10-24T00:00:00.000000Z"
function parseDate(value: string): Date {
    const [year, month, day] = value.slice(0, 10).split('-').map(Number);

    return new Date(year, month - 1, day);
}

export function daysLate(borrowing: Borrowing, today = new Date()): number {
    if (borrowing.status !== 'borrowed' || !borrowing.due_date) {
        return 0;
    }

    const diff = startOfDay(today) - startOfDay(parseDate(borrowing.due_date));

    return Math.max(0, Math.round(diff / DAY_MS));
}

export function isOverdue(borrowing: Borrowing, today = new Date()): boolean {
    return daysLate(borrowing, today) > 0;
}

export function displayStatus(borrowing: Borrowing): DisplayStatus {
    return isOverdue(borrowing) ? 'overdue' : borrowing.status;
}

export function formatDate(value: string | null, withYear = true): string {
    if (!value) {
        return '—';
    }

    return parseDate(value).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        ...(withYear ? { year: 'numeric' } : {}),
    });
}

export function formatDateTime(value: string): string {
    const date = new Date(value);
    const isToday = startOfDay(date) === startOfDay(new Date());
    const time = date.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
    });

    return isToday ? `Today, ${time}` : `${formatDate(value, false)}, ${time}`;
}

export function initials(name: string): string {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');
}
