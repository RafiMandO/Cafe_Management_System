<?php
session_start();

$page = $_GET['page'] ?? 'login';
$errors = [];
$success = '';

if ($page === 'logout') {
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
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
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            $_SESSION['user'] = [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
            ];
            $success = 'Account created. You can now log in.';
            $page = 'login';
        }
    } elseif ($page === 'login') {
        $user = $_SESSION['user'] ?? null;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            $errors[] = 'Enter your email and password.';
        } elseif (!$user || $user['email'] !== $email || !password_verify($password, $user['password'])) {
            $errors[] = 'Email or password is incorrect.';
        }

        if (!$errors) {
            $_SESSION['logged_in'] = true;
            header('Location: index.php?page=welcome');
            exit;
        }
    }
}

if ($page === 'register') {
    require __DIR__ . '/../app/views/auth/register.php';
} elseif ($page === 'welcome' && !empty($_SESSION['logged_in'])) {
    $userName = htmlspecialchars($_SESSION['user']['name'], ENT_QUOTES, 'UTF-8');
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome | Brew &amp; Bean</title>
        <link rel="stylesheet" href="css/style.css">
    </head>

    <body>
        <main class="auth-shell">
            <section class="welcome-card">
                <span class="brand-mark">B&amp;B</span>
                <p class="eyebrow">Cafe Management System</p>
                <h1>Welcome, <?= $userName ?>.</h1>
                <p class="muted">You are successfully logged in to Brew &amp; Bean.</p>
                <a class="button" href="index.php?page=logout">Log out</a>
            </section>
        </main>
    </body>

    </html>
<?php
} else {
    require __DIR__ . '/../app/views/auth/login.php';
}
