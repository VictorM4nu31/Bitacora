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
        <I18nProvider>
            <>
                <div className="absolute top-4 right-4">
                    <LocaleSwitcher />
                </div>
                <AuthLayoutTemplate title={title} description={description}>
                    {children}
                </AuthLayoutTemplate>
            </>
        </I18nProvider>
    );
}
