import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { index, update, destroy } from '@/routes/equipment';
import { show as serviceOrderShow } from '@/routes/service-orders';

type EquipmentDetail = {
    id: number;
    name: string;
    brand: string | null;
    model: string | null;
    serial_number: string | null;
    type: string;
    notes: string | null;
    customer: { id: number; name: string } | null;
};

type HistoryItem = {
    id: number;
    status: string;
    statusLabel: string;
    customer_name: string | null;
    report_status: string | null;
    date: string;
};

type MaintenanceItem = {
    id: number;
    interval_days: number;
    next_due_at: string | null;
    enabled: boolean;
};

type PageProps = {
    equipment: EquipmentDetail;
    history: HistoryItem[];
    maintenance: MaintenanceItem[];
    maintenanceUrl: string;
    completeMaintenanceUrl: string;
    customers: { id: number; name: string }[];
    types: { value: string; label: string }[];
};

export default function EquipmentShow() {
    const { t } = useTranslation();
    const {
        equipment,
        history,
        maintenance,
        maintenanceUrl,
        completeMaintenanceUrl,
        customers,
        types,
    } = usePage<PageProps>().props;

    const scheduleForm = useForm({ interval_days: '90' });

    function scheduleMaintenance(event: FormEvent) {
        event.preventDefault();
        scheduleForm.post(maintenanceUrl, {
            preserveScroll: true,
            onSuccess: () => scheduleForm.reset(),
        });
    }

    function complete(item: MaintenanceItem) {
        const url = completeMaintenanceUrl.replace('__ID__', String(item.id));
        router.post(url, {}, { preserveScroll: true });
    }
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);

    const form = useForm({
        name: equipment.name,
        customer_id: String(equipment.customer?.id ?? ''),
        brand: equipment.brand ?? '',
        model: equipment.model ?? '',
        serial_number: equipment.serial_number ?? '',
        type: equipment.type,
        notes: equipment.notes ?? '',
    });

    function submitEdit(event: FormEvent) {
        event.preventDefault();

        form.put(update.url({ equipment: equipment.id }), {
            preserveScroll: true,
            onSuccess: () => {
                setEditOpen(false);
            },
        });
    }

    function confirmDelete() {
        router.delete(destroy.url({ equipment: equipment.id }));
    }

    const typeLabel =
        types.find((item) => item.value === equipment.type)?.label ??
        equipment.type;

    return (
        <>
            <Head title={`${t('Equipment')} - ${equipment.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Link
                    href={index.url()}
                    className="text-muted-foreground text-sm"
                >
                    ← {t('Back to equipment')}
                </Link>

                <div className="flex items-center justify-between">
                    <Heading
                        title={equipment.name}
                        description={
                            equipment.customer?.name ?? t('No customer')
                        }
                    />

                    <div className="flex gap-2">
                        <Dialog open={editOpen} onOpenChange={setEditOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline">{t('Edit')}</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>
                                        {t('Edit equipment')}
                                    </DialogTitle>
                                </DialogHeader>

                                <form
                                    onSubmit={submitEdit}
                                    className="space-y-4"
                                >
                                    <div className="grid gap-2">
                                        <Label htmlFor="customer">
                                            {t('Customer')} *
                                        </Label>
                                        <Select
                                            value={String(
                                                form.data.customer_id,
                                            )}
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
                                                        value={String(
                                                            customer.id,
                                                        )}
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
                                        <Label htmlFor="name">
                                            {t('Name')} *
                                        </Label>
                                        <Input
                                            id="name"
                                            value={form.data.name}
                                            onChange={(e) =>
                                                form.setData(
                                                    'name',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                        <InputError
                                            message={form.errors.name}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="type">
                                            {t('Type')} *
                                        </Label>
                                        <Select
                                            value={String(form.data.type)}
                                            onValueChange={(v) =>
                                                form.setData('type', v)
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue
                                                    placeholder={t(
                                                        'Select a type',
                                                    )}
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
                                        <InputError
                                            message={form.errors.type}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="brand">
                                            {t('Brand')}
                                        </Label>
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
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="model">
                                            {t('Model')}
                                        </Label>
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
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="notes">
                                            {t('Notes')}
                                        </Label>
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
                                    </div>

                                    <div className="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setEditOpen(false)}
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

                        <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                            <DialogTrigger asChild>
                                <Button variant="destructive">
                                    {t('Delete')}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>
                                        {t('Delete equipment')}
                                    </DialogTitle>
                                </DialogHeader>
                                <p className="text-muted-foreground text-sm">
                                    {t('This will permanently delete')} «
                                    {equipment.name}».
                                </p>
                                <div className="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        onClick={() => setDeleteOpen(false)}
                                    >
                                        {t('Cancel')}
                                    </Button>
                                    <Button
                                        variant="destructive"
                                        onClick={confirmDelete}
                                    >
                                        {t('Delete')}
                                    </Button>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Information')}</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-1 text-sm">
                        <p>
                            <span className="text-muted-foreground">
                                {t('Customer')}:
                            </span>{' '}
                            {equipment.customer?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Type')}:
                            </span>{' '}
                            {typeLabel}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Brand')}:
                            </span>{' '}
                            {equipment.brand ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Model')}:
                            </span>{' '}
                            {equipment.model ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Serial number')}:
                            </span>{' '}
                            {equipment.serial_number ?? '—'}
                        </p>
                        {equipment.notes && (
                            <p>
                                <span className="text-muted-foreground">
                                    {t('Notes')}:
                                </span>{' '}
                                {equipment.notes}
                            </p>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Scheduled maintenance')}</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <form
                            onSubmit={scheduleMaintenance}
                            className="flex items-end gap-2"
                        >
                            <div className="grid max-w-48 gap-1">
                                <label
                                    htmlFor="interval"
                                    className="text-muted-foreground text-sm"
                                >
                                    {t('Every (days)')}
                                </label>
                                <input
                                    id="interval"
                                    type="number"
                                    min={1}
                                    value={scheduleForm.data.interval_days}
                                    onChange={(e) =>
                                        scheduleForm.setData(
                                            'interval_days',
                                            e.target.value,
                                        )
                                    }
                                    className="border-input h-9 rounded-md border bg-transparent px-3 text-sm"
                                />
                            </div>
                            <Button
                                type="submit"
                                disabled={scheduleForm.processing}
                            >
                                {t('Schedule')}
                            </Button>
                        </form>

                        {maintenance.length === 0 ? (
                            <p className="text-muted-foreground text-sm">
                                {t(
                                    'No maintenance scheduled for this equipment.',
                                )}
                            </p>
                        ) : (
                            <ul className="space-y-2">
                                {maintenance.map((item) => (
                                    <li
                                        key={item.id}
                                        className="border-muted flex items-center justify-between rounded-lg border px-3 py-2 text-sm"
                                    >
                                        <div>
                                            <p className="font-medium">
                                                {item.next_due_at ??
                                                    t('No date')}{' '}
                                                — {t('every')}{' '}
                                                {item.interval_days} {t('days')}
                                            </p>
                                            <p className="text-muted-foreground text-xs">
                                                {item.enabled
                                                    ? t('Active')
                                                    : t('Paused')}
                                            </p>
                                        </div>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => complete(item)}
                                        >
                                            {t('Mark as done')}
                                        </Button>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Service history')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {history.length === 0 ? (
                            <p className="text-muted-foreground text-sm">
                                {t('This equipment has no services yet.')}
                            </p>
                        ) : (
                            <ul className="space-y-2">
                                {history.map((item) => (
                                    <li key={item.id}>
                                        <Link
                                            href={serviceOrderShow.url({
                                                service_order: item.id,
                                            })}
                                            className="hover:bg-muted flex items-center justify-between rounded-lg border px-3 py-2 text-sm transition-colors"
                                        >
                                            <div>
                                                <p className="font-medium">
                                                    {item.customer_name ??
                                                        t('No customer')}{' '}
                                                    — {item.date}
                                                </p>
                                                <p className="text-muted-foreground text-xs">
                                                    {item.report_status ===
                                                    'finalized'
                                                        ? t(
                                                              'With finalized report',
                                                          )
                                                        : item.report_status ===
                                                            'draft'
                                                          ? t('Report draft')
                                                          : t('No report')}
                                                </p>
                                            </div>
                                            <Badge variant="secondary">
                                                {item.statusLabel}
                                            </Badge>
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

EquipmentShow.layout = {
    breadcrumbs: [{ title: 'Equipment', href: index.url() }],
};
