<?php
$success = $success ?? '';
$errors = $errors ?? [];
$dashboardData = $dashboardData ?? ['users' => 0, 'managers' => 0, 'customers' => 0, 'revenue' => 0, 'accounts' => [], 'best_sellers' => []];
$userName = htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Brew &amp; Bean</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-page">
    <header class="dashboard-header">
        <div><span class="brand-mark small">B&amp;B</span><span class="dashboard-brand">Brew &amp; Bean</span></div>
        <div class="header-actions"><span><?= $userName ?> <strong>Admin</strong></span><a href="index.php?page=logout">Log out</a></div>
    </header>
    <main class="dashboard-content">
        <p class="eyebrow">System control</p>
        <h1>Admin dashboard</h1>
        <p class="dashboard-lead">A quick view of your cafe accounts and business performance.</p>
        <?php if ($success): ?><div class="message success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="message error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <section class="stats-grid">
            <article class="stat-card"><span>Total users</span><strong><?= (int) $dashboardData['users'] ?></strong></article>
            <article class="stat-card"><span>Managers</span><strong><?= (int) $dashboardData['managers'] ?></strong></article>
            <article class="stat-card"><span>Customers</span><strong><?= (int) $dashboardData['customers'] ?></strong></article>
            <article class="stat-card"><span>Completed revenue</span><strong>$<?= number_format((float) $dashboardData['revenue'], 2) ?></strong></article>
        </section>
        <section class="dashboard-section">
            <h2>Create manager account</h2>
            <form class="inline-form" method="post" action="index.php?page=admin"><input type="hidden" name="action" value="create_manager"><input name="name" placeholder="Manager name" required><input name="email" type="email" placeholder="Email" required><input name="password" type="password" placeholder="Password" minlength="6" required><button class="button" type="submit">Add manager</button></form>
        </section>
        <section class="dashboard-section">
            <h2>Accounts</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($dashboardData['accounts'] as $account): ?><tr>
                                <td><?= htmlspecialchars($account['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($account['role'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($account['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <form method="post" action="index.php?page=admin"><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>"><button class="text-button" type="submit"><?= $account['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button></form>
                                </td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </section>
        <section class="dashboard-section">
            <h2>Edit manager accounts</h2>
            <?php foreach ($dashboardData['accounts'] as $account): ?><?php if ($account['role'] === 'manager'): ?><form class="inline-form" method="post" action="index.php?page=admin"><input type="hidden" name="action" value="update_manager"><input type="hidden" name="user_id" value="<?= (int) $account['id'] ?>"><input name="name" value="<?= htmlspecialchars($account['name'], ENT_QUOTES, 'UTF-8') ?>" required><input name="email" type="email" value="<?= htmlspecialchars($account['email'], ENT_QUOTES, 'UTF-8') ?>" required><button class="button" type="submit">Save manager</button></form><?php endif; ?><?php endforeach; ?>
        </section>
        <section class="dashboard-section">
            <h2>Best-selling products</h2>
            <?php if (!$dashboardData['best_sellers']): ?><p class="empty-state">Completed orders will appear here.</p><?php else: ?><div class="order-list"><?php foreach ($dashboardData['best_sellers'] as $item): ?><div class="order-row"><strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= (int) $item['quantity'] ?> sold</span></div><?php endforeach; ?></div><?php endif; ?>
        </section>
    </main>
</body>

</html>