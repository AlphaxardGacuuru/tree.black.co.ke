import { usePage } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { FloatingUserAvatar } from '@/components/floating-user-avatar';
import { SidebarProvider } from '@/components/ui/sidebar';
import type { AppVariant } from '@/types';

type Props = {
    children: ReactNode;
    variant?: AppVariant;
};

export function AppShell({ children, variant = 'sidebar' }: Props) {
    const { sidebarOpen, auth } = usePage().props as {
        sidebarOpen: boolean;
        auth?: { user?: unknown };
    };

    const shouldRenderFloatingAvatar = Boolean(auth?.user);

    if (variant === 'header') {
        return (
            <div className="flex min-h-screen w-full flex-col">
                {children}
                {shouldRenderFloatingAvatar && <FloatingUserAvatar />}
            </div>
        );
    }

    return (
        <SidebarProvider defaultOpen={sidebarOpen}>
            {children}
            {/* {shouldRenderFloatingAvatar && <FloatingUserAvatar />} */}
        </SidebarProvider>
    );
}
