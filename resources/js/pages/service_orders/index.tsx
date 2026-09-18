import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { useCan } from '@/hooks/use-authorization';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index, store, show } from '@/routes/service-orders';

type OrderItem = {
    id: number;
    status: string;
    customer: { id: number; name: string } | null;
    equipment: { id: number; name: string } | null;
    technician: { id: number; name: string } | null;
};

type Option = {
    value: string;
    label: string;
};

type PageProps = {
    orders: {
        data: OrderItem[];
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    statuses: Option[];
    customers: { id: number; name: string }[];
    equipment: { id: number; name: string; customer_id: number }[];
    filters: { search: string };
};

const STATUS_VARIANTS: Record<
    string,
    'witness' | 'info' | 'success' | 'secondary'
> = {
    pending: 'witness',
    in_progress: 'info',
    completed: 'success',
    cancelled: 'secondary',
};

const STATUS_BAND: Record<string, string> = {
    pending: 'border-l-witness',
    in_progress: 'border-l-info',
    completed: 'border-l-success',
    cancelled: 'border-l-line',
};

export default function ServiceOrders() {
    const { t } = useTranslation();
    const { orders, statuses, customers, equipment, filters } =
        usePage<PageProps>().props;
    const can = useCan();
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState(filters.search);

    const form = useForm({
        customer_id: '',
        equipment_id: '',
        scheduled_at: '',
    });

    function submit(event: FormEvent) {
        event.preventDefault();

        form.post(store.url(), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setOpen(false);
            },
        });
    }

    function searchServices(event: FormEvent) {
        event.preventDefault();

        router.get(index.url(), { search: search.trim() }, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function clearSearch() {
        setSearch('');
        router.get(index.url(), {}, { preserveState: true, preserveScroll: true });
    }

    const statusLabel = (value: string) =>
        statuses.find((s) => s.value === value)?.label ?? value;

    return (
        <>
            <Head title={t('Services')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={t('Services')}
                        description={t('Your company service orders')}
                    />

                     {can('create services') && <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>{t('New service')}</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('New service')}</DialogTitle>
                            </DialogHeader>

                            <form onSubmit={submit} className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="customer">
                                        {t('Customer')} *
                                    </Label>
                                    <Select
                                        value={String(form.data.customer_id)}
                                        onValueChange={(v) => {
                                            form.setData('equipment_id', '');
                                            form.setData('customer_id', v);
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={t(
                                                    'Select a customer',
                                                )}
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {customers.map((customer) => (
                                                <SelectItem
                                                    key={customer.id}
                                                    value={String(customer.id)}
                                                >
                                                    {customer.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        message={form.errors.customer_id}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="equipment">
                                        {t('Equipment')}
                                    </Label>
                                    <Select
                                        value={String(form.data.equipment_id)}
                                        onValueChange={(v) =>
                                            form.setData('equipment_id', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={t(
                                                    'Select equipment (optional)',
                                                )}
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                                {equipment
                                                    .filter((item) => item.customer_id === Number(form.data.customer_id))
                                                    .map((item) => (
                                                <SelectItem
                                                    key={item.id}
                                                    value={String(item.id)}
                                                >
                                                    {item.name}
                                                </SelectItem>
                                                    ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError
                                        message={form.errors.equipment_id}
                                    />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="scheduled_at">
                                        {t('Scheduled date')}
                                    </Label>
                                    <Input
                                        id="scheduled_at"
                                        type="datetime-local"
                                        value={form.data.scheduled_at}
                                        onChange={(e) =>
                                            form.setData(
                                                'scheduled_at',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.scheduled_at}
                                    />
                                </div>

                                <div className="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setOpen(false)}
                                    >
                                        {t('Cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={form.processing}
                                    >
                                        {t('Create')}
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                     </Dialog>}
                 </div>

                <form onSubmit={searchServices} className="flex gap-2">
                    <Input
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={t('Search services')}
                        aria-label={t('Search services')}
                    />
                    <Button type="submit" variant="outline">
                        {t('Search')}
                    </Button>
                    {filters.search && (
                        <Button type="button" variant="ghost" onClick={clearSearch}>
                            {t('Clear')}
                        </Button>
                    )}
                </form>

                 <div className="rounded-[10px] border-[1.5px] border-line bg-card">
                    {orders.data.length === 0 ? (
                        <div className="flex flex-col items-center gap-3 p-10 text-center">
                            <svg
                                viewBox="0 0 64 64"
                                fill="none"
                                className="h-14 w-14 text-muted-foreground"
                                aria-hidden="true"
                            >
                                <rect
                                    x="14"
                                    y="8"
                                    width="36"
                                    height="48"
                                    rx="4"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                />
                                <path
                                    d="M22 22l4 4 8-8M22 34l4 4 8-8M22 46h20"
                                    stroke="currentColor"
                                    strokeWidth="2"
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                />
                            </svg>
                            <p className="font-display text-lg font-bold">
                                {t('No services yet. Create the first one.')}
                            </p>
                            <p className="max-w-sm text-sm text-muted-foreground">
                                {t(
                                    'Every service is a logbook page: customer, equipment and voice note.',
                                )}
                            </p>
                        </div>
                    ) : (
                        orders.data.map((order) => (
                            <Link
                                key={order.id}
                                href={show.url({ service_order: order.id })}
                                className={`grid border-b border-line border-l-4 px-4 py-4 transition-colors last:border-b-0 hover:bg-accent/50 ${STATUS_BAND[order.status] ?? 'border-l-line'}`}
                            >
                                <div className="grid grid-cols-1 items-center gap-2 sm:grid-cols-[1fr_auto]">
                                    <div className="min-w-0">
                                        <p className="font-display truncate text-lg font-bold tracking-tight">
                                            {order.customer?.name ??
                                                t('No customer')}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {order.equipment?.name ??
                                                t('No equipment')}
                                            {order.technician
                                                ? ` · ${order.technician.name}`
                                                : ''}
                                        </p>
                                    </div>
                                    <Badge
                                        variant={
                                            STATUS_VARIANTS[order.status] ??
                                            'secondary'
                                        }
                                    >
                                        {statusLabel(order.status)}
                                    </Badge>
                                </div>
                            </Link>
                        ))
                     )}
                 </div>

                {(orders.prev_page_url || orders.next_page_url) && (
                    <div className="flex justify-between">
                        {orders.prev_page_url ? (
                            <Button asChild variant="outline" size="sm">
                                <Link href={orders.prev_page_url} preserveState preserveScroll>
                                    {t('Previous')}
                                </Link>
                            </Button>
                        ) : <span />}
                        {orders.next_page_url && (
                            <Button asChild variant="outline" size="sm">
                                <Link href={orders.next_page_url} preserveState preserveScroll>
                                    {t('Next')}
                                </Link>
                            </Button>
                        )}
                    </div>
                )}
            </div>
        </>
    );
}

ServiceOrders.layout = {
    breadcrumbs: [
        {
            title: 'Services',
            href: index.url(),
        },
    ],
};
