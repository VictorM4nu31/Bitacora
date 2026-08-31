import { Head, Link, useForm, usePage } from '@inertiajs/react';
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
            <Head title="Clientes" />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <div className="flex items-center justify-between">
                    <Heading
                        title="Clientes"
                        description="Los clientes de tu empresa y sus equipos"
                    />

                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>Nuevo cliente</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Nuevo cliente</DialogTitle>
                            </DialogHeader>

                            <form onSubmit={submit} className="space-y-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Nombre *</Label>
                                    <Input
                                        id="name"
                                        value={form.data.name}
                                        onChange={(e) => form.setData('name', e.target.value)}
                                        placeholder="Nombre del cliente"
                                        autoFocus
                                    />
                                    <InputError message={form.errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Teléfono</Label>
                                    <Input
                                        id="phone"
                                        value={form.data.phone}
                                        onChange={(e) => form.setData('phone', e.target.value)}
                                        placeholder="55 1234 5678"
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
                                        placeholder="cliente@correo.com"
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
                                        onClick={() => setOpen(false)}
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
                </div>

                <div className="rounded-xl border">
                    {customers.data.length === 0 ? (
                        <div className="text-muted-foreground p-8 text-center text-sm">
                            No hay clientes todavía. Crea el primero.
                        </div>
                    ) : (
                        customers.data.map((customer) => (
                            <Link
                                key={customer.id}
                                href={show.url({ customer: customer.id })}
                                className="hover:bg-muted grid border-b px-4 py-3 transition-colors last:border-b-0 dark:hover:bg-muted/40"
                            >
                                <div className="min-w-0">
                                    <p className="truncate font-medium">{customer.name}</p>
                                    <p className="text-muted-foreground text-sm">
                                        {customer.phone ?? customer.email ?? 'Sin contacto'}
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
            title: 'Clientes',
            href: index.url(),
        },
    ],
};
