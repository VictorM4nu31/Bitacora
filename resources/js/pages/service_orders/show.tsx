import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import VoiceRecorder from '@/components/voice-recorder';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
import { index, update, destroy } from '@/routes/service-orders';

type OrderDetail = {
    id: number;
    status: string;
    scheduled_at: string | null;
    started_at: string | null;
    completed_at: string | null;
    customer: { id: number; name: string } | null;
    equipment: { id: number; name: string } | null;
    technician: { id: number; name: string } | null;
};

type AudioItem = {
    id: number;
    status: string;
    duration_ms: number | null;
};

type PageProps = {
    order: OrderDetail;
    statuses: { value: string; label: string }[];
    customers: { id: number; name: string }[];
    equipment: { id: number; name: string; customer_id: number }[];
    audioUrl: string;
    audioRecords: AudioItem[];
};

const STATUS_STYLES: Record<string, string> = {
    pending: 'text-amber-700 bg-amber-500/10 dark:text-amber-400',
    in_progress: 'text-blue-700 bg-blue-500/10 dark:text-blue-400',
    completed: 'text-green-700 bg-green-500/10 dark:text-green-400',
    cancelled: 'text-muted-foreground bg-muted',
};

export default function ServiceOrderShow() {
    const { order, statuses, customers, equipment, audioUrl, audioRecords } =
        usePage<PageProps>().props;
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);

    const form = useForm({
        customer_id: String(order.customer?.id ?? ''),
        equipment_id: order.equipment ? String(order.equipment.id) : '',
        scheduled_at: order.scheduled_at ?? '',
    });

    const statusLabel = statuses.find((s) => s.value === order.status)?.label ?? order.status;

    function submitEdit(event: FormEvent) {
        event.preventDefault();

        form.put(update.url({ service_order: order.id }), {
            preserveScroll: true,
            onSuccess: () => setEditOpen(false),
        });
    }

    function changeStatus(status: string) {
        router.put(update.url({ service_order: order.id }), { status });
    }

    function confirmDelete() {
        router.delete(destroy.url({ service_order: order.id }));
    }

    return (
        <>
            <Head title={`Servicio - ${order.customer?.name ?? 'Sin cliente'}`} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Link href={index.url()} className="text-muted-foreground text-sm">
                    ← Volver a servicios
                </Link>

                <div className="flex items-center justify-between">
                    <Heading
                        title={order.customer?.name ?? 'Sin cliente'}
                        description="Orden de servicio"
                    />

                    <div className="flex gap-2">
                        {order.status === 'pending' && (
                            <Button onClick={() => changeStatus('in_progress')}>
                                Iniciar servicio
                            </Button>
                        )}
                        {order.status === 'in_progress' && (
                            <Button onClick={() => changeStatus('completed')}>
                                Completar servicio
                            </Button>
                        )}

                        <Dialog open={editOpen} onOpenChange={setEditOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline">Editar</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Editar servicio</DialogTitle>
                                </DialogHeader>

                                <form onSubmit={submitEdit} className="space-y-4">
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
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="scheduled_at">Fecha programada</Label>
                                        <Input
                                            id="scheduled_at"
                                            type="datetime-local"
                                            value={form.data.scheduled_at}
                                            onChange={(e) =>
                                                form.setData('scheduled_at', e.target.value)
                                            }
                                        />
                                    </div>

                                    <div className="flex justify-end gap-2">
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setEditOpen(false)}
                                        >
                                            Cancelar
                                        </Button>
                                        <Button type="submit" disabled={form.processing}>
                                            Guardar
                                        </Button>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>

                        <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                            <DialogTrigger asChild>
                                <Button variant="destructive">Eliminar</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Eliminar servicio</DialogTitle>
                                </DialogHeader>
                                <p className="text-muted-foreground text-sm">
                                    Se eliminará esta orden de servicio. Esta acción no se puede
                                    deshacer.
                                </p>
                                <div className="flex justify-end gap-2">
                                    <Button
                                        variant="outline"
                                        onClick={() => setDeleteOpen(false)}
                                    >
                                        Cancelar
                                    </Button>
                                    <Button variant="destructive" onClick={confirmDelete}>
                                        Eliminar
                                    </Button>
                                </div>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <div className="flex">
                    <Badge variant="secondary" className={STATUS_STYLES[order.status]}>
                        {statusLabel}
                    </Badge>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Información</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-1 text-sm">
                        <p>
                            <span className="text-muted-foreground">Cliente:</span>{' '}
                            {order.customer?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Equipo:</span>{' '}
                            {order.equipment?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Técnico:</span>{' '}
                            {order.technician?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Programado:</span>{' '}
                            {order.scheduled_at ? new Date(order.scheduled_at).toLocaleString() : '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Iniciado:</span>{' '}
                            {order.started_at ? new Date(order.started_at).toLocaleString() : '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Completado:</span>{' '}
                            {order.completed_at ? new Date(order.completed_at).toLocaleString() : '—'}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Nota de voz</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <VoiceRecorder audioUrl={audioUrl} initial={audioRecords} />
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

ServiceOrderShow.layout = {
    breadcrumbs: [
        { title: 'Servicios', href: index.url() },
    ],
};
