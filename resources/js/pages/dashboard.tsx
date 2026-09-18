import { Head, Link } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { CalendarClock, ClipboardList, Users, Wrench } from 'lucide-react';
import { dashboard } from '@/routes';
import { index as customersIndex } from '@/routes/customers';
import { index as equipmentIndex } from '@/routes/equipment';
import { index as serviceOrdersIndex } from '@/routes/service-orders';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

const FLOW = ['01', '02', '03', '04'];

export default function Dashboard() {
    const { t } = useTranslation();

    return (
        <>
            <Head title={t('Dashboard')} />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="order-band paper-card rounded-[10px] bg-card px-6 py-5">
                    <p className="font-mono text-[11px] font-semibold tracking-[0.08em] text-muted-foreground uppercase">
                        {t('Today in the field')}
                    </p>
                    <h1 className="font-display text-2xl font-extrabold tracking-tight">
                        {t('Where does the work continue?')}
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        {t(
                            'Record a voice note, review the draft and share the report.',
                        )}
                    </p>
                </div>

                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <Link href={serviceOrdersIndex.url()}>
                        <Card className="h-full transition-transform hover:-translate-y-0.5">
                            <CardHeader>
                                <ClipboardList className="size-5 text-primary" />
                                <CardTitle>{t('Services')}</CardTitle>
                                <CardDescription>
                                    {t('Voice note → draft → finalized PDF')}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="font-mono text-xs text-muted-foreground tabular-nums">
                                {FLOW[0]} · {FLOW[1]}
                            </CardContent>
                        </Card>
                    </Link>
                    <Link href={equipmentIndex.url()}>
                        <Card className="h-full transition-transform hover:-translate-y-0.5">
                            <CardHeader>
                                <Wrench className="size-5 text-primary" />
                                <CardTitle>{t('Equipment')}</CardTitle>
                                <CardDescription>
                                    {t('History and scheduled maintenance')}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="font-mono text-xs text-muted-foreground tabular-nums">
                                {FLOW[2]}
                            </CardContent>
                        </Card>
                    </Link>
                    <Link href={customersIndex.url()}>
                        <Card className="h-full transition-transform hover:-translate-y-0.5">
                            <CardHeader>
                                <Users className="size-5 text-primary" />
                                <CardTitle>{t('Customers')}</CardTitle>
                                <CardDescription>
                                    {t('Share the report with each customer')}
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="font-mono text-xs text-muted-foreground tabular-nums">
                                {FLOW[3]}
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <Card>
                    <CardHeader>
                        <CalendarClock className="size-5 text-primary" />
                        <CardTitle>{t('Field routine')}</CardTitle>
                        <CardDescription>
                            {t(
                                'Start the service, record, review the draft, confirm and share.',
                            )}
                        </CardDescription>
                    </CardHeader>
                </Card>
            </div>
        </>
    );
}

Dashboard.layout = {
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
    ],
};
