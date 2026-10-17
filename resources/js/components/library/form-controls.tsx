import type { ComponentProps, ReactNode } from 'react';
import { cn } from '@/lib/utils';

const control =
    'w-full rounded-sm border border-input bg-panel px-3 text-lg text-bone placeholder:text-faded outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/40 aria-invalid:border-destructive';

// Label in copper caps, the control, then help text or the error
export function Field({
    label,
    htmlFor,
    optional = false,
    help,
    error,
    className,
    children,
}: {
    label: string;
    htmlFor: string;
    optional?: boolean;
    help?: ReactNode;
    error?: string;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={cn('flex flex-col gap-2', className)}>
            <label
                htmlFor={htmlFor}
                className="font-caps text-xs font-semibold tracking-[0.14em] text-copper uppercase"
            >
                {label}
                {optional && <span className="text-faded"> · optional</span>}
            </label>
            {children}
            {error ? (
                <p className="text-base text-status-overdue">{error}</p>
            ) : (
                help && <p className="text-base text-dust">{help}</p>
            )}
        </div>
    );
}

export function TextInput({ className, ...props }: ComponentProps<'input'>) {
    return <input className={cn(control, 'h-11', className)} {...props} />;
}

export function TextArea({ className, ...props }: ComponentProps<'textarea'>) {
    return (
        <textarea
            className={cn(control, 'resize-y py-2.5', className)}
            {...props}
        />
    );
}

// A plain <select>, so Inertia's <Form> picks its value up by name
export function SelectInput({ className, ...props }: ComponentProps<'select'>) {
    return <select className={cn(control, 'h-11', className)} {...props} />;
}

// The row of buttons at the bottom of a form
export function FormActions({ children }: { children: ReactNode }) {
    return (
        <div className="flex flex-wrap items-center gap-5 border-t border-line pt-6">
            {children}
        </div>
    );
}

// Copper button text: small, tracked capitals
export const capsButton =
    'font-caps text-xs font-semibold tracking-[0.1em] uppercase';
