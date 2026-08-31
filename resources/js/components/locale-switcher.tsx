import { router, usePage } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { Button } from '@/components/ui/button';

type PageProps = {
    locale?: string;
    auth?: { user: { locale?: string } | null };
};

export default function LocaleSwitcher() {
    const { props } = usePage<PageProps>();
    const { locale, setLocale } = useTranslation();
    const authenticated = !!props.auth?.user;
    const current = props.auth?.user?.locale ?? props.locale ?? locale ?? 'es';

    const switchLocale = (next: string) => {
        if (next === current) return;
        if (authenticated) {
            router.patch('/locale', { locale: next }, { preserveScroll: true });
        } else {
            setLocale(next);
        }
    };

    return (
        <div className="flex items-center gap-1">
            <Button
                variant={current === 'es' ? 'secondary' : 'ghost'}
                size="sm"
                type="button"
                onClick={() => switchLocale('es')}
            >
                ES
            </Button>
            <Button
                variant={current === 'en' ? 'secondary' : 'ghost'}
                size="sm"
                type="button"
                onClick={() => switchLocale('en')}
            >
                EN
            </Button>
        </div>
    );
}
