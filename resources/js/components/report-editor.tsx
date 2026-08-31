import { router, useForm } from '@inertiajs/react';
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
};

export default function ReportEditor({
    report,
    types,
    updateUrl,
    finalizeUrl,
    pdfUrl,
    shareUrl,
}: Props) {
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
                El reporte se generará automáticamente cuando se procese la nota de voz.
            </p>
        );
    }

    async function share() {
        if (!shareUrl) return;
        try {
            const res = await fetch(shareUrl, { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('share failed');
            const data = (await res.json()) as { url: string };
            await navigator.clipboard.writeText(data.url);
            toast.success('Enlace copiado');
        } catch {
            toast.error('No se pudo compartir el reporte');
        }
    }

    if (report.status === 'finalized') {
        return (
            <div className="space-y-3 text-sm">
                <div className="flex items-center justify-between gap-2">
                    <Badge>Finalizado</Badge>
                    <div className="flex gap-2">
                        {pdfUrl && (
                            <Button asChild variant="outline" size="sm" type="button">
                                <a href={pdfUrl}>Descargar PDF</a>
                            </Button>
                        )}
                        {shareUrl && (
                            <Button size="sm" type="button" onClick={share}>
                                Compartir con cliente
                            </Button>
                        )}
                    </div>
                </div>
                {report.arrival_time && (
                    <p>
                        <span className="text-muted-foreground">Llegada:</span> {report.arrival_time}
                    </p>
                )}
                {report.problem && (
                    <p>
                        <span className="text-muted-foreground">Problema:</span> {report.problem}
                    </p>
                )}
                {report.diagnosis && (
                    <p>
                        <span className="text-muted-foreground">Diagnóstico:</span> {report.diagnosis}
                    </p>
                )}
                {report.work_done && (
                    <p>
                        <span className="text-muted-foreground">Trabajo:</span> {report.work_done}
                    </p>
                )}
                {report.result && (
                    <p>
                        <span className="text-muted-foreground">Resultado:</span> {report.result}
                    </p>
                )}
                {report.total_cost !== null && (
                    <p>
                        <span className="text-muted-foreground">Costo:</span> ${report.total_cost}
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
                    <Label htmlFor="arrival_time">Hora de llegada</Label>
                    <Input
                        id="arrival_time"
                        value={form.data.arrival_time}
                        onChange={(e) => form.setData('arrival_time', e.target.value)}
                        placeholder="10:20"
                    />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="equipment_type">Tipo de equipo</Label>
                    <Select
                        value={String(form.data.equipment_type)}
                        onValueChange={(v) => form.setData('equipment_type', v)}
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
                </div>
            </div>

            <div className="grid gap-2">
                <Label htmlFor="problem">Problema</Label>
                <Input
                    id="problem"
                    value={form.data.problem}
                    onChange={(e) => form.setData('problem', e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="diagnosis">Diagnóstico</Label>
                <Input
                    id="diagnosis"
                    value={form.data.diagnosis}
                    onChange={(e) => form.setData('diagnosis', e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="work_done">Trabajo realizado</Label>
                <textarea
                    id="work_done"
                    value={form.data.work_done}
                    onChange={(e) => form.setData('work_done', e.target.value)}
                    className="border-input min-h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm"
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="tests_performed">Pruebas realizadas</Label>
                <Input
                    id="tests_performed"
                    value={form.data.tests_performed}
                    onChange={(e) => form.setData('tests_performed', e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="result">Resultado</Label>
                <Input
                    id="result"
                    value={form.data.result}
                    onChange={(e) => form.setData('result', e.target.value)}
                />
            </div>

            <div className="grid gap-2">
                <Label htmlFor="total_cost">Costo total (MXN)</Label>
                <Input
                    id="total_cost"
                    type="number"
                    step="0.01"
                    value={String(form.data.total_cost)}
                    onChange={(e) => form.setData('total_cost', e.target.value)}
                />
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" onClick={saveDraft} disabled={form.processing}>
                    Guardar borrador
                </Button>
                <Button type="button" onClick={finalize} disabled={form.processing}>
                    Confirmar reporte
                </Button>
            </div>
        </div>
    );
}
