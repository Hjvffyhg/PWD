<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('librarian', $base);

// ---------- CREATE / UPDATE / DELETE student accounts ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    $id = (int)($_POST['user_id'] ?? 0);
    if ($act === 'delete') {
        $busy = $conn->query("SELECT 1 FROM transactions WHERE user_id=$id AND status IN ('pending','borrowed','return_requested')")->num_rows;
        if ($busy) flash('Cannot delete: this student still has active requests or loans.', 'error');
        else { $conn->query("DELETE FROM users WHERE user_id=$id AND role='student'"); flash('Student account deleted.'); }
    } else {
        $f = [];
        foreach (['full_name', 'student_no', 'email', 'course_year', 'username'] as $k) $f[$k] = trim($_POST[$k] ?? '');
        $pass = $_POST['password'] ?? '';
        $err = '';
        if (strlen($f['full_name']) < 3) $err = 'Please enter the full name.';
        elseif (!preg_match('/^\d{4}-\d{5}$/', $f['student_no'])) $err = 'Student ID must look like 2026-00001.';
        elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $err = 'Please enter a valid email.';
        elseif (!preg_match('/^[A-Za-z0-9_.]{4,30}$/', $f['username'])) $err = 'Username must be 4-30 letters, numbers, dots or underscores.';
        elseif ($act === 'add' && strlen($pass) < 6) $err = 'Password must be at least 6 characters.';
        elseif ($pass !== '' && strlen($pass) < 6) $err = 'New password must be at least 6 characters.';
        if (!$err) {
            $d = $conn->prepare("SELECT 1 FROM users WHERE (username=? OR student_no=? OR email=?) AND user_id<>?");
            $d->bind_param('sssi', $f['username'], $f['student_no'], $f['email'], $id); $d->execute();
            if ($d->get_result()->num_rows) $err = 'Username, student ID or email already exists.';
        }
        if ($err) flash($err, 'error');
        elseif ($act === 'add') {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $st = $conn->prepare("INSERT INTO users (full_name,student_no,email,course_year,username,password,role) VALUES (?,?,?,?,?,?, 'student')");
            $st->bind_param('ssssss', $f['full_name'], $f['student_no'], $f['email'], $f['course_year'], $f['username'], $hash);
            $st->execute(); flash('Student account created.');
        } else {
            $st = $conn->prepare("UPDATE users SET full_name=?,student_no=?,email=?,course_year=?,username=? WHERE user_id=? AND role='student'");
            $st->bind_param('sssssi', $f['full_name'], $f['student_no'], $f['email'], $f['course_year'], $f['username'], $id);
            $st->execute();
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $p = $conn->prepare("UPDATE users SET password=? WHERE user_id=? AND role='student'");
                $p->bind_param('si', $hash, $id); $p->execute();
            }
            flash('Student account updated.');
        }
    }
    header('Location: students.php'); exit;
}

// ---------- READ ----------
$q = trim($_GET['q'] ?? ''); $like = "%$q%";
$st = $conn->prepare("SELECT u.*, (SELECT COUNT(*) FROM transactions t WHERE t.user_id=u.user_id AND t.status IN ('borrowed','return_requested')) AS out_now
                      FROM users u WHERE u.role='student' AND (u.full_name LIKE ? OR u.student_no LIKE ? OR u.username LIKE ? OR u.email LIKE ?) ORDER BY u.user_id DESC");
$st->bind_param('ssss', $like, $like, $like, $like); $st->execute();
$rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);

layout_start('Students', $base, 'students');
?>
<h1 class="page-title">Students <small>Manage registered student accounts.</small></h1>
<?php show_flash(); ?>
<section class="panel students-panel">
  <div class="toolbar">
    <form method="get"><input type="text" name="q" value="<?= e($q) ?>" placeholder="Search name, student ID, username or email"><button class="btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button></form>
    <button class="btn yellow" data-open="stuForm" data-fill='<?= e(json_encode(['action' => 'add', 'user_id' => 0, 'full_name' => '', 'student_no' => '', 'email' => '', 'course_year' => '', 'username' => '', 'password' => '', '_title' => 'Register Student'])) ?>'><i class="fa-solid fa-user-plus"></i> Add Student</button>
  </div>
  <?php if (!$rows): ?><div class="empty"><i class="fa-solid fa-user-graduate"></i>No students found.</div><?php else: ?>
  <div class="table-wrap students-table-wrap"><table class="students-table">
    <tr><th>Photo</th><th>Name</th><th>Student ID</th><th>Course &amp; Year</th><th>Email</th><th>Books Out</th><th>Actions</th></tr>
    <?php foreach ($rows as $s): ?>
    <tr>
      <td><img class="av" src="<?= e(pic_url($s['profile_pic'], $base)) ?>" alt=""></td>
      <td><b><?= e($s['full_name']) ?></b><br><small style="color:var(--muted)">@<?= e($s['username']) ?></small></td>
      <td><?= e($s['student_no']) ?></td><td><?= e($s['course_year']) ?></td><td><?= e($s['email']) ?></td><td><?= (int)$s['out_now'] ?></td>
      <td><div class="actions">
        <button class="btn sm" data-open="stuForm" data-fill='<?= e(json_encode(['action' => 'edit', 'user_id' => $s['user_id'], 'full_name' => $s['full_name'], 'student_no' => $s['student_no'], 'email' => $s['email'], 'course_year' => $s['course_year'], 'username' => $s['username'], 'password' => '', '_title' => 'Edit Student'])) ?>'><i class="fa-solid fa-pen"></i> Edit</button>
        <form method="post" data-confirm="Delete this student account?"><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= $s['user_id'] ?>"><button class="btn sm danger"><i class="fa-solid fa-trash"></i> Delete</button></form>
      </div></td>
    </tr>
    <?php endforeach; ?>
  </table></div>
  <p class="table-scroll-hint"><i class="fa-solid fa-arrow-left"></i> Swipe or scroll horizontally to view all student details. <i class="fa-solid fa-arrow-right"></i></p>
  <?php endif; ?>
</section>

<div class="modal" id="stuForm" aria-hidden="true">
  <div class="modal-box narrow">
    <button class="modal-x" data-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <h2 data-title>Register Student</h2>
    <form method="post">
      <input type="hidden" name="action" value="add"><input type="hidden" name="user_id" value="0">
      <div class="form-grid">
        <div class="full"><label>Full Name</label><input type="text" name="full_name" required></div>
        <div><label>Student ID</label><input type="text" name="student_no" data-studentid="strict" inputmode="numeric" maxlength="10" placeholder="2026-00001" pattern="\d{4}-\d{5}" required></div>
        <div><label>Course &amp; Year</label><input type="text" name="course_year" placeholder="BSIT - 2nd Year"></div>
        <div class="full"><label>Email</label><input type="email" name="email" required></div>
        <div><label>Username</label><input type="text" name="username" required></div>
        <div><label>Password</label><input type="password" name="password" placeholder="Leave blank to keep"></div>
      </div>
      <button class="btn block" type="submit">Save Student</button>
    </form>
  </div>
</div>
<?php layout_end($base);
