import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
    customers: { data: CustomerItem[] };
};

export default function Customers() {
    const { t } = useTranslation();
    const { customers } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

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

                    <Dialog open={open} onOpenChange={setOpen}>
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
                    </Dialog>
                </div>

                <div className="rounded-xl border">
                    {customers.data.length === 0 ? (
                        <div className="text-muted-foreground p-8 text-center text-sm">
                            {t('No customers yet. Create the first one.')}
                        </div>
                    ) : (
                        customers.data.map((customer) => (
                            <Link
                                key={customer.id}
                                href={show.url({ customer: customer.id })}
                                className="hover:bg-muted dark:hover:bg-muted/40 grid border-b px-4 py-3 transition-colors last:border-b-0"
                            >
                                <div className="min-w-0">
                                    <p className="truncate font-medium">
                                        {customer.name}
                                    </p>
                                    <p className="text-muted-foreground text-sm">
                                        {customer.phone ??
                                            customer.email ??
                                            t('No contact')}
                                    </p>
                                </div>
                            </Link>
                        ))
                    )}
                </div>
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
