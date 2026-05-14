<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/helpers.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}

$parsedEnv = parse_env_lines(<<<ENV
APP_NAME="Library MS"
DB_HOST=localhost
MAIL_PASSWORD="abc=123"
# comment

ENV);

assert_same('Library MS', $parsedEnv['APP_NAME'] ?? null, 'Quoted .env values should be parsed.');
assert_same('localhost', $parsedEnv['DB_HOST'] ?? null, 'Unquoted .env values should be parsed.');
assert_same('abc=123', $parsedEnv['MAIL_PASSWORD'] ?? null, 'Values containing "=" should be preserved.');

$courses = course_options();
assert_true(in_array('BSC', $courses, true), 'Course options should include BSC.');
assert_true(in_array('BSIT', $courses, true), 'Course options should include BSIT.');

$yearLevels = year_level_options();
assert_same(['1st Year', '2nd Year', '3rd Year', '4th Year', '5th Year', 'Other'], $yearLevels, 'Year level options should match the registration dropdown.');

assert_true(email_requires_verification('presenter@example.com', 'selected', 'presenter@example.com'), 'Selected verification mode should require the presenter email.');
assert_true(!email_requires_verification('student@example.com', 'selected', 'presenter@example.com'), 'Selected verification mode should skip other emails.');
assert_true(email_requires_verification('student@example.com', 'all', ''), 'All verification mode should require every borrower email.');

$borrower = [
    'role' => 'borrower',
    'email' => 'presenter@example.com',
    'email_verified_at' => null,
];
assert_true(!user_can_log_in($borrower, 'selected', 'presenter@example.com'), 'Unverified selected presenter account should not log in.');

$borrower['email_verified_at'] = '2026-05-14 12:00:00';
assert_true(user_can_log_in($borrower, 'selected', 'presenter@example.com'), 'Verified presenter account should log in.');

$pendingBorrower = [
    'role' => 'borrower',
    'email' => 'student@example.com',
    'email_verified_at' => '2026-05-14 12:00:00',
    'approval_status' => 'pending',
];
assert_true(!user_can_log_in($pendingBorrower, 'none', ''), 'Pending borrower accounts should stay blocked until admin approval.');

$approvedBorrower = $pendingBorrower;
$approvedBorrower['approval_status'] = 'approved';
assert_true(user_can_log_in($approvedBorrower, 'none', ''), 'Approved borrower accounts should be able to log in.');

$rejectedBorrower = $pendingBorrower;
$rejectedBorrower['approval_status'] = 'rejected';
assert_true(!user_can_log_in($rejectedBorrower, 'none', ''), 'Rejected borrower accounts should stay blocked.');

$admin = [
    'role' => 'admin',
    'email' => 'admin@example.com',
    'email_verified_at' => null,
];
assert_true(user_can_log_in($admin, 'selected', 'presenter@example.com'), 'Admin accounts should not be blocked by borrower verification rules.');

echo "feature_smoke.php passed\n";
