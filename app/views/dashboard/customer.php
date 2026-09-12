<?php
$success = $success ?? '';
$errors = $errors ?? [];
$dashboardData = $dashboardData ?? ['menu' => [], 'orders' => [], 'cart' => [], 'review_items' => []];
$userName = htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Brew &amp; Bean</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dashboard-page">
    <header class="dashboard-header">
        <div><span class="brand-mark small">B&amp;B</span><span class="dashboard-brand">Brew &amp; Bean</span></div>
        <div class="header-actions"><span><?= $userName ?> <strong>Customer</strong></span><a href="index.php?page=logout">Log out</a></div>
    </header>
    <main class="dashboard-content">
        <p class="eyebrow">Ordering and feedback</p>
        <h1>What are you having today?</h1>
        <p class="dashboard-lead">Browse the menu and keep track of your recent orders.</p>
        <?php if ($success): ?><div class="message success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="message error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <section class="dashboard-section">
            <h2>Available menu</h2>
            <?php if (!$dashboardData['menu']): ?><p class="empty-state">The menu is being prepared. Please check back soon.</p><?php else: ?>
                <div class="menu-grid">
                    <?php foreach ($dashboardData['menu'] as $item): ?><article class="menu-card"><?php if (!empty($item['image_url'])): ?><img src="<?= htmlspecialchars($item['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?><span><?= htmlspecialchars($item['category'], ENT_QUOTES, 'UTF-8') ?></span>
                            <h3><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></h3><strong>$<?= number_format((float) $item['price'], 2) ?></strong>
                            <form method="post" action="index.php?page=customer"><input type="hidden" name="action" value="add_cart"><input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>"><button class="button" type="submit">Add to cart</button></form>
                        </article><?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <section class="dashboard-section">
            <h2>Your cart</h2>
            <?php if (!$dashboardData['cart']): ?><p class="empty-state">Your cart is empty.</p><?php else: ?><div class="order-list"><?php foreach ($dashboardData['cart'] as $item): ?><div class="order-row"><strong><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></strong><span>Qty: <?= (int) $item['quantity'] ?></span><span>$<?= number_format((float) $item['price'] * $item['quantity'], 2) ?></span>
                            <form method="post" action="index.php?page=customer"><input type="hidden" name="action" value="remove_cart"><input type="hidden" name="item_id" value="<?= (int) $item['id'] ?>"><button class="text-button" type="submit">Remove</button></form>
                        </div><?php endforeach; ?></div>
                <form class="inline-form" method="post" action="index.php?page=customer"><input type="hidden" name="action" value="place_order"><select name="order_type" required>
                        <option value="">Choose order type</option>
                        <option value="dine-in">Dine-in</option>
                        <option value="takeaway">Takeaway</option>
                    </select><button class="button" type="submit">Place order</button></form><?php endif; ?>
        </section>
        <section class="dashboard-section">
            <h2>Recent orders</h2>
            <?php if (!$dashboardData['orders']): ?><p class="empty-state">You have not placed an order yet.</p><?php else: ?><div class="order-list"><?php foreach ($dashboardData['orders'] as $order): ?><div class="order-row"><strong>#<?= (int) $order['id'] ?></strong><span><?= htmlspecialchars($order['order_type'], ENT_QUOTES, 'UTF-8') ?></span><span><?= htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8') ?></span><strong>$<?= number_format((float) $order['total'], 2) ?></strong></div><?php endforeach; ?></div><?php endif; ?>
        </section>
        <section class="dashboard-section">
            <h2>Leave feedback</h2>
            <form class="inline-form" method="post" action="index.php?page=customer"><input type="hidden" name="action" value="review"><select name="menu_item_id" required>
                    <option value="">Choose item</option><?php foreach ($dashboardData['review_items'] as $item): ?><option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                </select><select name="rating" required>
                    <option value="">Rating</option>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Good</option>
                    <option value="3">3 - Average</option>
                    <option value="2">2 - Poor</option>
                    <option value="1">1 - Bad</option>
                </select><textarea name="comment" placeholder="Your review"></textarea><button class="button" type="submit">Submit review</button></form>
        </section>
    </main>
</body>

</html>