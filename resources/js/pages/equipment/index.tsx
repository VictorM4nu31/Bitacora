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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index, store, show } from '@/routes/equipment';

type EquipmentItem = {
    id: number;
    name: string;
    brand: string | null;
    model: string | null;
    serial_number: string | null;
    type: string;
    customer: { id: number; name: string } | null;
};

type Option = {
    value: string;
    label: string;
};

type CustomerOption = {
    id: number;
    name: string;
};

type PageProps = {
    equipment: {
        data: EquipmentItem[];
        prev_page_url: string | null;
        next_page_url: string | null;
    };
    customers: CustomerOption[];
    types: Option[];
    filters: { search: string };
};

export default function Equipment() {
    const { t } = useTranslation();
    const { equipment, customers, types, filters } = usePage<PageProps>().props;
    const can = useCan();
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState(filters.search);

    const form = useForm({
        name: '',
        customer_id: '',
        brand: '',
        model: '',
        serial_number: '',
        type: '',
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

    function searchEquipment(event: FormEvent) {
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
            <Head title={t('Equipment')} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title={t('Equipment')}
                        description={t('Your customers equipment inventory')}
                    />

                     {can('create equipment') && <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>{t('New equipment')}</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('New equipment')}</DialogTitle>
                            </DialogHeader>

                            <form onSubmit={submit} className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="customer">
                                        {t('Customer')} *
                                    </Label>
                                    <Select
                                        value={String(form.data.customer_id)}
                                        onValueChange={(v) =>
                                            form.setData('customer_id', v)
                                        }
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
                                    <Label htmlFor="name">{t('Name')} *</Label>
                                    <Input
                                        id="name"
                                        value={form.data.name}
                                        onChange={(e) =>
                                            form.setData('name', e.target.value)
                                        }
                                        placeholder={t(
                                            'E.g. Mini split 1.5 ton',
                                        )}
                                        autoFocus
                                    />
                                    <InputError message={form.errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="type">{t('Type')} *</Label>
                                    <Select
                                        value={String(form.data.type)}
                                        onValueChange={(v) =>
                                            form.setData('type', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={t('Select a type')}
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {types.map((type) => (
                                                <SelectItem
                                                    key={type.value}
                                                    value={type.value}
                                                >
                                                    {type.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.type} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="brand">{t('Brand')}</Label>
                                    <Input
                                        id="brand"
                                        value={form.data.brand}
                                        onChange={(e) =>
                                            form.setData(
                                                'brand',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError message={form.errors.brand} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="model">{t('Model')}</Label>
                                    <Input
                                        id="model"
                                        value={form.data.model}
                                        onChange={(e) =>
                                            form.setData(
                                                'model',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError message={form.errors.model} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="serial">
                                        {t('Serial number')}
                                    </Label>
                                    <Input
                                        id="serial"
                                        value={form.data.serial_number}
                                        onChange={(e) =>
                                            form.setData(
                                                'serial_number',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.serial_number}
                                    />
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

                <form onSubmit={searchEquipment} className="flex gap-2">
                    <Input
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={t('Search equipment')}
                        aria-label={t('Search equipment')}
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

                <div className="rounded-xl border">
                    {equipment.data.length === 0 ? (
                        <div className="text-muted-foreground p-8 text-center text-sm">
                            {t('No equipment yet. Create the first one.')}
                        </div>
                    ) : (
                        equipment.data.map((item) => (
                            <Link
                                key={item.id}
                                href={show.url({ equipment: item.id })}
                                className="hover:bg-muted dark:hover:bg-muted/40 grid border-b px-4 py-3 transition-colors last:border-b-0"
                            >
                                <div className="min-w-0">
                                    <p className="truncate font-medium">
                                        {item.name}
                                    </p>
                                    <p className="text-muted-foreground text-sm">
                                        {item.customer?.name ??
                                            t('No customer')}
                                        {item.model
                                            ? ` · ${item.brand ?? ''} ${item.model}`
                                            : ''}
                                    </p>
                                </div>
                            </Link>
                        ))
                    )}
                </div>

                {(equipment.prev_page_url || equipment.next_page_url) && (
                    <div className="flex justify-between">
                        {equipment.prev_page_url ? (
                            <Button asChild variant="outline" size="sm">
                                <Link href={equipment.prev_page_url} preserveState preserveScroll>
                                    {t('Previous')}
                                </Link>
                            </Button>
                        ) : <span />}
                        {equipment.next_page_url && (
                            <Button asChild variant="outline" size="sm">
                                <Link href={equipment.next_page_url} preserveState preserveScroll>
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

Equipment.layout = {
    breadcrumbs: [
        {
            title: 'Equipment',
            href: index.url(),
        },
    ],
};
