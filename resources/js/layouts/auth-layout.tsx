import { I18nProvider } from '@sematico/laravel-inertia-i18n-react';
import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';
import LocaleSwitcher from '@/components/locale-switcher';

export default function AuthLayout({
    title = '',
    description = '',
    children,
}: {
    title?: string;
    description?: string;
    children: React.ReactNode;
}) {
    return (
        <>
            <div className="absolute top-4 right-4">
                <LocaleSwitcher />
            </div>
            <I18nProvider>
                <AuthLayoutTemplate title={title} description={description}>
                    {children}
                </AuthLayoutTemplate>
            </I18nProvider>
        </>
    );
}
