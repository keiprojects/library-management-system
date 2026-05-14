<?php

declare(strict_types=1);

/**
 * Sends an email using a basic SMTP client compatible with Gmail app passwords.
 *
 * @return array{success:bool,message:string}
 */
function send_smtp_mail(string $toEmail, string $toName, string $subject, string $htmlBody): array
{
    if (
        MAIL_HOST === ''
        || MAIL_USERNAME === ''
        || MAIL_PASSWORD === ''
        || MAIL_FROM_EMAIL === ''
    ) {
        return [
            'success' => false,
            'message' => 'SMTP settings are incomplete. Please update your .env file.',
        ];
    }

    $transport = MAIL_ENCRYPTION === 'ssl' ? 'ssl://' : '';
    $socket = @stream_socket_client(
        $transport . MAIL_HOST . ':' . MAIL_PORT,
        $errorNumber,
        $errorMessage,
        30
    );

    if (!is_resource($socket)) {
        return [
            'success' => false,
            'message' => 'Unable to connect to the SMTP server: ' . $errorMessage,
        ];
    }

    stream_set_timeout($socket, 30);

    try {
        smtp_expect($socket, [220]);
        smtp_command($socket, 'EHLO localhost', [250]);

        if (MAIL_ENCRYPTION === 'tls') {
            smtp_command($socket, 'STARTTLS', [220]);

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('Failed to start TLS encryption.');
            }

            smtp_command($socket, 'EHLO localhost', [250]);
        }

        smtp_command($socket, 'AUTH LOGIN', [334]);
        smtp_command($socket, base64_encode(MAIL_USERNAME), [334]);
        smtp_command($socket, base64_encode(MAIL_PASSWORD), [235]);
        smtp_command($socket, 'MAIL FROM:<' . MAIL_FROM_EMAIL . '>', [250]);
        smtp_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtp_command($socket, 'DATA', [354]);

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . smtp_format_address(MAIL_FROM_EMAIL, MAIL_FROM_NAME),
            'To: ' . smtp_format_address($toEmail, $toName),
            'Subject: ' . $subject,
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n" . normalize_smtp_body($htmlBody) . "\r\n.";
        fwrite($socket, $message . "\r\n");
        smtp_expect($socket, [250]);
        smtp_command($socket, 'QUIT', [221]);
    } catch (Throwable $exception) {
        fclose($socket);

        return [
            'success' => false,
            'message' => $exception->getMessage(),
        ];
    }

    fclose($socket);

    return [
        'success' => true,
        'message' => 'Email sent successfully.',
    ];
}

/**
 * Sends one SMTP command and validates the response code.
 */
function smtp_command($socket, string $command, array $expectedCodes): string
{
    fwrite($socket, $command . "\r\n");

    return smtp_expect($socket, $expectedCodes);
}

/**
 * Reads an SMTP response and validates the response code.
 */
function smtp_expect($socket, array $expectedCodes): string
{
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;

        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    $statusCode = (int) substr($response, 0, 3);

    if (!in_array($statusCode, $expectedCodes, true)) {
        throw new RuntimeException('SMTP error: ' . trim($response));
    }

    return $response;
}

/**
 * Formats a display name and email into a mailbox header.
 */
function smtp_format_address(string $email, string $name): string
{
    $name = trim($name);

    if ($name === '') {
        return $email;
    }

    return sprintf('"%s" <%s>', addcslashes($name, "\"\\"), $email);
}

/**
 * Normalizes the body for SMTP data mode.
 */
function normalize_smtp_body(string $body): string
{
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $body = str_replace("\n.", "\n..", $body);

    return str_replace("\n", "\r\n", $body);
}
