<?php

declare(strict_types=1);

/**
 * Tries to detect the base URL of the project folder automatically.
 *
 * This helps the app work both with `php -S` and with folders inside
 * tools like XAMPP, WAMP, Laragon, or Herd.
 */
function detect_app_url(): string
{
  $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
  $projectRoot = realpath(dirname(__DIR__));

  if ($documentRoot === false || $projectRoot === false) {
    return '';
  }

  $documentRoot = str_replace('\\', '/', $documentRoot);
  $projectRoot = str_replace('\\', '/', $projectRoot);

  if (!str_starts_with($projectRoot, $documentRoot)) {
    return '';
  }

  $relativePath = trim(substr($projectRoot, strlen($documentRoot)), '/');

  return $relativePath === '' ? '' : '/' . $relativePath;
}

/**
 * Core application settings.
 *
 * Students can update these values when they set up the project
 * on their own computer or inside a different local server folder.
 */
define('APP_NAME', 'Library Management System with Role-Based Authentication');
define('APP_URL', detect_app_url());
define('APP_TIMEZONE', 'Asia/Manila');

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'library_management_system');
define('DB_USER', 'root');
define('DB_PASS', 'password');

/**
 * Penalty charged per day after the due date.
 */
define('PENALTY_PER_DAY', 5.00);