import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Field = 'name' | 'email' | 'password' | 'password_confirmation' | 'setup_token';

export default function Install({ requiresToken }: { requiresToken: boolean }) {
    const form = useForm({ name: '', email: '', password: '', password_confirmation: '', setup_token: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('setup.store'), { onFinish: () => form.reset('password', 'password_confirmation') });
    };

    const fields: [Field, string, string, string][] = [
        ...(requiresToken ? [['setup_token', 'Setup key', 'password', 'off'] as [Field, string, string, string]] : []),
        ['name', 'Full name', 'text', 'name'],
        ['email', 'Email address', 'email', 'username'],
        ['password', 'Password', 'password', 'new-password'],
        ['password_confirmation', 'Confirm password', 'password', 'new-password'],
    ];

    return <div className="simple-page">
        <Head title="Set up SAF Partners" />
        <span className="simple-logo"><img src="/images/saf-partners-logo.png" alt="SAF Partners" /></span>
        <main className="setup-card">
            <span className="eyebrow">First-time setup</span>
            <h1>Create the super admin</h1>
            <p>The website will be available once the super administrator account has been created. This account has full access to the content management system.</p>
            <form onSubmit={submit} className="setup-form" noValidate>
                {fields.map(([name, label, type, autoComplete]) => <label key={name}>
                    <span>{label}</span>
                    <input type={type} name={name} value={form.data[name]} autoComplete={autoComplete} required
                        autoFocus={name === fields[0][0]} onChange={e => form.setData(name, e.target.value)}
                        aria-invalid={!!form.errors[name]} aria-describedby={form.errors[name] ? `${name}-error` : undefined} />
                    {form.errors[name] && <small id={`${name}-error`}>{form.errors[name]}</small>}
                </label>)}
                <p className="setup-hint">Use at least 10 characters, including letters and numbers.</p>
                <button type="submit" className="btn btn-primary" disabled={form.processing}>{form.processing ? 'Creating…' : 'Create account & open site'}</button>
            </form>
        </main>
    </div>;
}
