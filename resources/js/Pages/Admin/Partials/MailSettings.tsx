import { useForm } from '@inertiajs/react';
import { FormEvent, ReactNode } from 'react';

export type MailSettingsData = {
    mailer: 'log' | 'smtp' | 'microsoft365';
    host?: string | null; port?: number | null; encryption?: string | null; username?: string | null;
    from_address?: string | null; from_name?: string | null; contact_to?: string | null;
    ms_tenant_id?: string | null; ms_client_id?: string | null;
    has_password: boolean; has_client_secret: boolean;
    last_tested_at?: string | null; last_test_status?: string | null; last_test_message?: string | null;
};

type Preset = { id: string; label: string; description: string; values: Partial<Record<string, string | number>> };

const presets: Preset[] = [
    { id: 'm365-oauth', label: 'Microsoft 365', description: 'Recommended. Modern authentication (OAuth 2.0).', values: { mailer: 'microsoft365' } },
    { id: 'm365-basic', label: 'Microsoft 365 (password)', description: 'Legacy SMTP AUTH with a mailbox password.', values: { mailer: 'smtp', host: 'smtp.office365.com', port: 587, encryption: 'tls' } },
    { id: 'smtp', label: 'Other SMTP server', description: 'Google Workspace, SendGrid, Mailgun, cPanel…', values: { mailer: 'smtp' } },
    { id: 'log', label: 'Log only', description: 'Do not send. Write emails to the log (development).', values: { mailer: 'log' } },
];

const Field = ({ label, error, hint, children, wide }: { label: string; error?: string; hint?: ReactNode; children: ReactNode; wide?: boolean }) =>
    <label className={wide ? 'wide' : ''}>{label}{children}{hint && <span className="field-hint">{hint}</span>}{error && <small className="form-error">{error}</small>}</label>;

