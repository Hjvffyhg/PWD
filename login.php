<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/public_parts.php';
$base = '';

if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error = '';
$ident = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ident = trim($_POST['identifier'] ?? '');
    $pass  = $_POST['password'] ?? '';
    if ($ident === '' || $pass === '') {
        $error = 'Please enter your username / student ID and password.';
    } else {
        $st = $conn->prepare("SELECT * FROM users WHERE username=? OR student_no=? OR email=? LIMIT 1");
        $st->bind_param('sss', $ident, $ident, $ident); $st->execute();
        $u = $st->get_result()->fetch_assoc();
        if ($u && password_verify($pass, $u['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $u['user_id'];
            $_SESSION['role'] = $u['role'];
            header('Location: index.php');
            exit;
        }
        $error = 'Incorrect username/student ID or password.';
    }
}

$new = latest_books($conn, 6);
layout_start('Login', $base);
?>
<div class="grid g-hero">
  <div>
    <?php welcome_card(); ?>
    <section class="panel">
      <h2>Your gateway to knowledge, anytime, anywhere.</h2>
      <p class="intro-text">Welcome to BAPS e-Library &ndash; your all-in-one digital hub for seamless book access and processing. Streamline your research, explore thousands of titles, and manage your borrowing with ease.</p>
      <a class="btn yellow sm" href="register.php">REGISTER NOW!</a>
    </section>
    <?php newly_added_panel($new, $base); ?>
  </div>
  <section class="panel auth-card" style="align-self:start">
    <img class="logo" src="assets/images/logo.png" alt="BAPS e-Library logo">
    <?php show_flash(); ?>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
      <label for="identifier">Username / Student ID</label>
      <input type="text" id="identifier" name="identifier" data-studentid="auto" maxlength="60" value="<?= e($ident) ?>" placeholder="Enter username or student ID" required autofocus>
      <label for="password">Password</label>
      <div class="pw"><input type="password" id="password" name="password" placeholder="********" required><button type="button" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
      <button class="btn block" type="submit">LOGIN</button>
    </form>
    <p class="alt">DON'T HAVE AN ACCOUNT YET?<a href="register.php">CREATE ACCOUNT/REGISTER HERE</a></p>
  </section>
</div>
<?php layout_end($base);
