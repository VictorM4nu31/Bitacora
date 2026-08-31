import { useEffect, useRef, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';

type AudioItem = {
    id: number;
    status: string;
    duration_ms: number | null;
    statusUrl?: string;
    transcript?: string | null;
};

type Props = {
    audioUrl: string;
    initial: AudioItem[];
};

type Phase = 'idle' | 'recording' | 'uploading' | 'done' | 'error';

function getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp('(^|;\\s*)' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[2]) : null;
}

const STATUS_STYLES: Record<string, string> = {
    uploaded: 'text-blue-700 bg-blue-500/10 dark:text-blue-400',
    processing: 'text-amber-700 bg-amber-500/10 dark:text-amber-400',
    transcribed: 'text-green-700 bg-green-500/10 dark:text-green-400',
    failed: 'text-red-700 bg-red-500/10 dark:text-red-400',
};

const STATUS_LABELS: Record<string, string> = {
    uploaded: 'Subido',
    processing: 'Procesando',
    transcribed: 'Transcrito',
    failed: 'Error',
};

export default function VoiceRecorder({ audioUrl, initial }: Props) {
    const [phase, setPhase] = useState<Phase>('idle');
    const [error, setError] = useState<string | null>(null);
    const [elapsed, setElapsed] = useState(0);
    const [items, setItems] = useState<AudioItem[]>(initial);

    const mediaRecorderRef = useRef<MediaRecorder | null>(null);
    const chunksRef = useRef<Blob[]>([]);
    const streamRef = useRef<MediaStream | null>(null);
    const timerRef = useRef<number | null>(null);
    const startTimeRef = useRef<number>(0);
    const pollingRef = useRef<number | null>(null);

    function startRecording() {
        setError(null);
        setPhase('recording');
        setElapsed(0);

        navigator.mediaDevices
            .getUserMedia({ audio: true })
            .then((stream) => {
                streamRef.current = stream;
                startTimeRef.current = Date.now();
                const recorder = new MediaRecorder(stream);
                mediaRecorderRef.current = recorder;
                chunksRef.current = [];

                recorder.ondataavailable = (e) => {
                    if (e.data.size > 0) chunksRef.current.push(e.data);
                };

                recorder.onstop = () => {
                    if (stream.getTracks().length) {
                        stream.getTracks().forEach((track) => track.stop());
                    }

                    const duration = Math.round(Date.now() - startTimeRef.current);
                    const blob = new Blob(chunksRef.current, { type: recorder.mimeType });

                    if (blob.size === 0) {
                        setPhase('idle');
                        setError('La grabación quedó vacía. Inténtalo de nuevo.');
                        return;
                    }

                    void upload(blob, duration);
                };

                recorder.start();
                timerRef.current = window.setInterval(() => {
                    setElapsed((t) => t + 1);
                }, 1000);
            })
            .catch(() => {
                setPhase('idle');
                setError('No se pudo acceder al micrófono. Revisa los permisos.');
            });
    }

    useEffect(() => {
        const active = items.filter(
            (item) => item.statusUrl && (item.status === 'uploaded' || item.status === 'processing'),
        );

        if (active.length === 0) return;

        const poll = async () => {
            for (const item of active) {
                if (!item.statusUrl) continue;
                try {
                    const res = await fetch(item.statusUrl, { headers: { Accept: 'application/json' } });
                    if (!res.ok) continue;
                    const data = (await res.json()) as AudioItem & { statusLabel?: string };
                    setItems((prev) =>
                        prev.map((i) =>
                            i.id === data.id
                                ? { ...i, status: data.status, transcript: data.transcript ?? i.transcript }
                                : i,
                        ),
                    );
                } catch {
                    // network issues are tolerated; retry on next tick
                }
            }
        };

        void poll();
        pollingRef.current = window.setInterval(poll, 2000);

        return () => {
            if (pollingRef.current) window.clearInterval(pollingRef.current);
        };
    }, [items]);

    function stopRecording() {
        if (timerRef.current) window.clearInterval(timerRef.current);
        mediaRecorderRef.current?.stop();
        setPhase('uploading');
    }

    async function upload(blob: Blob, duration: number) {
        const form = new FormData();
        form.append('audio', blob, 'nota.webm');
        form.append('duration_ms', String(duration));

        try {
            const response = await fetch(audioUrl, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') ?? '',
                    Accept: 'application/json',
                },
                body: form,
            });

            if (!response.ok) {
                throw new Error('No se pudo subir la nota de voz.');
            }

            const data = (await response.json()) as {
                id: number;
                status: string;
                statusUrl: string;
            };
            setItems((prev) => [
                { id: data.id, status: data.status, duration_ms: duration, statusUrl: data.statusUrl },
                ...prev,
            ]);
            setPhase('done');
        } catch {
            setPhase('error');
            setError('No se pudo subir la nota de voz. Revisa tu conexión.');
        }
    }

    const seconds = String(Math.floor(elapsed % 60)).padStart(2, '0');
    const minutes = String(Math.floor(elapsed / 60)).padStart(2, '0');

    return (
        <div className="space-y-4">
            <div className="flex items-center gap-3">
                {phase === 'recording' ? (
                    <Button variant="destructive" onClick={stopRecording}>
                        ⏺ Detener ({minutes}:{seconds})
                    </Button>
                ) : (
                    <Button
                        onClick={startRecording}
                        disabled={phase === 'uploading'}
                    >
                        🎙️ Grabar nota de voz
                    </Button>
                )}

                {phase === 'uploading' && (
                    <span className="text-muted-foreground text-sm">Subiendo…</span>
                )}
                {phase === 'done' && (
                    <span className="text-sm text-green-600 dark:text-green-400">
                        Nota guardada
                    </span>
                )}
            </div>

            {error && <p className="text-sm text-red-600 dark:text-red-400">{error}</p>}

            {items.length > 0 && (
                <ul className="space-y-2">
                    {items.map((item) => (
                        <li
                            key={item.id}
                            className="border-muted rounded-lg border px-3 py-2 text-sm"
                        >
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Nota de voz
                                    {item.duration_ms
                                        ? ` (${Math.round(item.duration_ms / 1000)}s)`
                                        : ''}
                                </span>
                                <Badge variant="secondary" className={STATUS_STYLES[item.status]}>
                                    {STATUS_LABELS[item.status] ?? item.status}
                                </Badge>
                            </div>
                            {item.status === 'transcribed' && item.transcript && (
                                <p className="text-foreground mt-2 line-clamp-3 whitespace-pre-wrap">
                                    {item.transcript}
                                </p>
                            )}
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
