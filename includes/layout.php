<?php

declare(strict_types=1);

/**
 * Returns the Tailwind classes for a flash message type.
 */
function flash_class(string $type): string
{
    return match ($type) {
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'error' => 'border-rose-200 bg-rose-50 text-rose-800',
        default => 'border-slate-200 bg-slate-50 text-slate-700',
    };
}

/**
 * Returns the Tailwind classes used for record status badges.
 */
function status_badge_class(string $status): string
{
    return match ($status) {
        'borrowed' => 'bg-sky-100 text-sky-700',
        'returned' => 'bg-emerald-100 text-emerald-700',
        'overdue' => 'bg-amber-100 text-amber-800',
        default => 'bg-slate-100 text-slate-700',
    };
}

/**
 * Outputs the <head> section and shared styles/scripts.
 */
function render_head(string $title): void
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> | <?= e(APP_NAME) ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['"Segoe UI"', 'Tahoma', 'Geneva', 'Verdana', 'sans-serif'],
                        },
                        colors: {
                            library: {
                                ink: '#102033',
                                gold: '#d2a954',
                                paper: '#f6f2e8',
                                mist: '#e4ebf2',
                            }
                        },
                        boxShadow: {
                            panel: '0 18px 40px rgba(15, 23, 42, 0.08)',
                        }
                    }
                }
            };
        </script>
        <style type="text/tailwindcss">
            @layer components {
                .btn-primary {
                    @apply inline-flex items-center justify-center rounded-xl bg-library-ink px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800;
                }
                .btn-secondary {
                    @apply inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50;
                }
                .btn-danger {
                    @apply inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700;
                }
                .input-field {
                    @apply w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-library-gold focus:ring-2 focus:ring-library-gold/30;
                }
                .label-text {
                    @apply mb-2 block text-sm font-medium text-slate-700;
                }
                .panel {
                    @apply rounded-3xl border border-white/60 bg-white/90 p-6 shadow-panel backdrop-blur;
                }
                .stat-block {
                    @apply rounded-3xl border border-slate-200 bg-white p-5 shadow-panel;
                }
                .data-table {
                    @apply min-w-full divide-y divide-slate-200 text-sm;
                }
                .nav-link {
                    @apply flex items-center rounded-2xl px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-white hover:text-library-ink;
                }
                .nav-link-active {
                    @apply bg-white text-library-ink shadow-sm;
                }
                .badge {
                    @apply inline-flex rounded-full px-3 py-1 text-xs font-semibold;
                }
            }
        </style>
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(210,169,84,0.18),_transparent_35%),linear-gradient(180deg,_#f8fafc_0%,_#eef2f7_100%)] text-slate-800">
    <?php
}

/**
 * Renders the public auth layout used by login and registration.
 */
function render_auth_start(string $title): void
{
    render_head($title);
    $flash = get_flash();
    ?>
    <div class="min-h-screen lg:grid lg:grid-cols-[1.1fr_0.9fr]">
        <section class="relative hidden overflow-hidden bg-library-ink text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(210,169,84,0.28),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(255,255,255,0.15),_transparent_35%)]"></div>
            <div class="relative px-12 py-10">
                <p class="text-sm uppercase tracking-[0.35em] text-library-gold">Group Project</p>
                <h1 class="mt-6 max-w-xl text-5xl font-semibold leading-tight">Library Management System with Role-Based Authentication</h1>
                <p class="mt-6 max-w-lg text-base leading-7 text-slate-200">
                    Organize books, borrowers, borrowing records, returns, and reports in one student-friendly web application.
                </p>
            </div>
            <div class="relative grid gap-6 px-12 pb-12">
                <div class="rounded-3xl border border-white/10 bg-white/10 p-6 backdrop-blur">
                    <p class="text-sm uppercase tracking-[0.25em] text-library-gold">Included Modules</p>
                    <div class="mt-4 grid gap-3 text-sm text-slate-200">
                        <p>Authentication for admin and borrower roles</p>
                        <p>Book inventory with quantity tracking</p>
                        <p>Borrow and return monitoring with due dates</p>
                        <p>On-screen reports and penalty visibility</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-6 py-10">
            <div class="w-full max-w-xl">
                <div class="mb-8 lg:hidden">
                    <p class="text-sm uppercase tracking-[0.35em] text-library-ink/60">Group Project</p>
                    <h1 class="mt-3 text-3xl font-semibold text-library-ink">Library Management System</h1>
                </div>
                <?php if ($flash !== null): ?>
                    <div class="mb-6 rounded-2xl border px-4 py-3 text-sm <?= e(flash_class($flash['type'])) ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>
    <?php
}

/**
 * Closes the public auth layout.
 */
function render_auth_end(): void
{
    ?>
            </div>
        </section>
    </div>
    </body>
    </html>
    <?php
}

