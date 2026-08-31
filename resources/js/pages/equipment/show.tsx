import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
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
import { index, update, destroy } from '@/routes/equipment';

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

type PageProps = {
    equipment: EquipmentDetail;
    customers: { id: number; name: string }[];
    types: { value: string; label: string }[];
};

export default function EquipmentShow() {
    const { equipment, customers, types } = usePage<PageProps>().props;
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

    const typeLabel = types.find((t) => t.value === equipment.type)?.label ?? equipment.type;

    return (
        <>
            <Head title={`Equipo - ${equipment.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Link href={index.url()} className="text-muted-foreground text-sm">
                    ← Volver a equipos
                </Link>

                <div className="flex items-center justify-between">
                    <Heading
                        title={equipment.name}
                        description={equipment.customer?.name ?? 'Sin cliente'}
                    />

                    <div className="flex gap-2">
                        <Dialog open={editOpen} onOpenChange={setEditOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline">Editar</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Editar equipo</DialogTitle>
                                </DialogHeader>

                                <form onSubmit={submitEdit} className="space-y-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="customer">Cliente *</Label>
                                        <Select
                                            value={String(form.data.customer_id)}
                                            onValueChange={(v) => form.setData('customer_id', v)}
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
                                        <Label htmlFor="name">Nombre *</Label>
                                        <Input
                                            id="name"
                                            value={form.data.name}
                                            onChange={(e) => form.setData('name', e.target.value)}
                                        />
                                        <InputError message={form.errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="type">Tipo *</Label>
                                        <Select
                                            value={String(form.data.type)}
                                            onValueChange={(v) => form.setData('type', v)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Selecciona un tipo" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {types.map((type) => (
                                                    <SelectItem key={type.value} value={type.value}>
                                                        {type.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={form.errors.type} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="brand">Marca</Label>
                                        <Input
                                            id="brand"
                                            value={form.data.brand}
                                            onChange={(e) => form.setData('brand', e.target.value)}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="model">Modelo</Label>
                                        <Input
                                            id="model"
                                            value={form.data.model}
                                            onChange={(e) => form.setData('model', e.target.value)}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="serial">Número de serie</Label>
                                        <Input
                                            id="serial"
                                            value={form.data.serial_number}
                                            onChange={(e) => form.setData('serial_number', e.target.value)}
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="notes">Notas</Label>
                                        <textarea
                                            id="notes"
                                            value={form.data.notes}
                                            onChange={(e) => form.setData('notes', e.target.value)}
                                            className="border-input min-h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm"
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
                                    <DialogTitle>Eliminar equipo</DialogTitle>
                                </DialogHeader>
                                <p className="text-muted-foreground text-sm">
                                    Se eliminará «{equipment.name}». Esta acción no se puede
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

                <Card>
                    <CardHeader>
                        <CardTitle>Información</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-1 text-sm">
                        <p>
                            <span className="text-muted-foreground">Cliente:</span>{' '}
                            {equipment.customer?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Tipo:</span>{' '}
                            {typeLabel}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Marca:</span>{' '}
                            {equipment.brand ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Modelo:</span>{' '}
                            {equipment.model ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Núm. serie:</span>{' '}
                            {equipment.serial_number ?? '—'}
                        </p>
                        {equipment.notes && (
                            <p>
                                <span className="text-muted-foreground">Notas:</span>{' '}
                                {equipment.notes}
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

EquipmentShow.layout = {
    breadcrumbs: [
        { title: 'Equipos', href: index.url() },
    ],
};
