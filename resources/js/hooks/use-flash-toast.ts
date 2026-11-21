import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';

type Flash = {
    success?: string | null;
    error?: string | null;
};

// after every visit, show the message the controller sent with ->with('success'/'error')
export function useFlashToast(): void {
    useEffect(() => {
        return router.on('success', (event) => {
            const flash = event.detail.page.props.flash as Flash | undefined;

            if (flash?.success) {
                toast.success(flash.success);
            }

            if (flash?.error) {
                toast.error(flash.error);
            }
        });
    }, []);
}