export default function MailSettings({ mail, userEmail }: { mail: MailSettingsData; userEmail: string }) {
    const form = useForm({
        mailer: mail.mailer,
        host: mail.host ?? '', port: mail.port ?? 587, encryption: mail.encryption ?? 'tls',
        username: mail.username ?? '', password: '',
        from_address: mail.from_address ?? '', from_name: mail.from_name ?? 'SAF Partners', contact_to: mail.contact_to ?? '',
        ms_tenant_id: mail.ms_tenant_id ?? '', ms_client_id: mail.ms_client_id ?? '', ms_client_secret: '',
    });
    const test = useForm({ to: userEmail });
    const errors = form.errors as Record<string, string>;

    const activePreset = form.data.mailer === 'microsoft365' ? 'm365-oauth'
        : form.data.mailer === 'log' ? 'log'
        : form.data.host === 'smtp.office365.com' ? 'm365-basic' : 'smtp';

    const choose = (preset: Preset) => form.setData(data => ({ ...data, ...preset.values } as typeof data));
    const save = (e: FormEvent) => { e.preventDefault(); form.put(route('admin.mail.update'), { preserveScroll: true, onSuccess: () => form.setData(d => ({ ...d, password: '', ms_client_secret: '' })) }); };
    const sendTest = (e: FormEvent) => { e.preventDefault(); test.post(route('admin.mail.test'), { preserveScroll: true }); };
    const input = (name: keyof typeof form.data, props: Record<string, unknown> = {}) =>
        <input value={form.data[name] as string | number} onChange={e => form.setData(name, (props.type === 'number' ? +e.target.value : e.target.value) as never)} aria-invalid={!!errors[name]} {...props} />;
    const secretPlaceholder = (saved: boolean) => saved ? '•••••••• saved — leave blank to keep' : '';

    return <>
        <section className="admin-panel">
            <h2>Outgoing email</h2>
            <p className="admin-help">Used for website enquiry notifications and account emails such as password resets. Until you save settings here, the <code>MAIL_*</code> values in the server's <code>.env</code> file are used.</p>

            <div className="mail-presets" role="radiogroup" aria-label="Email provider">
                {presets.map(preset => <button type="button" key={preset.id} role="radio" aria-checked={activePreset === preset.id}
                    className={activePreset === preset.id ? 'active' : ''} onClick={() => choose(preset)}>
                    <strong>{preset.label}</strong><span>{preset.description}</span>
                </button>)}
            </div>

            <form onSubmit={save} className="admin-form-grid">
                {form.data.mailer === 'microsoft365' && <>
                    <div className="admin-help wide">
                        <strong>Microsoft 365 setup (one time, by a Microsoft 365 administrator)</strong>
                        <ol>
                            <li>In the <strong>Microsoft Entra admin center</strong>, go to App registrations → <strong>New registration</strong> (single tenant). Copy the <strong>Application (client) ID</strong> and <strong>Directory (tenant) ID</strong>.</li>
                            <li>Under <strong>API permissions</strong>, add <em>APIs my organization uses → Office 365 Exchange Online → Application permissions → <code>SMTP.SendAsApp</code></em>, then <strong>Grant admin consent</strong>.</li>
                            <li>Under <strong>Certificates &amp; secrets</strong>, create a client secret and copy its <strong>Value</strong>. Note its expiry date.</li>
                            <li>In Exchange Online PowerShell, register the app and give it access to the sending mailbox:<br />
                                <code>New-ServicePrincipal -AppId &lt;client ID&gt; -ObjectId &lt;Enterprise application object ID&gt;</code><br />
                                <code>Add-MailboxPermission -Identity &lt;mailbox&gt; -User &lt;service principal ID&gt; -AccessRights FullAccess</code></li>
                            <li>Make sure <strong>Authenticated SMTP</strong> is enabled for the mailbox (Microsoft 365 admin center → Users → the mailbox → Mail → Manage email apps).</li>
                        </ol>
                    </div>
                    <Field label="Directory (tenant) ID" error={errors.ms_tenant_id}>{input('ms_tenant_id', { placeholder: '00000000-0000-0000-0000-000000000000', autoComplete: 'off' })}</Field>
                    <Field label="Application (client) ID" error={errors.ms_client_id}>{input('ms_client_id', { placeholder: '00000000-0000-0000-0000-000000000000', autoComplete: 'off' })}</Field>
                    <Field label="Client secret value" error={errors.ms_client_secret} hint="Stored encrypted. Renew it in Entra before it expires.">
                        {input('ms_client_secret', { type: 'password', autoComplete: 'new-password', placeholder: secretPlaceholder(mail.has_client_secret) })}
                    </Field>
                    <Field label="Sending mailbox" error={errors.username} hint="The licensed or shared mailbox the app sends as.">
                        {input('username', { type: 'email', placeholder: 'contact@safpartners.ae', autoComplete: 'off' })}
                    </Field>
                </>}

                {form.data.mailer === 'smtp' && <>
                    {activePreset === 'm365-basic' && <p className="admin-warning wide">Microsoft is retiring password (Basic) authentication for SMTP in Exchange Online. If sign-in fails, switch to the <strong>Microsoft 365</strong> (OAuth 2.0) option.</p>}
                    <Field label="SMTP host" error={errors.host}>{input('host', { placeholder: 'smtp.example.com', autoComplete: 'off' })}</Field>
                    <Field label="Port" error={errors.port}>{input('port', { type: 'number', min: 1, max: 65535 })}</Field>
                    <Field label="Encryption" error={errors.encryption}>
                        <select value={form.data.encryption} onChange={e => form.setData('encryption', e.target.value)}>
                            <option value="tls">STARTTLS (port 587)</option>
                            <option value="ssl">SSL/TLS (port 465)</option>
                        </select>
                    </Field>
                    <Field label="Username" error={errors.username}>{input('username', { autoComplete: 'off' })}</Field>
                    <Field label="Password" error={errors.password} hint="Stored encrypted.">
                        {input('password', { type: 'password', autoComplete: 'new-password', placeholder: secretPlaceholder(mail.has_password) })}
                    </Field>
                </>}

                {form.data.mailer !== 'log' && <>
                    <Field label="From address" error={errors.from_address} hint={form.data.mailer === 'microsoft365' ? 'Must be the mailbox above, or an address it has Send As rights for.' : undefined}>
                        {input('from_address', { type: 'email', placeholder: 'contact@safpartners.ae' })}
                    </Field>
                    <Field label="From name" error={errors.from_name}>{input('from_name')}</Field>
                </>}
                <Field label="Send website enquiries to" error={errors.contact_to} hint="Leave blank to use MAIL_CONTACT_TO from .env." wide>
                    {input('contact_to', { type: 'email', placeholder: 'contact@safpartners.ae' })}
                </Field>
                <div><button className="admin-primary" disabled={form.processing}>{form.processing ? 'Saving…' : 'Save email settings'}</button></div>
            </form>
        </section>

        <section className="admin-panel">
            <h2>Send a test email</h2>
            <p>Save your settings first, then send a test to confirm delivery.</p>
            {mail.last_tested_at && <p className={`mail-status ${mail.last_test_status === 'ok' ? 'ok' : 'failed'}`}>
                Last test {new Date(mail.last_tested_at).toLocaleString()}: {mail.last_test_status === 'ok' ? 'succeeded' : 'failed'} — {mail.last_test_message}
            </p>}
            <form onSubmit={sendTest} className="mail-test">
                <label>Recipient<input type="email" value={test.data.to} onChange={e => test.setData('to', e.target.value)} aria-invalid={!!test.errors.to} /></label>
                <button className="admin-primary" disabled={test.processing}>{test.processing ? 'Sending…' : 'Send test email'}</button>
            </form>
            {test.errors.to && <small className="form-error" role="alert">{test.errors.to}</small>}
        </section>
    </>;
}
