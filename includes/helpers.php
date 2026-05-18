<?php

declare(strict_types=1);

/**
 * Escapes output so it is safe to display in HTML.
 *
 * @param mixed $value Value that will be shown on the page.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Builds an application URL based on APP_URL.
 */
function url(string $path = ''): string
{
    $base = rtrim(APP_URL, '/');
    $cleanPath = ltrim($path, '/');

    return $cleanPath === '' ? $base : $base . '/' . $cleanPath;
}

/**
 * Builds an absolute URL for links that leave the browser, such as email verification.
 */
function absolute_url(string $path = ''): string
{
    if (APP_PUBLIC_URL !== '') {
        $base = rtrim(APP_PUBLIC_URL, '/');
        $cleanPath = ltrim($path, '/');

        return $cleanPath === '' ? $base : $base . '/' . $cleanPath;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . url($path);
}

/**
 * Redirects the browser to another page and stops the script.
 */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Checks whether the current request is a POST form submission.
 */
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

/**
 * Stores a short feedback message in the session.
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Gets and removes the current flash message from the session.
 *
 * @return array{type:string,message:string}|null
 */
function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;

    if ($flash !== null) {
        unset($_SESSION['flash']);
    }

    return $flash;
}

/**
 * Formats a date string into a more readable style.
 */
function format_date(?string $date): string
{
    if (empty($date)) {
        return 'Not yet returned';
    }

    return date('M d, Y', strtotime($date));
}

/**
 * Formats money values with two decimal places.
 */
function format_money(float $amount): string
{
    return 'PHP ' . number_format($amount, 2);
}

/**
 * Keeps form values selected after validation errors.
 */
function selected(string $currentValue, string $expectedValue): string
{
    return $currentValue === $expectedValue ? 'selected' : '';
}

/**
 * Keeps checkbox values checked after validation errors.
 */
function checked(bool $condition): string
{
    return $condition ? 'checked' : '';
}

/**
 * Shared approval status options for borrower review.
 *
 * @return list<string>
 */
function borrower_approval_options(): array
{
    return [
        'pending',
        'approved',
        'rejected',
    ];
}

/**
 * Shared account status options for user access control.
 *
 * @return list<string>
 */
function account_status_options(): array
{
    return [
        'active',
        'inactive',
    ];
}

/**
 * Shared user role options for super admin user management.
 *
 * @return list<string>
 */
function user_role_options(): array
{
    return [
        'super_admin',
        'admin',
        'borrower',
    ];
}

/**
 * Converts internal role values into user-friendly labels.
 */
function format_role(string $role): string
{
    return ucwords(str_replace('_', ' ', $role));
}

/**
 * Shared course options for borrower forms.
 *
 * @return list<string>
 */
function course_options(): array
{
    return [
        'BSIT',
        'BSCS',
        'BSC',
        'BSIS',
        'BSEMC',
        'BSBA',
        'BSA',
        'BSHM',
        'BSTM',
        'BSEd',
        'BEEd',
        'BSN',
        'Other',
    ];
}

/**
 * Shared year level options for borrower forms.
 *
 * @return list<string>
 */
function year_level_options(): array
{
    return [
        '1st Year',
        '2nd Year',
        '3rd Year',
        '4th Year',
        '5th Year',
        'Other',
    ];
}

/**
 * Checks whether a submitted value is one of the allowed dropdown options.
 */
function value_in_options(string $value, array $options): bool
{
    return in_array($value, $options, true);
}

/**
 * Checks whether a borrower email should go through verification.
 */
function email_requires_verification(
    string $email,
    string $mode = EMAIL_VERIFICATION_MODE,
    string $selectedEmails = EMAIL_VERIFICATION_EMAILS
): bool {
    $mode = strtolower(trim($mode));

    if ($mode === 'none') {
        return false;
    }

    if ($mode === 'all') {
        return true;
    }

    $targets = array_values(array_filter(array_map(
        static fn(string $item): string => strtolower(trim($item)),
        explode(',', $selectedEmails)
    )));

    return in_array(strtolower(trim($email)), $targets, true);
}

/**
 * Checks if a user may log in based on role and verification status.
 */
function user_can_log_in(
    array $user,
    string $mode = EMAIL_VERIFICATION_MODE,
    string $selectedEmails = EMAIL_VERIFICATION_EMAILS
): bool {
    if (($user['account_status'] ?? 'active') !== 'active') {
        return false;
    }

    if (($user['role'] ?? '') !== 'borrower') {
        return true;
    }

    if (($user['approval_status'] ?? 'approved') !== 'approved') {
        return false;
    }

    if (!email_requires_verification((string) ($user['email'] ?? ''), $mode, $selectedEmails)) {
        return true;
    }

    return !empty($user['email_verified_at']);
}

/**
 * Returns the borrower login block message that best matches the account state.
 */
function borrower_login_block_message(array $user): string
{
    if (($user['account_status'] ?? 'active') !== 'active') {
        return 'Your account has been deactivated. Please contact the library admin.';
    }

    $approvalStatus = (string) ($user['approval_status'] ?? 'approved');

    if ($approvalStatus === 'pending') {
        return 'Your borrower account is waiting for admin approval after student ID review.';
    }

    if ($approvalStatus === 'rejected') {
        return 'Your borrower account was rejected during student ID review. Please contact the library admin.';
    }

    return 'Your account is registered, but the email address still needs verification before borrower login.';
}

