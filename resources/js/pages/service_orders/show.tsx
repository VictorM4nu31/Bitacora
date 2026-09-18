import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { useState, type FormEvent } from 'react';
import Heading from '@/components/heading';
import { useCan } from '@/hooks/use-authorization';
import PhotoGallery from '@/components/photo-gallery';
import ReportEditor from '@/components/report-editor';
import VoiceRecorder from '@/components/voice-recorder';
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

type Report = {
    id: number;
    status: string;
    arrival_time: string | null;
    equipment_type: string | null;
    problem: string | null;
    diagnosis: string | null;
    work_done: string | null;
    tests_performed: string | null;
    result: string | null;
    total_cost: string | number | null;
};

type Audit = {
    events: {
        id: number;
        action: string;
        user_name: string | null;
        new_status: string | null;
        created_at: string;
    }[];
    ai: {
        transcription_ms: number | null;
        analysis_ms: number | null;
        transcription_provider: string | null;
        analysis_provider: string | null;
    } | null;
};

type PageProps = {
    order: OrderDetail;
    statuses: { value: string; label: string }[];
    customers: { id: number; name: string }[];
    equipment: { id: number; name: string; customer_id: number }[];
    audioUrl: string;
    audioRecords: AudioItem[];
    photoUploadUrl: string;
    photos: { id: number; original_name: string; caption: string | null; url: string }[];
    report: Report | null;
    audit: Audit | null;
    reportOptions: {
        types: { value: string; label: string }[];
        updateUrl: string | null;
        finalizeUrl: string | null;
        pdfUrl: string | null;
        shareUrl: string | null;
    };
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

export default function ServiceOrderShow() {
    const { t } = useTranslation();
    const can = useCan();
    const {
        order,
        statuses,
        customers,
        equipment,
        audioUrl,
        audioRecords,
        photoUploadUrl,
        photos,
        report,
        reportOptions,
        audit,
    } = usePage<PageProps>().props;
    const [editOpen, setEditOpen] = useState(false);
    const [deleteOpen, setDeleteOpen] = useState(false);

    const form = useForm({
        customer_id: String(order.customer?.id ?? ''),
        equipment_id: order.equipment ? String(order.equipment.id) : '',
        scheduled_at: order.scheduled_at ?? '',
    });

    const statusLabel =
        statuses.find((s) => s.value === order.status)?.label ?? order.status;

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
            <Head
                title={`${t('Service')} - ${order.customer?.name ?? t('No customer')}`}
            />

            <div className="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
                <Link
                    href={index.url()}
                    className="text-muted-foreground text-sm"
                >
                    ← {t('Back to services')}
                </Link>

                <div className="flex items-center justify-between">
                    <Heading
                        title={order.customer?.name ?? t('No customer')}
                        description={t('Service order')}
                    />

                    <div className="flex gap-2">
                        {can('update services') && order.status === 'pending' && (
                            <Button onClick={() => changeStatus('in_progress')}>
                                {t('Start service')}
                            </Button>
                        )}
                        {can('update services') && order.status === 'in_progress' && (
                            <Button onClick={() => changeStatus('completed')}>
                                {t('Complete service')}
                            </Button>
                        )}

                        {can('update services') && <Dialog open={editOpen} onOpenChange={setEditOpen}>
                            <DialogTrigger asChild>
                                <Button variant="outline">{t('Edit')}</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>
                                        {t('Edit service')}
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
                                            onValueChange={(v) => {
                                                form.setData(
                                                    'equipment_id',
                                                    '',
                                                );
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
                                                        value={String(
                                                            customer.id,
                                                        )}
                                                    >
                                                        {customer.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="equipment">
                                            {t('Equipment')}
                                        </Label>
                                        <Select
                                            value={String(
                                                form.data.equipment_id,
                                            )}
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
                                                {equipment.map((item) => (
                                                    <SelectItem
                                                        key={item.id}
                                                        value={String(item.id)}
                                                    >
                                                        {item.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
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
                        </Dialog>}

                        {can('delete services') && <Dialog open={deleteOpen} onOpenChange={setDeleteOpen}>
                            <DialogTrigger asChild>
                                <Button variant="destructive">
                                    {t('Delete')}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>
                                        {t('Delete service')}
                                    </DialogTitle>
                                </DialogHeader>
                                <p className="text-muted-foreground text-sm">
                                    {t(
                                        'This will permanently delete this service order.',
                                    )}
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
                        </Dialog>}
                    </div>
                </div>

                <div className="flex">
                    <Badge
                        variant={STATUS_VARIANTS[order.status] ?? 'secondary'}
                    >
                        {statusLabel}
                    </Badge>
                </div>

                <Card className="order-band">
                    <CardHeader>
                        <CardTitle>{t('Information')}</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-1 text-sm">
                        <p>
                            <span className="text-muted-foreground">
                                {t('Customer')}:
                            </span>{' '}
                            {order.customer?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Equipment')}:
                            </span>{' '}
                            {order.equipment?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Technician')}:
                            </span>{' '}
                            {order.technician?.name ?? '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Scheduled')}:
                            </span>{' '}
                            {order.scheduled_at
                                ? new Date(order.scheduled_at).toLocaleString()
                                : '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Started')}:
                            </span>{' '}
                            {order.started_at
                                ? new Date(order.started_at).toLocaleString()
                                : '—'}
                        </p>
                        <p>
                            <span className="text-muted-foreground">
                                {t('Completed')}:
                            </span>{' '}
                            {order.completed_at
                                ? new Date(order.completed_at).toLocaleString()
                                : '—'}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Service report')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ReportEditor
                            report={report}
                            types={reportOptions.types}
                            updateUrl={reportOptions.updateUrl}
                            finalizeUrl={reportOptions.finalizeUrl}
                            pdfUrl={reportOptions.pdfUrl}
                            shareUrl={reportOptions.shareUrl}
                            canUpdate={can('update reports')}
                            canFinalize={can('finalize reports')}
                            canGeneratePdf={can('generate pdf')}
                            canShare={can('share reports')}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Voice note')}</CardTitle>
                    </CardHeader>
                    <CardContent className="rounded-b-[10px] bg-[#101413] p-5 text-[#F2EFE6]">
                        <VoiceRecorder
                            audioUrl={audioUrl}
                            initial={audioRecords}
                            canUpload={can('update services')}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('Photos')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <PhotoGallery
                            photoUploadUrl={photoUploadUrl}
                            initial={photos}
                            canUpload={can('update services')}
                        />
                    </CardContent>
                </Card>

                {audit && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('Audit')}</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            {audit.ai && (
                                <div className="grid gap-1 font-mono text-xs tabular-nums">
                                    <p className="text-muted-foreground">
                                        {t('Transcription')}:{' '}
                                        {audit.ai.transcription_provider ?? '—'}{' '}
                                        · {audit.ai.transcription_ms ?? 0} ms
                                    </p>
                                    <p className="text-muted-foreground">
                                        {t('Analysis')}:{' '}
                                        {audit.ai.analysis_provider ?? '—'} ·{' '}
                                        {audit.ai.analysis_ms ?? 0} ms
                                    </p>
                                </div>
                            )}
                            {audit.events.length === 0 ? (
                                <p className="text-muted-foreground text-sm">
                                    {t('No events recorded.')}
                                </p>
                            ) : (
                                <ul className="space-y-2">
                                    {audit.events.map((event) => (
                                        <li
                                            key={event.id}
                                            className="border-muted flex items-center justify-between rounded-lg border px-3 py-2 text-sm"
                                        >
                                            <span className="text-muted-foreground">
                                                {event.action} —{' '}
                                                {event.created_at}
                                            </span>
                                            <span className="text-muted-foreground text-xs">
                                                {event.user_name ??
                                                    t('System (AI)')}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </CardContent>
                    </Card>
                )}
            </div>
        </>
    );
}

ServiceOrderShow.layout = {
    breadcrumbs: [{ title: 'Services', href: index.url() }],
};
