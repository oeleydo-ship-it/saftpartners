<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaUploadRequest;
use App\Models\Media;
use App\Services\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function store(MediaUploadRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $mime = $file->getMimeType();
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true), 422, 'Unsupported file contents.');
        $path = $file->store('media/'.date('Y/m'), 'public');
        $media = Media::create([
            'user_id' => $request->user()->id,
            'path' => $path,
            'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
            'mime_type' => $mime,
            'size' => $file->getSize(),
            'alt_text' => $request->validated('alt_text'),
        ]);
        Audit::record('media.uploaded', $media);
        return back()->with('success', 'Media uploaded.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        abort_unless(request()->user()->canDeleteContent(), 403);
        Storage::disk($media->disk)->delete($media->path);
        Audit::record('media.deleted', $media);
        $media->delete();
        return back()->with('success', 'Media deleted.');
    }
}
