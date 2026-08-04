<?php

namespace App\Services\Mail;

use App\Support\Settings;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class SettingsMailService
{
    /**
     * Check if SMTP is configured enough to send mails.
     */
    public function isConfigured(): bool
    {
        $host      = Settings::get('smtp', 'host');
        $port      = Settings::get('smtp', 'port');
        $fromEmail = Settings::get('smtp', 'from_email');

        return ! empty($host) && ! empty($port) && ! empty($fromEmail);
    }

    /**
     * Send a mailable using SMTP settings from the settings table.
     * If SMTP is not configured, it silently skips.
     */
    public function sendTo(string $email, Mailable $mailable): void
    {
        // If SMTP is not configured, just skip without throwing error
        if (! $this->isConfigured()) {
            return;
        }

        // Read SMTP settings from DB
        $driver     = Settings::get('smtp', 'driver', 'smtp') ?: 'smtp';
        $host       = Settings::get('smtp', 'host', config('mail.mailers.smtp.host'));
        $port       = Settings::get('smtp', 'port', config('mail.mailers.smtp.port'));
        $encryption = Settings::get('smtp', 'encryption', config('mail.mailers.smtp.encryption'));
        $username   = Settings::get('smtp', 'username', config('mail.mailers.smtp.username'));
        $password   = Settings::get('smtp', 'password', config('mail.mailers.smtp.password'));

        $fromEmail  = Settings::get('smtp', 'from_email', config('mail.from.address'));
        $fromName   = Settings::get('smtp', 'from_name', config('mail.from.name'));

        // Build runtime mailer config (separate mailer so default stays intact)
        config([
            'mail.mailers.settings_smtp' => [
                'transport'  => $driver,
                'host'       => $host,
                'port'       => $port,
                'encryption' => $encryption ?: null,
                'username'   => $username,
                'password'   => $password,
                'timeout'    => null,
                'auth_mode'  => null,
            ],
            'mail.from.address' => $fromEmail,
            'mail.from.name'    => $fromName,
        ]);

        try {
            Mail::mailer('settings_smtp')
                ->to($email)
                ->send($mailable);
        } catch (\Throwable $e) {
            // Optional: log but never break the app
            // logger()->warning('Settings SMTP mail send failed: ' . $e->getMessage());
        }
    }
}
