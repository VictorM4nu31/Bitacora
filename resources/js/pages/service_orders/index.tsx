import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
    orders: { data: OrderItem[] };
    statuses: Option[];
    customers: { id: number; name: string }[];
    equipment: { id: number; name: string; customer_id: number }[];
};

const STATUS_STYLES: Record<string, string> = {
    pending: 'text-amber-700 bg-amber-500/10 dark:text-amber-400',
    in_progress: 'text-blue-700 bg-blue-500/10 dark:text-blue-400',
    completed: 'text-green-700 bg-green-500/10 dark:text-green-400',
    cancelled: 'text-muted-foreground bg-muted',
};

export default function ServiceOrders() {
    const { orders, statuses, customers, equipment } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

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

    const statusLabel = (value: string) =>
        statuses.find((s) => s.value === value)?.label ?? value;

    return (
        <>
            <Head title="Servicios" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Servicios"
                        description="Las órdenes de servicio de tu empresa"
                    />

                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>Nuevo servicio</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Nuevo servicio</DialogTitle>
                            </DialogHeader>

                            <form onSubmit={submit} className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="customer">Cliente *</Label>
                                    <Select
                                        value={String(form.data.customer_id)}
                                        onValueChange={(v) => {
                                            form.setData('equipment_id', '');
                                            form.setData('customer_id', v);
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona un cliente" />
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
                                    <InputError message={form.errors.customer_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="equipment">Equipo</Label>
                                    <Select
                                        value={String(form.data.equipment_id)}
                                        onValueChange={(v) => form.setData('equipment_id', v)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Selecciona un equipo (opcional)" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {equipment.map((item) => (
                                                <SelectItem key={item.id} value={String(item.id)}>
                                                    {item.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.equipment_id} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="scheduled_at">Fecha programada</Label>
                                    <Input
                                        id="scheduled_at"
                                        type="datetime-local"
                                        value={form.data.scheduled_at}
                                        onChange={(e) => form.setData('scheduled_at', e.target.value)}
                                    />
                                    <InputError message={form.errors.scheduled_at} />
                                </div>

                                <div className="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setOpen(false)}
                                    >
                                        Cancelar
                                    </Button>
                                    <Button type="submit" disabled={form.processing}>
                                        Crear
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div className="rounded-xl border">
                    {orders.data.length === 0 ? (
                        <div className="text-muted-foreground p-8 text-center text-sm">
                            No hay servicios todavía. Crea el primero.
                        </div>
                    ) : (
                        orders.data.map((order) => (
                            <Link
                                key={order.id}
                                href={show.url({ service_order: order.id })}
                                className="hover:bg-muted grid border-b px-4 py-3 transition-colors last:border-b-0 dark:hover:bg-muted/40"
                            >
                                <div className="grid grid-cols-1 items-center gap-2 sm:grid-cols-[auto_1fr_auto]">
                                    <div className="min-w-0">
                                        <p className="truncate font-medium">
                                            {order.customer?.name ?? 'Sin cliente'}
                                        </p>
                                        <p className="text-muted-foreground text-sm">
                                            {order.equipment?.name ?? 'Sin equipo'}
                                            {order.technician ? ` · ${order.technician.name}` : ''}
                                        </p>
                                    </div>
                                    <Badge
                                        variant="secondary"
                                        className={STATUS_STYLES[order.status]}
                                    >
                                        {statusLabel(order.status)}
                                    </Badge>
                                </div>
                            </Link>
                        ))
                    )}
                </div>
            </div>
        </>
    );
}

ServiceOrders.layout = {
    breadcrumbs: [
        {
            title: 'Servicios',
            href: index.url(),
        },
    ],
};
