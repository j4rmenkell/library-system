import type { ReactNode } from 'react';
import { capsButton } from '@/components/library/form-controls';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';

// Asks before a destructive action, e.g. deleting a book
export function ConfirmDialog({
    open,
    onOpenChange,
    title,
    description,
    confirmLabel,
    onConfirm,
    processing = false,
}: {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    title: string;
    description: ReactNode;
    confirmLabel: string;
    onConfirm: () => void;
    processing?: boolean;
}) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="border-copper bg-panel">
                <DialogTitle className="font-display text-xl">
                    {title}
                </DialogTitle>
                <DialogDescription className="text-base text-dust">
                    {description}
                </DialogDescription>
                <DialogFooter className="gap-3">
                    <Button
                        variant="ghost"
                        onClick={() => onOpenChange(false)}
                        className={capsButton}
                    >
                        Keep it
                    </Button>
                    <Button
                        variant="destructive"
                        onClick={onConfirm}
                        disabled={processing}
                        className={capsButton}
                    >
                        {confirmLabel}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
