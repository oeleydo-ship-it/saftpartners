<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MediaUploadRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->isAdmin(); }
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:8192', 'mimetypes:image/jpeg,image/png,image/webp,application/pdf'],
            'alt_text' => ['nullable', 'string', 'max:180'],
        ];
    }
}