/**
 * Returns the sidebar links based on the current role.
 *
 * @return list<array{label:string,href:string,key:string}>
 */
function navigation_links(string $role): array
{
    if ($role === 'admin') {
        return [
            ['label' => 'Dashboard', 'href' => 'admin/dashboard.php', 'key' => 'dashboard'],
            ['label' => 'Books', 'href' => 'admin/books/index.php', 'key' => 'books'],
            ['label' => 'Borrowers', 'href' => 'admin/borrowers/index.php', 'key' => 'borrowers'],
            ['label' => 'Borrow Books', 'href' => 'admin/transactions/borrow.php', 'key' => 'borrow'],
            ['label' => 'Return Books', 'href' => 'admin/transactions/return.php', 'key' => 'return'],
            ['label' => 'Borrowed Report', 'href' => 'admin/reports/borrowed.php', 'key' => 'borrowed_report'],
            ['label' => 'Returned Report', 'href' => 'admin/reports/returned.php', 'key' => 'returned_report'],
            ['label' => 'Overdue Report', 'href' => 'admin/reports/overdue.php', 'key' => 'overdue_report'],
            ['label' => 'Most Borrowed', 'href' => 'admin/reports/most-borrowed.php', 'key' => 'most_borrowed'],
        ];
    }

    return [
        ['label' => 'Dashboard', 'href' => 'student/dashboard.php', 'key' => 'dashboard'],
        ['label' => 'Available Books', 'href' => 'student/books.php', 'key' => 'books'],
        ['label' => 'Borrowed Books', 'href' => 'student/borrowed.php', 'key' => 'borrowed'],
        ['label' => 'Returned History', 'href' => 'student/history.php', 'key' => 'history'],
    ];
}

/**
 * Renders the main authenticated app layout.
 */
function render_app_start(string $title, string $activeKey): void
{
    render_head($title);
    $user = current_user();
    $role = $user['role'] ?? 'borrower';
    $links = navigation_links($role);
    $flash = get_flash();
    ?>
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 -translate-x-full border-r border-white/60 bg-library-paper px-6 py-8 transition duration-300 lg:static lg:w-auto lg:translate-x-0">
            <div class="mb-8">
                <p class="text-xs uppercase tracking-[0.3em] text-library-ink/50"><?= $role === 'admin' ? 'Admin / Librarian' : 'Student / Borrower' ?></p>
                <h1 class="mt-3 text-2xl font-semibold text-library-ink">Library MS</h1>
                <p class="mt-2 text-sm text-slate-600">Simple, practical, and ready for classroom presentation.</p>
            </div>
            <nav class="grid gap-2">
                <?php foreach ($links as $link): ?>
                    <?php $classes = $link['key'] === $activeKey ? 'nav-link nav-link-active' : 'nav-link'; ?>
                    <a href="<?= e(url($link['href'])) ?>" class="<?= e($classes) ?>"><?= e($link['label']) ?></a>
                <?php endforeach; ?>
            </nav>
            <div class="mt-8 rounded-3xl bg-white p-4 text-sm text-slate-600 shadow-sm">
                <p class="font-semibold text-library-ink"><?= e($user['name'] ?? 'User') ?></p>
                <p class="mt-1"><?= e($user['email'] ?? '') ?></p>
                <a href="<?= e(url('logout.php')) ?>" class="mt-4 inline-flex text-sm font-semibold text-rose-600">Log out</a>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col">
            <header class="sticky top-0 z-30 border-b border-white/60 bg-white/75 px-6 py-4 backdrop-blur lg:px-10">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <button type="button" id="sidebarToggle" class="mb-3 inline-flex rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 lg:hidden">Menu</button>
                        <p class="text-sm uppercase tracking-[0.3em] text-library-ink/45"><?= $role === 'admin' ? 'Library Operations' : 'Student Portal' ?></p>
                        <h2 class="mt-1 text-2xl font-semibold text-library-ink"><?= e($title) ?></h2>
                    </div>
                    <div class="hidden text-right md:block">
                        <p class="text-sm text-slate-500"><?= date('l, F d, Y') ?></p>
                        <p class="mt-1 text-sm font-medium text-slate-700">Welcome back, <?= e($user['name'] ?? 'User') ?></p>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-6 py-8 lg:px-10">
                <?php if ($flash !== null): ?>
                    <div class="mb-6 rounded-2xl border px-4 py-3 text-sm <?= e(flash_class($flash['type'])) ?>">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>
    <?php
}

/**
 * Closes the authenticated app layout and includes small UI scripts.
 */
function render_app_end(): void
{
    ?>
            </main>
        </div>
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }

        document.querySelectorAll('[data-confirm]').forEach((button) => {
            button.addEventListener('click', (event) => {
                const message = button.getAttribute('data-confirm') || 'Are you sure?';

                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
        });
    </script>
    </body>
    </html>
    <?php
}

