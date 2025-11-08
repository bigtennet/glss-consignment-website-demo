<?php

declare(strict_types=1);

namespace SwiftShip;

class Mailer
{
    /**
     * @var array<string, string>
     */
    private array $settings;

    /**
     * @param array<string, string> $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function send(string $to, string $subject, string $message, ?string $fromEmail = null, ?string $fromName = null): bool
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $fromEmail = $fromEmail
            ?: ($this->settings['mail_from_email']
                ?? $this->settings['support_email']
                ?? 'no-reply@localhost.test');

        $fromName = $fromName
            ?: ($this->settings['mail_from_name']
                ?? $this->settings['site_name']
                ?? 'GLSS');

        $headers = [
            sprintf('From: %s <%s>', $fromName, $fromEmail),
            sprintf('Reply-To: %s', $fromEmail),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
        ];

        if (($this->settings['mail_use_smtp'] ?? '0') === '1') {
            if (!empty($this->settings['mail_smtp_host'])) {
                ini_set('SMTP', $this->settings['mail_smtp_host']);
            }

            if (!empty($this->settings['mail_smtp_port'])) {
                ini_set('smtp_port', (string) $this->settings['mail_smtp_port']);
            }

            if (!empty($fromEmail)) {
                ini_set('sendmail_from', $fromEmail);
            }

            // Note: PHP's built-in mail() does not support SMTP authentication.
            // Configure your server's MTA (sendmail/postfix) for authenticated relay if required.
        }

        return mail($to, $subject, $message, implode("\r\n", $headers));
    }
}


