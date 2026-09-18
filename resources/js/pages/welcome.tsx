import { Head, Link, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';

const STEPS = [
    {
        n: '01',
        title: 'Graba la nota de voz',
        text: 'El técnico habla como trabaja, sin formularios en campo.',
    },
    {
        n: '02',
        title: 'Revisa el borrador',
        text: 'La IA propone; el humano confirma. Nada se finaliza solo.',
    },
    {
        n: '03',
        title: 'Agrega evidencia',
        text: 'Fotos del servicio y tiempos de procesamiento trazables.',
    },
    {
        n: '04',
        title: 'Comparte el PDF',
        text: 'Reporte profesional por enlace firmado, sin login.',
    },
];

export default function Welcome() {
    const { auth, name } = usePage().props;

    return (
        <>
            <Head title="Bitácora — Habla como trabajas" />
            <div className="flex min-h-screen flex-col bg-background text-foreground">
                <header className="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-5">
                    <span className="flex items-center gap-2">
                        <span className="flex size-9 items-center justify-center rounded-md bg-primary text-primary-foreground">
                            <AppLogoIcon className="size-5" />
                        </span>
                        <span className="font-display text-lg font-bold tracking-tight">
                            {name}
                        </span>
                    </span>
                    <nav className="flex items-center gap-2 text-sm">
                        {auth.user ? (
                            <Link
                                href={dashboard()}
                                className="inline-flex h-11 items-center rounded-lg border-[1.5px] border-primary bg-primary px-5 font-semibold text-primary-foreground"
                            >
                                Ir al taller
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={login()}
                                    className="inline-flex h-11 items-center rounded-lg px-4 font-semibold hover:bg-accent"
                                >
                                    Entrar
                                </Link>
                                <Link
                                    href={register()}
                                    className="inline-flex h-11 items-center rounded-lg border-[1.5px] border-primary bg-primary px-5 font-semibold text-primary-foreground"
                                >
                                    Crear cuenta
                                </Link>
                            </>
                        )}
                    </nav>
                </header>

                <main className="mx-auto grid w-full max-w-5xl flex-1 gap-10 px-6 py-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                    <div>
                        <p className="inline-flex items-center rounded-full border-[1.5px] border-line bg-card px-3 py-1 font-mono text-[11px] font-semibold tracking-[0.08em] uppercase">
                            Bitácora de campo
                        </p>
                        <h1 className="font-display mt-4 text-4xl leading-[1.05] font-extrabold tracking-tight lg:text-5xl">
                            Habla como trabajas.
                            <br />
                            Entrega un reporte profesional.
                        </h1>
                        <p className="mt-4 max-w-xl text-lg text-muted-foreground">
                            Voz → borrador automático → revisión humana → PDF
                            compartible. Trazabilidad por equipo, sin papeleo.
                        </p>
                        <div className="mt-6 flex flex-wrap gap-3">
                            <Link
                                href={auth.user ? dashboard() : register()}
                                className="inline-flex h-12 items-center rounded-lg bg-signal-solid px-6 font-semibold text-signal-foreground"
                            >
                                {auth.user
                                    ? 'Abrir órdenes de servicio'
                                    : 'Empezar ahora'}
                            </Link>
                            <Link
                                href={auth.user ? dashboard() : login()}
                                className="inline-flex h-12 items-center rounded-lg border-[1.5px] border-input bg-card px-6 font-semibold"
                            >
                                Ver cómo funciona
                            </Link>
                        </div>
                    </div>

                    <ol className="paper-card order-band space-y-0 rounded-[10px] bg-card">
                        {STEPS.map((step, i) => (
                            <li
                                key={step.n}
                                className={`flex gap-4 px-5 py-4 ${i > 0 ? 'border-t border-line' : ''}`}
                            >
                                <span className="font-mono text-sm font-semibold text-muted-foreground tabular-nums">
                                    {step.n}
                                </span>
                                <span>
                                    <span className="font-display block font-bold">
                                        {step.title}
                                    </span>
                                    <span className="block text-sm text-muted-foreground">
                                        {step.text}
                                    </span>
                                </span>
                            </li>
                        ))}
                    </ol>
                </main>

                <footer className="mx-auto w-full max-w-5xl px-6 pb-8 text-sm text-muted-foreground">
                    La IA propone, el técnico confirma. Todo queda trazable por
                    empresa y equipo.
                </footer>
            </div>
        </>
    );
}
