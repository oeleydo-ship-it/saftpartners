<?php
namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->isAdmin(); }
    public function rules(): array
    {
        return [
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:10000'],
            // Legal documents are long-form.
            'settings.privacy_policy' => ['nullable', 'string', 'max:60000'],
            'settings.terms' => ['nullable', 'string', 'max:60000'],
            'settings.privacy_policy_updated' => ['nullable', 'date_format:Y-m-d'],
            'settings.terms_updated' => ['nullable', 'date_format:Y-m-d'],
        ];
    }
}
