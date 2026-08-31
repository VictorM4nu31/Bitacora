<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePhotoRequest;
use App\Models\ServiceOrder;
use App\Models\ServicePhoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ServicePhotoController extends Controller
{
    /**
     * Store an uploaded photo for a service order.
     */
    public function store(StorePhotoRequest $request, ServiceOrder $serviceOrder): JsonResponse
    {
        Gate::authorize('view', $serviceOrder);

        $file = $request->file('photo');
        $extension = $file->clientExtension() ?: 'jpg';

        $path = $file->storeAs(
            sprintf('photos/%s/%s/%s', $serviceOrder->company_id, now()->format('Y/m'), Str::uuid()),
            sprintf('%s.%s', Str::uuid(), $extension),
            'local',
        );

        $photo = ServicePhoto::create([
            'service_order_id' => $serviceOrder->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'id' => $photo->id,
            'url' => route('service-photos.file', $photo),
            'original_name' => $photo->original_name,
        ]);
    }

    /**
     * Serve the photo file (authenticated private access).
     */
    public function file(ServicePhoto $servicePhoto): StreamedResponse
    {
        Gate::authorize('view', $servicePhoto->serviceOrder);

        return Storage::disk($servicePhoto->disk)->response($servicePhoto->path);
    }

    /**
     * Remove a photo.
     */
    public function destroy(ServicePhoto $servicePhoto): JsonResponse
    {
        Gate::authorize('view', $servicePhoto->serviceOrder);

        Storage::disk($servicePhoto->disk)->delete($servicePhoto->path);
        $servicePhoto->delete();

        return response()->json(['deleted' => true]);
    }
}
