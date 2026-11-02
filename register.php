<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/public_parts.php';
$base = '';

if (!empty($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$errors = [];
$f = ['full_name' => '', 'student_no' => '', 'email' => '', 'course_year' => '', 'username' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $_) $f[$k] = trim($_POST[$k] ?? '');
    $pass = $_POST['password'] ?? '';

    // Server-side validation
    if (strlen($f['full_name']) < 3) $errors[] = 'Please enter your full name.';
    if (!preg_match('/^\d{4}-\d{5}$/', $f['student_no'])) $errors[] = 'Student ID must look like 2026-00001.';
    if (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($f['course_year'] === '') $errors[] = 'Please enter your course and year.';
    if (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $f['username'])) $errors[] = 'Username must be 4-30 letters, numbers, dots or underscores.';
    if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (!$errors) {
        $st = $conn->prepare("SELECT 1 FROM users WHERE username=? OR student_no=? OR email=?");
        $st->bind_param('sss', $f['username'], $f['student_no'], $f['email']); $st->execute();
        if ($st->get_result()->num_rows) $errors[] = 'Username, student ID or email is already registered.';
    }
    if (!$errors) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $st = $conn->prepare("INSERT INTO users (full_name,student_no,email,course_year,username,password,role) VALUES (?,?,?,?,?,?, 'student')");
        $st->bind_param('ssssss', $f['full_name'], $f['student_no'], $f['email'], $f['course_year'], $f['username'], $hash);
        $st->execute();
        flash('Account created! You can now log in.');
        header('Location: login.php');
        exit;
    }
}

$new = latest_books($conn, 6);
layout_start('Register', $base);
?>
<div class="grid g-hero">
  <div>
    <?php welcome_card(); ?>
    <?php newly_added_panel($new, $base); ?>
  </div>
  <section class="panel auth-card" style="align-self:start">
    <h2 style="font-size:2rem;text-align:left">Registration</h2>
    <?php if ($errors): ?><div class="alert alert-error"><?php foreach ($errors as $er) echo e($er) . '<br>'; ?></div><?php endif; ?>
    <form method="post" novalidate>
      <label for="full_name">Full Name</label>
      <input type="text" id="full_name" name="full_name" value="<?= e($f['full_name']) ?>" placeholder="Juan Dela Cruz" required>
      <label for="student_no">Student ID Number</label>
      <input type="text" id="student_no" name="student_no" data-studentid="strict" inputmode="numeric" maxlength="10" value="<?= e($f['student_no']) ?>" placeholder="2026-00001" pattern="\d{4}-\d{5}" required>
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" value="<?= e($f['email']) ?>" placeholder="juandelacruz@example.com" required>
      <label for="course_year">Course &amp; Year</label>
      <input type="text" id="course_year" name="course_year" value="<?= e($f['course_year']) ?>" placeholder="BSIT - 2nd Year" required>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="<?= e($f['username']) ?>" placeholder="juandelacruz" required>
      <label for="password">Password</label>
      <div class="pw"><input type="password" id="password" name="password" placeholder="At least 6 characters" required><button type="button" aria-label="Show password"><i class="fa-regular fa-eye"></i></button></div>
      <button class="btn block yellow" type="submit"><i class="fa-solid fa-user-plus"></i> REGISTER ACCOUNT</button>
    </form>
    <p class="alt">ALREADY HAVE AN ACCOUNT?<a href="login.php">LOGIN HERE</a></p>
  </section>
</div>
<?php layout_end($base);
