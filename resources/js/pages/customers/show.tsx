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
import { index, update, destroy } from '@/routes/customers';

type CustomerDetail = {
    id: number;
    name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    notes: string | null;
};

type PageProps = {
    customer: CustomerDetail;
};

export default function CustomerShow() {
    const { customer } = usePage<PageProps>().props;
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);

    const form = useForm({
        name: customer.name,
        phone: customer.phone ?? '',
        email: customer.email ?? '',
        address: customer.address ?? '',
        notes: customer.notes ?? '',
    });

    function submitEdit(event: FormEvent) {
        event.preventDefault();

        form.put(update.url({ customer: customer.id }), {
            preserveScroll: true,
            onSuccess: () => {
                setEditOpen(false);
            },
        });
    }

    function confirmDelete() {
        router.delete(destroy.url({ customer: customer.id }));
    }

    return (
        <>
            <Head title={`Cliente - ${customer.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Link href={index.url()} className="text-muted-foreground text-sm">
                    ← Volver a clientes
                </Link>

                <div className="flex items-center justify-between">
                    <Heading
                        title={customer.name}
                        description={customer.email ?? customer.phone ?? 'Sin contacto'}
                    />

                    <div className="flex gap-2">
                        <Dialog open={editOpen} onOpenChange={setEditOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline">Editar</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Editar cliente</DialogTitle>
                                </DialogHeader>

                                <form onSubmit={submitEdit} className="space-y-4">
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
                                        <Label htmlFor="phone">Teléfono</Label>
                                        <Input
                                            id="phone"
                                            value={form.data.phone}
                                            onChange={(e) => form.setData('phone', e.target.value)}
                                        />
                                        <InputError message={form.errors.phone} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Correo</Label>
                                        <Input
                                            id="email"
                                            type="email"
                                            value={form.data.email}
                                            onChange={(e) => form.setData('email', e.target.value)}
                                        />
                                        <InputError message={form.errors.email} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="address">Dirección</Label>
                                        <Input
                                            id="address"
                                            value={form.data.address}
                                            onChange={(e) => form.setData('address', e.target.value)}
                                        />
                                        <InputError message={form.errors.address} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="notes">Notas</Label>
                                        <textarea
                                            id="notes"
                                            value={form.data.notes}
                                            onChange={(e) => form.setData('notes', e.target.value)}
                                            className="border-input min-h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm"
                                        />
                                        <InputError message={form.errors.notes} />
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
                                    <DialogTitle>Eliminar cliente</DialogTitle>
                                </DialogHeader>
                                <p className="text-muted-foreground text-sm">
                                    Se eliminará «{customer.name}». Esta acción no se puede
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
                            <span className="text-muted-foreground">Teléfono:</span>{' '}
                            {customer.phone ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Correo:</span>{' '}
                            {customer.email ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">Dirección:</span>{' '}
                            {customer.address ?? '—'}
                        </p>
                        {customer.notes && (
                            <p>
                                <span className="text-muted-foreground">Notas:</span>{' '}
                                {customer.notes}
                            </p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}

CustomerShow.layout = {
    breadcrumbs: [
        { title: 'Clientes', href: index.url() },
    ],
};
