<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

redirect_if_authenticated();

$form = [
    'name' => '',
    'email' => '',
    'student_id' => '',
    'course' => '',
    'year_level' => '',
    'contact_info' => '',
];
$errors = [];
$uploadedStudentIdPath = null;

if (is_post()) {
    $form = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'student_id' => trim($_POST['student_id'] ?? ''),
        'course' => trim($_POST['course'] ?? ''),
        'year_level' => trim($_POST['year_level'] ?? ''),
        'contact_info' => trim($_POST['contact_info'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    foreach ($form as $label => $value) {
        if ($value === '') {
            $errors[] = ucfirst(str_replace('_', ' ', $label)) . ' is required.';
        }
    }

    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    }

    if (!value_in_options($form['course'], course_options())) {
        $errors[] = 'Please choose a valid course.';
    }

    if (!value_in_options($form['year_level'], year_level_options())) {
        $errors[] = 'Please choose a valid year level.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Password confirmation does not match.';
    }

    if (!isset($_FILES['student_id_card']) || (int) ($_FILES['student_id_card']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Student ID image is required for admin verification.';
    }

    if ($errors === []) {
        $uploadResult = store_student_id_upload($_FILES['student_id_card']);

        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $uploadedStudentIdPath = $uploadResult['path'];
        }
    }

    if ($errors === []) {
        $requiresVerification = email_requires_verification($form['email']);
        $result = create_borrower_account($form + [
            'password' => $password,
            'approval_status' => 'pending',
            'mark_verified' => !$requiresVerification,
            'student_id_card_path' => $uploadedStudentIdPath,
        ]);

        if ($result['success']) {
            if ($requiresVerification) {
                $tokenResult = issue_email_verification((int) $result['user_id']);

                if (!$tokenResult['success'] || $tokenResult['token'] === null) {
                    flash('error', 'Registration completed, but verification setup failed. ' . $tokenResult['message']);
                    redirect('resend-verification.php');
                } else {
                    $user = find_user_by_id((int) $result['user_id']);
                    $mailResult = $user !== null ? send_verification_email($user, $tokenResult['token']) : [
                        'success' => false,
                        'message' => 'Unable to load the new borrower account for email sending.',
                    ];

                    if ($mailResult['success']) {
                        flash('success', 'Registration successful. Check your email if verification is enabled, then wait for admin approval of your student ID before logging in.');
                        redirect('login.php');
                    }

                    flash('error', 'Registration completed, but the verification email could not be sent. ' . $mailResult['message']);
                    redirect('resend-verification.php');
                }
            } else {
                flash('success', 'Registration successful. Your borrower account is now pending admin approval after student ID review.');
                redirect('login.php');
            }
        }

        if (!$result['success']) {
            if ($uploadedStudentIdPath !== null) {
                $savedFile = __DIR__ . '/' . $uploadedStudentIdPath;

                if (is_file($savedFile)) {
                    unlink($savedFile);
                }
            }

            $errors[] = $result['message'];
        }
    }
}

render_auth_start('Register');
?>
<div class="panel">
    <div class="mb-8">
        <p class="text-sm uppercase tracking-[0.3em] text-library-ink/50">Borrower Registration</p>
        <h2 class="mt-3 text-3xl font-semibold text-library-ink">Create a borrower account</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">Borrowers can register here to access the catalog and monitor active loans.</p>
    </div>

    <?php if ($errors !== []): ?>
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="grid gap-1">
                <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="grid gap-5 md:grid-cols-2">
        <div class="md:col-span-2">
            <label for="name" class="label-text">Full Name</label>
            <input type="text" id="name" name="name" class="input-field" value="<?= e($form['name']) ?>" placeholder="Juan Dela Cruz">
        </div>
        <div>
            <label for="email" class="label-text">Email Address</label>
            <input type="email" id="email" name="email" class="input-field" value="<?= e($form['email']) ?>" placeholder="borrower@school.edu">
        </div>
        <div>
            <label for="student_id" class="label-text">Student ID</label>
            <input type="text" id="student_id" name="student_id" class="input-field" value="<?= e($form['student_id']) ?>" placeholder="2026-000123">
        </div>
        <div>
            <label for="course" class="label-text">Course</label>
            <select id="course" name="course" class="input-field">
                <option value="">Select a course</option>
                <?php foreach (course_options() as $course): ?>
                    <option value="<?= e($course) ?>" <?= selected($form['course'], $course) ?>><?= e($course) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="year_level" class="label-text">Year Level</label>
            <select id="year_level" name="year_level" class="input-field">
                <option value="">Select a year level</option>
                <?php foreach (year_level_options() as $yearLevel): ?>
                    <option value="<?= e($yearLevel) ?>" <?= selected($form['year_level'], $yearLevel) ?>><?= e($yearLevel) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="md:col-span-2">
            <label for="contact_info" class="label-text">Contact Information</label>
            <input type="text" id="contact_info" name="contact_info" class="input-field" value="<?= e($form['contact_info']) ?>" placeholder="+1 555 010 1234">
        </div>
        <div class="md:col-span-2">
            <label for="student_id_card" class="label-text">Student ID Image</label>
            <input type="file" id="student_id_card" name="student_id_card" class="input-field" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <p class="mt-2 text-xs text-slate-500">Upload a clear photo or scan of your school ID for admin verification.</p>
        </div>
        <div>
            <label for="password" class="label-text">Password</label>
            <input type="password" id="password" name="password" class="input-field" placeholder="At least 6 characters">
        </div>
        <div>
            <label for="confirm_password" class="label-text">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="input-field" placeholder="Repeat your password">
        </div>
        <div class="md:col-span-2">
            <button type="submit" class="btn-primary w-full">Register Borrower Account</button>
        </div>
    </form>

    <div class="mt-6 text-sm text-slate-600">
        Already have an account?
        <a href="<?= e(url('login.php')) ?>" class="font-semibold text-library-ink">Log in here</a>
    </div>
</div>
<?php
render_auth_end();

