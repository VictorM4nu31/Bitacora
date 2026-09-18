export default function Heading({
    title,
    description,
    variant = 'default',
}: {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
}) {
    return (
        <header className={variant === 'small' ? '' : 'mb-8 space-y-0.5'}>
            <h2
                className={
                    variant === 'small'
                        ? 'font-display mb-0.5 text-base font-bold tracking-tight'
                        : 'font-display text-2xl font-extrabold tracking-tight'
                }
            >
                {title}
            </h2>
            {description && (
                <p className="text-muted-foreground text-sm">{description}</p>
            )}
        </header>
    );
}
