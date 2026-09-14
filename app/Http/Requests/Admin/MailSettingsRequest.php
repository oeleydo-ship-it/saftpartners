<?php
namespace App\Http\Requests\Admin;

use App\Models\MailSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class MailSettingsRequest extends FormRequest
{
    public function authorize(): bool { return (bool) $this->user()?->can('manage-mail'); }

    public function rules(): array
    {
        return [
            'mailer' => ['required', 'in:log,smtp,microsoft365'],
            'host' => ['nullable', 'required_if:mailer,smtp', 'string', 'max:255', 'regex:/^[A-Za-z0-9.-]+$/'],
            'port' => ['nullable', 'required_if:mailer,smtp', 'integer', 'between:1,65535'],
            'encryption' => ['nullable', 'required_if:mailer,smtp', 'in:tls,ssl'],
            'username' => ['nullable', 'required_if:mailer,microsoft365', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1000'],
            'from_address' => ['nullable', 'required_unless:mailer,log', 'email:rfc', 'max:190'],
            'from_name' => ['nullable', 'string', 'max:120'],
            'contact_to' => ['nullable', 'email:rfc', 'max:190'],
            'ms_tenant_id' => ['nullable', 'required_if:mailer,microsoft365', 'string', 'max:100', 'regex:/^[A-Za-z0-9.-]+$/'],
            'ms_client_id' => ['nullable', 'required_if:mailer,microsoft365', 'uuid'],
            'ms_client_secret' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => $this->input('mailer') === 'microsoft365' ? 'mailbox' : 'username',
            'from_address' => 'from address',
            'contact_to' => 'enquiry recipient',
            'ms_tenant_id' => 'tenant ID',
            'ms_client_id' => 'client (application) ID',
            'ms_client_secret' => 'client secret',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            // A blank secret keeps the saved one, so it is only required when nothing is stored yet.
            if ($this->input('mailer') === 'microsoft365' && blank($this->input('ms_client_secret')) && blank(MailSetting::current()->ms_client_secret)) {
                $validator->errors()->add('ms_client_secret', 'The client secret field is required.');
            }
            if ($this->input('mailer') === 'microsoft365' && filled($this->input('username')) && ! filter_var($this->input('username'), FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('username', 'The mailbox must be a valid email address.');
            }
        }];
    }
}
