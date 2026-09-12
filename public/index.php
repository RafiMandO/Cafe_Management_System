<?php
session_start();

require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/models/User.php';

$userModel = new User($pdo);

$page = $_GET['page'] ?? 'login';
$errors = [];
$flash = $_SESSION['flash'] ?? [];
unset($_SESSION['flash']);
$success = $flash['success'] ?? '';
$errors = $flash['errors'] ?? [];
$user = $_SESSION['user'] ?? null;
$role = $user['role'] ?? null;

function redirectTo(string $page): never
{
    header('Location: index.php?page=' . $page);
    exit;
}

function requireRole(array $roles): void
{
    if (empty($_SESSION['logged_in'])) {
        redirectTo('login');
    }

    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        redirectTo('dashboard');
    }
}

if ($page === 'logout') {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

if ($page === 'admin') {
    requireRole(['admin']);
}
if ($page === 'manager') {
    requireRole(['manager']);
}
if ($page === 'customer') {
    requireRole(['customer']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $username = strtolower(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($page === 'register') {
        $name = trim($_POST['name'] ?? '');
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($name === '') {
            $errors[] = 'Please enter your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (!preg_match('/^[a-z0-9_]{3,50}$/', $username)) {
            $errors[] = 'Username must be 3-50 characters using letters, numbers, or underscores.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            try {
                $userModel->create($name, $username, $email, $password);
                $success = 'Account created. You can now log in.';
                $page = 'login';
            } catch (PDOException $exception) {
                if ($exception->getCode() === '23000') {
                    $errors[] = 'An account with this email already exists.';
                } else {
                    $errors[] = 'Unable to create the account right now.';
                }
            }
        }
    } elseif ($page === 'login') {
        $login = strtolower(trim($_POST['login'] ?? $email));
        $user = $userModel->findByLogin($login);

        if ($login === '' || $password === '') {
            $errors[] = 'Enter your username or email and password.';
        } elseif (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Email or password is incorrect.';
        }

        if (!$errors) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            $_SESSION['logged_in'] = true;
            header('Location: index.php?page=dashboard');
            exit;
        }
    } elseif ($page === 'admin') {
        requireRole(['admin']);
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'create_manager') {
                $managerName = trim($_POST['name'] ?? '');
                $managerEmail = strtolower(trim($_POST['email'] ?? ''));
                $managerPassword = $_POST['password'] ?? '';
                if ($managerName === '' || !filter_var($managerEmail, FILTER_VALIDATE_EMAIL) || strlen($managerPassword) < 6) {
                    throw new RuntimeException('Enter a name, valid email, and password of at least 6 characters.');
                }
                $managerUsername = strtolower(preg_replace('/[^a-z0-9_]/', '_', $managerName));
                $userModel->create($managerName, $managerUsername, $managerEmail, $managerPassword, 'manager');
                $success = 'Manager account created.';
            } elseif ($action === 'update_manager') {
                $statement = $pdo->prepare("UPDATE users SET name = :name, email = :email WHERE id = :id AND role = 'manager'");
                $statement->execute(['name' => trim($_POST['name'] ?? ''), 'email' => strtolower(trim($_POST['email'] ?? '')), 'id' => (int) $_POST['user_id']]);
                $success = 'Manager account updated.';
            } elseif ($action === 'toggle_user') {
                $statement = $pdo->prepare("UPDATE users SET status = IF(status = 'active', 'inactive', 'active') WHERE id = :id AND role <> 'admin'");
                $statement->execute(['id' => (int) $_POST['user_id']]);
                $success = 'Account status updated.';
            }
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000' ? 'That email is already in use.' : 'Unable to update the account.';
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    } elseif ($page === 'manager') {
        requireRole(['manager']);
        $action = $_POST['action'] ?? '';
        try {
            if ($action === 'seed_menu') {
                $sampleItems = [
                    ['Cappuccino', 'Espresso with steamed milk foam', 'Coffee', 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=800&q=80', 4.50, 25],
                    ['Masala Tea', 'Black tea with warm spices and milk', 'Tea', 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?w=800&q=80', 3.50, 25],
                    ['Chocolate Cake', 'Moist chocolate cake with cream', 'Dessert', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=800&q=80', 5.00, 12],
                    ['Classic Burger', 'Grilled beef patty with fresh toppings', 'Main Course', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&q=80', 8.50, 15],
                    ['Creamy Pasta', 'Pasta tossed in a creamy herb sauce', 'Main Course', 'https://images.unsplash.com/photo-1473093295043-cdd812d0e601?w=800&q=80', 9.00, 15],
                    ['Garden Salad', 'Crisp seasonal vegetables and dressing', 'Healthy', 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=800&q=80', 6.50, 15],
                ];
                $exists = $pdo->prepare('SELECT COUNT(*) FROM menu_items WHERE name = :name');
                $insert = $pdo->prepare('INSERT INTO menu_items (name, description, category, image_url, price, stock_quantity, is_available) VALUES (:name, :description, :category, :image_url, :price, :stock_quantity, 1)');
                foreach ($sampleItems as [$name, $description, $category, $imageUrl, $price, $stock]) {
                    $exists->execute(['name' => $name]);
                    if (!$exists->fetchColumn()) {
                        $insert->execute(['name' => $name, 'description' => $description, 'category' => $category, 'image_url' => $imageUrl, 'price' => $price, 'stock_quantity' => $stock]);
                    }
                }
                $success = 'Sample menu items loaded.';
            } elseif ($action === 'save_menu_item') {
                $itemId = (int) ($_POST['item_id'] ?? 0);
                $values = [
                    'name' => trim($_POST['name'] ?? ''),
                    'description' => trim($_POST['description'] ?? ''),
                    'category' => trim($_POST['category'] ?? ''),
                    'image_url' => trim($_POST['image_url'] ?? ''),
                    'price' => (float) ($_POST['price'] ?? 0),
                    'stock_quantity' => (int) ($_POST['stock_quantity'] ?? 0),
                    'is_available' => isset($_POST['is_available']) ? 1 : 0,
                ];
                if ($values['name'] === '' || $values['category'] === '' || $values['price'] < 0 || $values['stock_quantity'] < 0) {
                    throw new RuntimeException('Enter valid menu item details.');
                }
                if ($itemId) {
                    $statement = $pdo->prepare('UPDATE menu_items SET name = :name, description = :description, category = :category, image_url = :image_url, price = :price, stock_quantity = :stock_quantity, is_available = :is_available WHERE id = :id');
                    $values['id'] = $itemId;
                } else {
                    $statement = $pdo->prepare('INSERT INTO menu_items (name, description, category, image_url, price, stock_quantity, is_available) VALUES (:name, :description, :category, :image_url, :price, :stock_quantity, :is_available)');
                }
                $statement->execute($values);
                $success = 'Menu item saved.';
            } elseif ($action === 'delete_menu_item') {
                $statement = $pdo->prepare('DELETE FROM menu_items WHERE id = :id');
                $statement->execute(['id' => (int) $_POST['item_id']]);
                $success = 'Menu item removed.';
            } elseif ($action === 'update_order') {
                $allowedStatuses = ['pending', 'preparing', 'completed', 'cancelled'];
                $status = $_POST['status'] ?? 'pending';
                if (!in_array($status, $allowedStatuses, true)) {
                    throw new RuntimeException('Invalid order status.');
                }
                $statement = $pdo->prepare('UPDATE orders SET status = :status WHERE id = :id');
                $statement->execute(['status' => $status, 'id' => (int) $_POST['order_id']]);
                $success = 'Order status updated.';
            }
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    } elseif ($page === 'customer') {
        requireRole(['customer']);
        $action = $_POST['action'] ?? '';
        $_SESSION['cart'] ??= [];
        try {
            if ($action === 'add_cart') {
                $itemId = (int) $_POST['item_id'];
                $_SESSION['cart'][$itemId] = ($_SESSION['cart'][$itemId] ?? 0) + 1;
                $success = 'Item added to cart.';
            } elseif ($action === 'remove_cart') {
                unset($_SESSION['cart'][(int) $_POST['item_id']]);
                $success = 'Item removed from cart.';
            } elseif ($action === 'place_order') {
                $cart = $_SESSION['cart'];
                $orderType = $_POST['order_type'] ?? '';
                if (!$cart || !in_array($orderType, ['dine-in', 'takeaway'], true)) {
                    throw new RuntimeException('Choose items and an order type first.');
                }
                $pdo->beginTransaction();
                $itemStatement = $pdo->prepare('SELECT id, price, stock_quantity FROM menu_items WHERE id = :id AND is_available = 1 FOR UPDATE');
                $total = 0;
                $items = [];
                foreach ($cart as $itemId => $quantity) {
                    $itemStatement->execute(['id' => (int) $itemId]);
                    $item = $itemStatement->fetch();
                    if (!$item || $item['stock_quantity'] < $quantity) {
                        throw new RuntimeException('One item is unavailable or out of stock.');
                    }
                    $total += (float) $item['price'] * $quantity;
                    $items[] = [$item, $quantity];
                }
                $orderStatement = $pdo->prepare('INSERT INTO orders (user_id, order_type, total) VALUES (:user_id, :order_type, :total)');
                $orderStatement->execute(['user_id' => $user['id'], 'order_type' => $orderType, 'total' => $total]);
                $orderId = $pdo->lastInsertId();
                $lineStatement = $pdo->prepare('INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (:order_id, :menu_item_id, :quantity, :price)');
                $stockStatement = $pdo->prepare('UPDATE menu_items SET stock_quantity = stock_quantity - :quantity WHERE id = :id');
                foreach ($items as [$item, $quantity]) {
                    $lineStatement->execute(['order_id' => $orderId, 'menu_item_id' => $item['id'], 'quantity' => $quantity, 'price' => $item['price']]);
                    $stockStatement->execute(['quantity' => $quantity, 'id' => $item['id']]);
                }
                $pdo->commit();
                $_SESSION['cart'] = [];
                $success = 'Order placed successfully. Total: $' . number_format($total, 2);
            } elseif ($action === 'review') {
                $rating = (int) $_POST['rating'];
                if ($rating < 1 || $rating > 5) {
                    throw new RuntimeException('Rating must be between 1 and 5.');
                }
                $statement = $pdo->prepare('INSERT INTO reviews (user_id, menu_item_id, rating, comment) VALUES (:user_id, :menu_item_id, :rating, :comment)');
                $statement->execute(['user_id' => $user['id'], 'menu_item_id' => (int) $_POST['menu_item_id'], 'rating' => $rating, 'comment' => trim($_POST['comment'] ?? '')]);
                $success = 'Thank you for your review.';
            }
        } catch (RuntimeException $exception) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = $exception->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($page, ['admin', 'manager', 'customer'], true)) {
    $_SESSION['flash'] = [
        'success' => $success,
        'errors' => $errors,
    ];
    redirectTo('dashboard');
}

if ($page === 'register') {
    require __DIR__ . '/../app/views/auth/register.php';
} elseif ($page === 'dashboard' && !empty($_SESSION['logged_in'])) {
    $role = $_SESSION['user']['role'];
    $dashboardData = [];

    if ($role === 'admin') {
        $dashboardData['users'] = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $dashboardData['managers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'manager'")->fetchColumn();
        $dashboardData['customers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();
        $dashboardData['revenue'] = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
        $dashboardData['accounts'] = $pdo->query("SELECT id, name, username, email, role, status FROM users WHERE role <> 'admin' ORDER BY role, name")->fetchAll();
        $dashboardData['best_sellers'] = $pdo->query("SELECT menu_items.name, SUM(order_items.quantity) AS quantity FROM order_items JOIN menu_items ON menu_items.id = order_items.menu_item_id JOIN orders ON orders.id = order_items.order_id WHERE orders.status = 'completed' GROUP BY menu_items.id, menu_items.name ORDER BY quantity DESC LIMIT 5")->fetchAll();
        require __DIR__ . '/../app/views/dashboard/admin.php';
    } elseif ($role === 'manager') {
        $dashboardData['menu_items'] = $pdo->query('SELECT COUNT(*) FROM menu_items')->fetchColumn();
        $dashboardData['available_items'] = $pdo->query('SELECT COUNT(*) FROM menu_items WHERE is_available = 1')->fetchColumn();
        $dashboardData['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'preparing')")->fetchColumn();
        $dashboardData['revenue'] = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
        $dashboardData['items'] = $pdo->query('SELECT * FROM menu_items ORDER BY category, name')->fetchAll();
        $dashboardData['orders'] = $pdo->query('SELECT orders.*, users.name AS customer_name FROM orders JOIN users ON users.id = orders.user_id ORDER BY orders.created_at DESC')->fetchAll();
        require __DIR__ . '/../app/views/dashboard/manager.php';
    } else {
        $statement = $pdo->query('SELECT id, name, category, price, image_url FROM menu_items WHERE is_available = 1 ORDER BY category, name');
        $dashboardData['menu'] = $statement->fetchAll();
        $statement = $pdo->prepare('SELECT id, order_type, status, total, created_at FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5');
        $statement->execute(['user_id' => $_SESSION['user']['id']]);
        $dashboardData['orders'] = $statement->fetchAll();
        $dashboardData['cart'] = [];
        if (!empty($_SESSION['cart'])) {
            $ids = array_keys($_SESSION['cart']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $statement = $pdo->prepare("SELECT id, name, price FROM menu_items WHERE id IN ($placeholders)");
            $statement->execute($ids);
            foreach ($statement->fetchAll() as $item) {
                $item['quantity'] = $_SESSION['cart'][$item['id']];
                $dashboardData['cart'][] = $item;
            }
        }
        $dashboardData['review_items'] = $pdo->query('SELECT id, name FROM menu_items ORDER BY name')->fetchAll();
        require __DIR__ . '/../app/views/dashboard/customer.php';
    }
} else {
    require __DIR__ . '/../app/views/auth/login.php';
}
