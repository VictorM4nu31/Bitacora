import { usePage } from '@inertiajs/react';

type AuthorizationPageProps = {
    auth?: {
        permissions?: string[];
    };
};

export function useCan(): (permission: string) => boolean {
    const { auth } = usePage<AuthorizationPageProps>().props;
    const permissions = auth?.permissions ?? [];

    return (permission: string) => permissions.includes(permission);
}
