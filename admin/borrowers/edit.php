<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

require_admin();

$userId = (int) ($_GET['id'] ?? 0);
$borrower = get_borrower_profile($userId);

if ($borrower === null) {
    flash('error', 'Borrower not found.');
    redirect('admin/borrowers/index.php');
}

$form = [
    'name' => $borrower['name'],
    'email' => $borrower['email'],
    'student_id' => $borrower['student_id'],
    'course' => $borrower['course'],
    'year_level' => $borrower['year_level'],
    'contact_info' => $borrower['contact_info'],
    'approval_status' => $borrower['approval_status'],
];
$errors = [];

if (is_post()) {
    $form = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'student_id' => trim($_POST['student_id'] ?? ''),
        'course' => trim($_POST['course'] ?? ''),
        'year_level' => trim($_POST['year_level'] ?? ''),
        'contact_info' => trim($_POST['contact_info'] ?? ''),
        'approval_status' => trim($_POST['approval_status'] ?? ''),
    ];
    $password = trim($_POST['password'] ?? '');

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

    if (!value_in_options($form['approval_status'], borrower_approval_options())) {
        $errors[] = 'Please choose a valid approval status.';
    }

    if ($password !== '' && strlen($password) < 6) {
        $errors[] = 'New password must be at least 6 characters long.';
    }

    $studentIdCardPath = $borrower['student_id_card_path'];
    $replacedStudentIdPath = null;

    if (isset($_FILES['student_id_card']) && (int) ($_FILES['student_id_card']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = store_student_id_upload($_FILES['student_id_card']);

        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            $studentIdCardPath = $uploadResult['path'];
            $replacedStudentIdPath = $borrower['student_id_card_path'];
        }
    }

    if ($errors === []) {
        $result = update_borrower($userId, $form + [
            'password' => $password,
            'student_id_card_path' => $studentIdCardPath,
        ]);

        if ($result['success']) {
            if ($replacedStudentIdPath !== null && $replacedStudentIdPath !== $studentIdCardPath) {
                $oldFile = dirname(__DIR__, 2) . '/' . $replacedStudentIdPath;

                if (is_file($oldFile)) {
                    unlink($oldFile);
                }
            }

            flash('success', $result['message']);
            redirect('admin/borrowers/index.php');
        }

        if ($studentIdCardPath !== $borrower['student_id_card_path'] && is_string($studentIdCardPath)) {
            $newFile = dirname(__DIR__, 2) . '/' . $studentIdCardPath;

            if (is_file($newFile)) {
                unlink($newFile);
            }
        }

        $errors[] = $result['message'];
    }
}

render_app_start('Edit Borrower', 'borrowers');
?>
<section class="panel max-w-4xl">
    <div class="mb-6">
        <p class="text-sm uppercase tracking-[0.25em] text-slate-500">Borrower Form</p>
        <h3 class="mt-2 text-2xl font-semibold text-library-ink">Update borrower details</h3>
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
            <input type="text" id="name" name="name" class="input-field" value="<?= e($form['name']) ?>">
        </div>
        <div>
            <label for="email" class="label-text">Email Address</label>
            <input type="email" id="email" name="email" class="input-field" value="<?= e($form['email']) ?>">
        </div>
        <div>
            <label for="student_id" class="label-text">Student ID</label>
            <input type="text" id="student_id" name="student_id" class="input-field" value="<?= e($form['student_id']) ?>">
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
            <input type="text" id="contact_info" name="contact_info" class="input-field" value="<?= e($form['contact_info']) ?>">
        </div>
        <div>
            <label for="approval_status" class="label-text">Approval Status</label>
            <select id="approval_status" name="approval_status" class="input-field">
                <?php foreach (borrower_approval_options() as $approvalStatus): ?>
                    <option value="<?= e($approvalStatus) ?>" <?= selected($form['approval_status'], $approvalStatus) ?>><?= e(ucfirst($approvalStatus)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="student_id_card" class="label-text">Replace Student ID Image</label>
            <input type="file" id="student_id_card" name="student_id_card" class="input-field" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            <?php if (!empty($borrower['student_id_card_path'])): ?>
                <p class="mt-2 text-xs text-slate-500">
                    Current file:
                    <a href="<?= e(url((string) $borrower['student_id_card_path'])) ?>" target="_blank" rel="noopener noreferrer" class="font-semibold text-library-ink">View uploaded ID</a>
                </p>
            <?php else: ?>
                <p class="mt-2 text-xs text-slate-500">No student ID file uploaded for this borrower.</p>
            <?php endif; ?>
        </div>
        <div class="md:col-span-2">
            <label for="password" class="label-text">New Password (Optional)</label>
            <input type="password" id="password" name="password" class="input-field" placeholder="Leave blank to keep the current password">
        </div>
        <div class="md:col-span-2 flex flex-wrap gap-3">
            <button type="submit" class="btn-primary">Update Borrower</button>
            <a href="<?= e(url('admin/borrowers/index.php')) ?>" class="btn-secondary">Back to Borrowers</a>
        </div>
    </form>
</section>
<?php
render_app_end();

