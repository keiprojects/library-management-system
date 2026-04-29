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

