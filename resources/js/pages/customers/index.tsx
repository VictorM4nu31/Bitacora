import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { useCan } from '@/hooks/use-authorization';
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
import { index, store, show } from '@/routes/customers';

type CustomerItem = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    notes: string | null;
};

type PageProps = {
    customers: {
        data: CustomerItem[];
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    filters: { search: string };
};

export default function Customers() {
    const { t } = useTranslation();
    const { customers } = usePage<PageProps>().props;
    const { filters } = usePage<PageProps>().props;
    const can = useCan();
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState(filters.search);

    const form = useForm({
        name: '',
        phone: '',
        email: '',
        address: '',
        notes: '',
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

    function searchCustomers(event: FormEvent) {
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

    return (
        <>
            <Head title={t('Customers')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={t('Customers')}
                        description={t(
                            'Your company customers and their equipment',
                        )}
                    />

                     {can('create customers') && <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>{t('New customer')}</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('New customer')}</DialogTitle>
                            </DialogHeader>

                            <form onSubmit={submit} className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">{t('Name')} *</Label>
                                    <Input
                                        id="name"
                                        value={form.data.name}
                                        onChange={(e) =>
                                            form.setData('name', e.target.value)
                                        }
                                        placeholder="Nombre del cliente"
                                        autoFocus
                                    />
                                    <InputError message={form.errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">{t('Phone')}</Label>
                                    <Input
                                        id="phone"
                                        value={form.data.phone}
                                        onChange={(e) =>
                                            form.setData(
                                                'phone',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="55 1234 5678"
                                    />
                                    <InputError message={form.errors.phone} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">{t('Email')}</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={form.data.email}
                                        onChange={(e) =>
                                            form.setData(
                                                'email',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="cliente@correo.com"
                                    />
                                    <InputError message={form.errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="address">
                                        {t('Address')}
                                    </Label>
                                    <Input
                                        id="address"
                                        value={form.data.address}
                                        onChange={(e) =>
                                            form.setData(
                                                'address',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError message={form.errors.address} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="notes">{t('Notes')}</Label>
                                    <textarea
                                        id="notes"
                                        value={form.data.notes}
                                        onChange={(e) =>
                                            form.setData(
                                                'notes',
                                                e.target.value,
                                            )
                                        }
                                        className="border-input min-h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm"
                                    />
                                    <InputError message={form.errors.notes} />
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
                                        {t('Save')}
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                     </Dialog>}
                </div>

                <form onSubmit={searchCustomers} className="flex gap-2">
                    <Input
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={t('Search customers')}
                        aria-label={t('Search customers')}
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
                    {customers.data.length === 0 ? (
                        <div className="flex flex-col items-center gap-2 p-10 text-center">
                            <p className="font-display text-lg font-bold">
                                {t('No customers yet. Create the first one.')}
                            </p>
                            <p className="max-w-sm text-sm text-muted-foreground">
                                {t(
                                    'Customers group equipment and service history.',
                                )}
                            </p>
                        </div>
                    ) : (
                        customers.data.map((customer) => (
                            <Link
                                key={customer.id}
                                href={show.url({ customer: customer.id })}
                                className="grid border-b border-line border-l-4 border-l-transparent px-4 py-4 transition-colors last:border-b-0 hover:border-l-primary hover:bg-accent/50"
                            >
                                <div className="min-w-0">
                                    <p className="font-display truncate text-lg font-bold tracking-tight">
                                        {customer.name}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {customer.phone ??
                                            customer.email ??
                                            t('No contact')}
                                    </p>
                                </div>
                            </Link>
                        ))
                    )}
                </div>

                {(customers.prev_page_url || customers.next_page_url) && (
                    <div className="flex justify-between">
                        {customers.prev_page_url ? (
                            <Button asChild variant="outline" size="sm">
                                <Link href={customers.prev_page_url} preserveState preserveScroll>
                                    {t('Previous')}
                                </Link>
                            </Button>
                        ) : <span />}
                        {customers.next_page_url && (
                            <Button asChild variant="outline" size="sm">
                                <Link href={customers.next_page_url} preserveState preserveScroll>
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

Customers.layout = {
    breadcrumbs: [
        {
            title: 'Customers',
            href: index.url(),
        },
    ],
};
