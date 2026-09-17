import { router, useForm } from '@inertiajs/react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type ReportData = {
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

type Props = {
    report: ReportData | null;
    types: { value: string; label: string }[];
    updateUrl: string | null;
    finalizeUrl: string | null;
    pdfUrl: string | null;
    shareUrl: string | null;
    canUpdate: boolean;
    canFinalize: boolean;
    canGeneratePdf: boolean;
    canShare: boolean;
};

export default function ReportEditor({
    report,
    types,
    updateUrl,
    finalizeUrl,
    pdfUrl,
    shareUrl,
    canUpdate,
    canFinalize,
    canGeneratePdf,
    canShare,
}: Props) {
    const { t } = useTranslation();
    const form = useForm({
        arrival_time: report?.arrival_time ?? '',
        equipment_type: report?.equipment_type ?? '',
        problem: report?.problem ?? '',
        diagnosis: report?.diagnosis ?? '',
        work_done: report?.work_done ?? '',
        tests_performed: report?.tests_performed ?? '',
        result: report?.result ?? '',
        total_cost: report?.total_cost ?? '',
    });

    if (!report) {
        return (
            <p className="text-muted-foreground text-sm">
                {t(
                    'The report will be generated automatically when the voice note is processed.',
                )}
            </p>
        );
    }

    async function share() {
        if (!shareUrl) return;
        try {
            const res = await fetch(shareUrl, {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) throw new Error('share failed');
            const data = (await res.json()) as { url: string };
            await navigator.clipboard.writeText(data.url);
            toast.success(t('Link copied'));
        } catch {
            toast.error(t('Could not share the report'));
        }
    }

    if (report.status === 'finalized') {
        return (
            <div className="space-y-3 text-sm">
                <div className="flex items-center justify-between gap-2">
                    <Badge>{t('Finalized')}</Badge>
                    <div className="flex gap-2">
                        {pdfUrl && canGeneratePdf && (
                            <Button
                                asChild
                                variant="outline"
                                size="sm"
                                type="button"
                            >
                                <a href={pdfUrl}>{t('Download PDF')}</a>
                            </Button>
                        )}
                        {shareUrl && canShare && (
                            <Button size="sm" type="button" onClick={share}>
                                {t('Share with customer')}
                            </Button>
                        )}
                    </div>
                </div>
                {report.arrival_time && (
                    <p>
                        <span className="text-muted-foreground">
                            {t('Arrival')}:
                        </span>{' '}
                        {report.arrival_time}
                    </p>
                )}
                {report.problem && (
                    <p>
                        <span className="text-muted-foreground">
                            {t('Problem')}:
                        </span>{' '}
                        {report.problem}
                    </p>
                )}
                {report.diagnosis && (
                    <p>
                        <span className="text-muted-foreground">
                            {t('Diagnosis')}:
                        </span>{' '}
                        {report.diagnosis}
                    </p>
                )}
                {report.work_done && (
                    <p>
                        <span className="text-muted-foreground">
                            {t('Work done')}:
                        </span>{' '}
                        {report.work_done}
                    </p>
                )}
                {report.result && (
                    <p>
                        <span className="text-muted-foreground">
                            {t('Result')}:
                        </span>{' '}
                        {report.result}
                    </p>
                )}
                {report.total_cost !== null && (
                    <p>
                        <span className="text-muted-foreground">
                            {t('Cost')}:
                        </span>{' '}
                        ${report.total_cost}
                    </p>
                )}
            </div>
        );
    }

    function saveDraft() {
        if (!updateUrl) return;
        form.put(updateUrl, { preserveScroll: true });
    }

    function finalize() {
        if (!updateUrl) return;
        router.post(finalizeUrl ?? '', {}, { preserveScroll: true });
    }

    return (
        <div className="space-y-4">
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div className="grid gap-2">
                    <Label htmlFor="arrival_time">{t('Arrival time')}</Label>
                    <Input
                        id="arrival_time"
                        value={form.data.arrival_time}
                        onChange={(e) =>
                            form.setData('arrival_time', e.target.value)
                        }
                        placeholder="10:20"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="equipment_type">
                        {t('Equipment type')}
                    </Label>
                    <Select
                        value={String(form.data.equipment_type)}
                        onValueChange={(v) => form.setData('equipment_type', v)}
                    >
                        <SelectTrigger>
                            <SelectValue placeholder={t('Select a type')} />
                        </SelectTrigger>
                        <SelectContent>
                            {types.map((type) => (
                                <SelectItem key={type.value} value={type.value}>
                                    {type.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="problem">{t('Problem')}</Label>
                <Input
                    id="problem"
                    value={form.data.problem}
                    onChange={(e) => form.setData('problem', e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="diagnosis">{t('Diagnosis')}</Label>
                <Input
                    id="diagnosis"
                    value={form.data.diagnosis}
                    onChange={(e) => form.setData('diagnosis', e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="work_done">{t('Work done')}</Label>
                <textarea
                    id="work_done"
                    value={form.data.work_done}
                    onChange={(e) => form.setData('work_done', e.target.value)}
                    className="border-input min-h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm"
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="tests_performed">{t('Tests performed')}</Label>
                <Input
                    id="tests_performed"
                    value={form.data.tests_performed}
                    onChange={(e) =>
                        form.setData('tests_performed', e.target.value)
                    }
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="result">{t('Result')}</Label>
                <Input
                    id="result"
                    value={form.data.result}
                    onChange={(e) => form.setData('result', e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="total_cost">{t('Total cost (MXN)')}</Label>
                <Input
                    id="total_cost"
                    type="number"
                    step="0.01"
                    value={String(form.data.total_cost)}
                    onChange={(e) => form.setData('total_cost', e.target.value)}
                />
            </div>

            <div className="flex justify-end gap-2">
                 {canUpdate && <Button
                    type="button"
                    variant="outline"
                    onClick={saveDraft}
                    disabled={form.processing}
                >
                    {t('Save draft')}
                 </Button>}
                 {canFinalize && <Button
                    type="button"
                    onClick={finalize}
                    disabled={form.processing}
                >
                    {t('Confirm report')}
                 </Button>}
            </div>
        </div>
    );
}
