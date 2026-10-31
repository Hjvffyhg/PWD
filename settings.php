<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
$base = '';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$uid = (int)$_SESSION['user_id'];
$role = $_SESSION['role'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    $u = $conn->query("SELECT * FROM users WHERE user_id=$uid")->fetch_assoc();
    if ($act === 'profile') {
        $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? ''); $cy = trim($_POST['course_year'] ?? $u['course_year']);
        $err = '';
        if (strlen($name) < 3) $err = 'Please enter your full name.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Please enter a valid email.';
        else {
            $d = $conn->prepare("SELECT 1 FROM users WHERE email=? AND user_id<>?");
            $d->bind_param('si', $email, $uid); $d->execute();
            if ($d->get_result()->num_rows) $err = 'That email is already used by another account.';
        }
        $pic = $u['profile_pic'];
        if (!$err) {
            $new = save_image('profile_pic', 'profiles', $err);
            if ($new) $pic = 'uploads/profiles/' . $new;
        }
        if ($err) flash($err, 'error');
        else {
            $st = $conn->prepare("UPDATE users SET full_name=?, email=?, course_year=?, profile_pic=? WHERE user_id=?");
            $st->bind_param('ssssi', $name, $email, $cy, $pic, $uid); $st->execute();
            flash('Profile updated.');
        }
    } elseif ($act === 'password') {
        $cur = $_POST['current'] ?? ''; $new = $_POST['new'] ?? ''; $con = $_POST['confirm'] ?? '';
        if (!password_verify($cur, $u['password'])) flash('Current password is incorrect.', 'error');
        elseif (strlen($new) < 6) flash('New password must be at least 6 characters.', 'error');
        elseif ($new !== $con) flash('New passwords do not match.', 'error');
        else {
            $h = password_hash($new, PASSWORD_DEFAULT);
            $st = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
            $st->bind_param('si', $h, $uid); $st->execute();
            flash('Password changed.');
        }
    }
    header('Location: settings.php'); exit;
}

layout_start('Settings', $base, 'settings');
?>
<h1 class="page-title">Settings <small>Update your profile and password.</small></h1>
<?php show_flash(); ?>
<div class="grid g2">
  <section class="panel">
    <h2>My Profile</h2>
    <div class="profile-top">
      <img src="<?= e(pic_url($me['profile_pic'], $base)) ?>" alt="Profile picture">
      <div><b><?= e($me['full_name']) ?></b><br><small style="color:var(--muted)">@<?= e($me['username']) ?> &middot; <?= ucfirst($role) ?><?= $me['student_no'] ? ' &middot; ' . e($me['student_no']) : '' ?></small></div>
    </div>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="profile">
      <label>Profile Picture</label><input type="file" name="profile_pic" accept="image/png,image/jpeg,image/webp">
      <label>Full Name</label><input type="text" name="full_name" value="<?= e($me['full_name']) ?>" required>
      <label>Email</label><input type="email" name="email" value="<?= e($me['email']) ?>" required>
      <?php if ($role === 'student'): ?><label>Course &amp; Year</label><input type="text" name="course_year" value="<?= e($me['course_year']) ?>"><?php endif; ?>
      <button class="btn block" type="submit">Save Changes</button>
    </form>
  </section>
  <section class="panel">
    <h2>Change Password</h2>
    <form method="post">
      <input type="hidden" name="action" value="password">
      <label>Current Password</label><input type="password" name="current" required>
      <label>New Password</label><input type="password" name="new" minlength="6" required>
      <label>Confirm New Password</label><input type="password" name="confirm" minlength="6" required>
      <button class="btn block" type="submit">Update Password</button>
    </form>
  </section>
</div>
<?php layout_end($base);
