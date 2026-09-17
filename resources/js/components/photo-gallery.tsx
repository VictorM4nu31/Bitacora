import { useRef, useState } from 'react';
import { useTranslation } from '@sematico/laravel-inertia-i18n-react';
import { Button } from '@/components/ui/button';

type Photo = {
    id: number;
    original_name: string;
    url: string;
};

type Props = {
    photoUploadUrl: string;
    initial: Photo[];
    canUpload: boolean;
};

function getCookie(name: string): string | null {
    const match = document.cookie.match(
        new RegExp('(^|;\\s*)' + name + '=([^;]*)'),
    );
    return match ? decodeURIComponent(match[2]) : null;
}

export default function PhotoGallery({ photoUploadUrl, initial, canUpload }: Props) {
    const { t } = useTranslation();
    const [photos, setPhotos] = useState<Photo[]>(initial);
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const inputRef = useRef<HTMLInputElement | null>(null);

    async function onFiles() {
        const input = inputRef.current;
        if (!input || !input.files?.length || uploading) return;

        const file = input.files[0];
        setUploading(true);
        setError(null);

        const form = new FormData();
        form.append('photo', file);

        try {
            const response = await fetch(photoUploadUrl, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') ?? '',
                    Accept: 'application/json',
                },
                body: form,
            });

            if (!response.ok) {
                throw new Error('Could not upload the photo.');
            }

            const data = (await response.json()) as Photo;
            setPhotos((prev) => [...prev, data]);
            input.value = '';
        } catch {
            setError(
                t('Could not upload the photo. Check the format and size.'),
            );
        } finally {
            setUploading(false);
        }
    }

    return (
        <div className="space-y-4">
            <div className="flex items-center gap-3">
                {canUpload && <input
                    ref={inputRef}
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    className="hidden"
                    onChange={onFiles}
                />}
                {canUpload && <Button
                    type="button"
                    variant="outline"
                    disabled={uploading}
                    onClick={() => inputRef.current?.click()}
                >
                    {t('Upload photo')}
                </Button>}
                {uploading && (
                    <span className="text-muted-foreground text-sm">
                        {t('Uploading…')}
                    </span>
                )}
            </div>

            {error && (
                <p className="text-sm text-red-600 dark:text-red-400">
                    {error}
                </p>
            )}

            {photos.length === 0 ? (
                <p className="text-muted-foreground text-sm">
                    {t('No photos yet. Upload evidence of the service.')}
                </p>
            ) : (
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    {photos.map((photo) => (
                        <div
                            key={photo.id}
                            className="overflow-hidden rounded-lg border"
                        >
                            <img
                                src={photo.url}
                                alt={photo.original_name}
                                className="aspect-video w-full object-cover"
                            />
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
