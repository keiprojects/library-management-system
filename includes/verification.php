<?php

declare(strict_types=1);

/**
 * Creates a verification token for a borrower account.
 *
 * @return array{success:bool,message:string,token:?string}
 */
function issue_email_verification(int $userId): array
{
    $token = bin2hex(random_bytes(32));
    $hash = password_hash($token, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . (int) EMAIL_VERIFICATION_EXPIRES_HOURS . ' hours'));

    $statement = db()->prepare(
        'UPDATE users
         SET verification_token_hash = :verification_token_hash,
             verification_token_expires_at = :verification_token_expires_at,
             email_verified_at = NULL
         WHERE id = :id'
    );

    $success = $statement->execute([
        'verification_token_hash' => $hash,
        'verification_token_expires_at' => $expiresAt,
        'id' => $userId,
    ]);

    if (!$success) {
        return [
            'success' => false,
            'message' => 'Unable to create an email verification token.',
            'token' => null,
        ];
    }

    return [
        'success' => true,
        'message' => 'Verification token created.',
        'token' => $token,
    ];
}

/**
 * Marks a user as verified using the token from the verification email.
 */
function verify_user_email(string $token): array
{
    $statement = db()->query(
        'SELECT id, verification_token_hash, verification_token_expires_at
         FROM users
         WHERE verification_token_hash IS NOT NULL'
    );

    foreach ($statement->fetchAll() as $user) {
        if (!password_verify($token, $user['verification_token_hash'])) {
            continue;
        }

        if (
            empty($user['verification_token_expires_at'])
            || strtotime($user['verification_token_expires_at']) < time()
        ) {
            return [
                'success' => false,
                'message' => 'This verification link has expired. Please request a new one.',
            ];
        }

        $update = db()->prepare(
            'UPDATE users
             SET email_verified_at = NOW(),
                 verification_token_hash = NULL,
                 verification_token_expires_at = NULL
             WHERE id = :id'
        );
        $update->execute(['id' => $user['id']]);

        return [
            'success' => true,
            'message' => 'Your email address has been verified. You can now log in.',
        ];
    }

    return [
        'success' => false,
        'message' => 'The verification link is invalid or has already been used.',
    ];
}

/**
 * Builds the verification URL sent to the borrower.
 */
function build_verification_link(string $token): string
{
    return absolute_url('verify-email.php?token=' . urlencode($token));
}

/**
 * Sends the borrower verification email.
 */
function send_verification_email(array $user, string $token): array
{
    $verificationLink = build_verification_link($token);
    $subject = 'Verify your Library Management System account';
    $body = sprintf(
        '<p>Hello %s,</p>
        <p>Click the link below to verify your borrower account before logging in:</p>
        <p><a href="%s">%s</a></p>
        <p>This link expires in %d hours.</p>',
        e($user['name'] ?? 'Borrower'),
        e($verificationLink),
        e($verificationLink),
        (int) EMAIL_VERIFICATION_EXPIRES_HOURS
    );

    return send_smtp_mail(
        (string) ($user['email'] ?? ''),
        (string) ($user['name'] ?? ''),
        $subject,
        $body
    );
}
