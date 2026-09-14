<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Single-row outgoing email configuration managed from the admin "Email" tab.
 */
class MailSetting extends Model
{
    protected $fillable = [
        'mailer', 'host', 'port', 'encryption', 'username', 'password', 'from_address', 'from_name',
        'contact_to', 'ms_tenant_id', 'ms_client_id', 'ms_client_secret',
        'last_tested_at', 'last_test_status', 'last_test_message',
    ];

    protected $hidden = ['password', 'ms_client_secret'];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
            'ms_client_secret' => 'encrypted',
            'last_tested_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return static::find(1) ?? (new static)->forceFill(['id' => 1, 'mailer' => 'log']);
    }

    /** Safe for the browser: secrets are replaced by "is set" flags. */
    public function toAdminArray(): array
    {
        return [
            ...collect($this->attributesToArray())->only([
                'mailer', 'host', 'port', 'encryption', 'username', 'from_address', 'from_name', 'contact_to',
                'ms_tenant_id', 'ms_client_id', 'last_tested_at', 'last_test_status', 'last_test_message',
            ])->all(),
            'mailer' => $this->mailer ?? 'log',
            'has_password' => filled($this->password),
            'has_client_secret' => filled($this->ms_client_secret),
        ];
    }
}
