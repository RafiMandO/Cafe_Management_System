<?php
$success = $success ?? '';
$errors = $errors ?? [];
$dashboardData = $dashboardData ?? ['menu_items' => 0, 'available_items' => 0, 'pending_orders' => 0, 'revenue' => 0, 'items' => [], 'orders' => []];
$userName = htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard | Brew &amp; Bean</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-page">
    <header class="dashboard-header">
        <div><span class="brand-mark small">B&amp;B</span><span class="dashboard-brand">Brew &amp; Bean</span></div>
        <div class="header-actions"><span><?= $userName ?> <strong>Manager</strong></span><a href="index.php?page=logout">Log out</a></div>
    </header>
    <main class="dashboard-content">
        <p class="eyebrow">Daily operations</p>
        <h1>Manager dashboard</h1>
        <p class="dashboard-lead">Keep the menu, stock, and incoming orders moving.</p>
        <?php if ($success): ?><div class="message success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="message error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <section class="stats-grid">
            <article class="stat-card"><span>Menu items</span><strong><?= (int) $dashboardData['menu_items'] ?></strong></article>
            <article class="stat-card"><span>Available now</span><strong><?= (int) $dashboardData['available_items'] ?></strong></article>
            <article class="stat-card"><span>Open orders</span><strong><?= (int) $dashboardData['pending_orders'] ?></strong></article>
            <article class="stat-card"><span>Completed revenue</span><strong>$<?= number_format((float) $dashboardData['revenue'], 2) ?></strong></article>
        </section>
        <section class="dashboard-section">
            <h2>Edit menu items</h2>
            <?php foreach ($dashboardData['items'] as $item): ?><div class="manager-item"><img class="menu-thumb" src="<?= htmlspecialchars($item['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>">
                    <form class="inline-form" method="post" action="index.php?page=manager"><input type="hidden" name="action" value="save_menu_item"><input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>"><input name="name" value="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>" required><input name="category" value="<?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?>" required><input name="image_url" value="<?= htmlspecialchars($item['image_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Image URL"><input name="price" type="number" step="0.01" min="0" value="<?= htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8') ?>" required><input name="stock_quantity" type="number" min="0" value="<?= (int) $item['stock_quantity'] ?>" required><label class="check-label"><input name="is_available" type="checkbox" <?= $item['is_available'] ? 'checked' : '' ?>> Available</label><button class="button" type="submit">Save changes</button></form>
                </div><?php endforeach; ?>
        </section>
        <section class="dashboard-section">
            <h2>Add menu item</h2>
            <form method="post" action="index.php?page=manager"><input type="hidden" name="action" value="seed_menu"><button class="button" type="submit">Load six sample items</button></form>
            <form class="inline-form" method="post" action="index.php?page=manager"><input type="hidden" name="action" value="save_menu_item"><input name="name" placeholder="Item name" required><input name="category" placeholder="Category" required><input name="image_url" placeholder="Image URL"><input name="price" type="number" step="0.01" min="0" placeholder="Price" required><input name="stock_quantity" type="number" min="0" placeholder="Stock" required><input name="description" placeholder="Description"><label class="check-label"><input name="is_available" type="checkbox" checked> Available</label><button class="button" type="submit">Add item</button></form>
        </section>
        <section class="dashboard-section">
            <h2>Menu and inventory</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Availability</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($dashboardData['items'] as $item): ?><tr>
                                <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>$<?= number_format((float) $item['price'], 2) ?></td>
                                <td><?= (int) $item['stock_quantity'] ?></td>
                                <td><?= $item['is_available'] ? 'Available' : 'Unavailable' ?></td>
                                <td>
                                    <form method="post" action="index.php?page=manager"><input type="hidden" name="action" value="delete_menu_item"><input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>"><button class="text-button" type="submit">Delete</button></form>
                                </td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </section>
        <section class="dashboard-section">
            <h2>Orders and billing</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Update</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($dashboardData['orders'] as $order): ?><tr>
                                <td>#<?= (int) $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($order['order_type'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>$<?= number_format((float) $order['total'], 2) ?></td>
                                <td><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <form method="post" action="index.php?page=manager"><input type="hidden" name="action" value="update_order"><input type="hidden" name="order_id" value="<?= (int) $order['id'] ?>"><select name="status">
                                            <option>pending</option>
                                            <option>preparing</option>
                                            <option>completed</option>
                                            <option>cancelled</option>
                                        </select><button class="text-button" type="submit">Save</button></form>
                                </td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>