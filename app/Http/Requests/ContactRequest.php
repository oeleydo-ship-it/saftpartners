<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:160'],
            'subject' => ['nullable', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
            // Spam traps are checked by App\Services\SpamGuard, not here, so bots get no validation hints.
            'website' => ['nullable', 'string', 'max:255'],
            'form_token' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
