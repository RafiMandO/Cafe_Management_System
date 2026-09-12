<?php
$errors = $errors ?? [];
$success = $success ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in | Brew &amp; Bean</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main class="auth-shell">
        <section class="auth-intro">
            <span class="brand-mark">B&amp;B</span>
            <p class="eyebrow">Brew &amp; Bean</p>
            <h1>Your daily cup, managed simply.</h1>
            <p>Keep your cafe moving smoothly from the first order to the last pour.</p>
        </section>

        <section class="auth-card">
            <p class="eyebrow">Welcome back</p>
            <h2>Log in to your account</h2>
            <?php if ($success): ?>
                <div class="message success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="message error"><?= htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="post" action="index.php?page=login">
                <label for="login">Username or email</label>
                <input id="login" name="login" type="text" placeholder="you@example.com" required autofocus>

                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Enter your password" required>

                <button class="button" type="submit">Log in</button>
            </form>
            <p class="form-footer">New here? <a href="index.php?page=register">Create an account</a></p>
        </section>
    </main>
</body>

</html>