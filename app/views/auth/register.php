<?php $errors = $errors ?? []; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Brew &amp; Bean</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main class="auth-shell single-panel">
        <section class="auth-card">
            <a class="back-link" href="index.php?page=login">&larr; Back to log in</a>
            <span class="brand-mark">B&amp;B</span>
            <p class="eyebrow">Get started</p>
            <h2>Create your account</h2>
            <?php if ($errors): ?>
                <div class="message error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="post" action="index.php?page=register">
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" placeholder="Your name" required value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="your_username" pattern="[A-Za-z0-9_]{3,50}" required value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <label for="email">Email address</label>
                <input id="email" name="email" type="email" placeholder="you@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="At least 6 characters" required minlength="6">

                <label for="confirm_password">Confirm password</label>
                <input id="confirm_password" name="confirm_password" type="password" placeholder="Repeat your password" required minlength="6">

                <button class="button" type="submit">Create account</button>
            </form>
        </section>
    </main>
</body>

</html>