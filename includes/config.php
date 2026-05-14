<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
load_env_file(dirname(__DIR__) . '/.env');

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
define('APP_URL', env_value('APP_URL', detect_app_url()));
define('APP_PUBLIC_URL', env_value('APP_PUBLIC_URL', ''));
define('APP_TIMEZONE', env_value('APP_TIMEZONE', 'Asia/Manila'));

define('DB_HOST', env_value('DB_HOST', '127.0.0.1'));
define('DB_PORT', env_value('DB_PORT', '3306'));
define('DB_NAME', env_value('DB_NAME', 'library_management_system'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));

define('MAIL_HOST', env_value('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', env_value('MAIL_PORT', '587'));
define('MAIL_USERNAME', env_value('MAIL_USERNAME', ''));
define('MAIL_PASSWORD', env_value('MAIL_PASSWORD', ''));
define('MAIL_FROM_EMAIL', env_value('MAIL_FROM_EMAIL', ''));
define('MAIL_FROM_NAME', env_value('MAIL_FROM_NAME', APP_NAME));
define('MAIL_ENCRYPTION', strtolower(env_value('MAIL_ENCRYPTION', 'tls')));

define('EMAIL_VERIFICATION_MODE', strtolower(env_value('EMAIL_VERIFICATION_MODE', 'none')));
define('EMAIL_VERIFICATION_EMAILS', env_value('EMAIL_VERIFICATION_EMAILS', ''));
define('EMAIL_VERIFICATION_EXPIRES_HOURS', env_value('EMAIL_VERIFICATION_EXPIRES_HOURS', '24'));

/**
 * Penalty charged per day after the due date.
 */
define('PENALTY_PER_DAY', 5.00);
