<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('librarian', $base);

$one = function ($sql) use ($conn) { return (int)$conn->query($sql)->fetch_row()[0]; };
$titles   = $one("SELECT COUNT(*) FROM books");
$copies   = $one("SELECT COALESCE(SUM(available_copies),0) FROM books");
$students = $one("SELECT COUNT(*) FROM users WHERE role='student'");
$pending  = $one("SELECT COUNT(*) FROM transactions WHERE status='pending'");
$active   = $one("SELECT COUNT(*) FROM transactions WHERE status IN ('borrowed','return_requested')");
$overdue  = $one("SELECT COUNT(*) FROM transactions WHERE status='borrowed' AND due_date < CURDATE()");

$recent = $conn->query("SELECT t.*, u.full_name, b.title FROM transactions t JOIN users u ON u.user_id=t.user_id JOIN books b ON b.book_id=t.book_id
                        ORDER BY t.trans_id DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$late = $conn->query("SELECT t.due_date, u.full_name, b.title FROM transactions t JOIN users u ON u.user_id=t.user_id JOIN books b ON b.book_id=t.book_id
                      WHERE t.status='borrowed' AND t.due_date < CURDATE() ORDER BY t.due_date LIMIT 6")->fetch_all(MYSQLI_ASSOC);

layout_start('Dashboard', $base, 'dashboard');
?>
<h1 class="page-title">Librarian Dashboard <small>Welcome, <?= e($me['full_name']) ?>. Here is today's overview.</small></h1>
<?php show_flash(); ?>
<div class="grid g-stats" style="margin-bottom:24px">
  <a href="inventory.php" class="stat"><div class="ico"><i class="fa-solid fa-book"></i></div><div><b><?= $titles ?></b><span>Book titles</span></div></a>
  <a href="inventory.php" class="stat"><div class="ico"><i class="fa-solid fa-boxes-stacked"></i></div><div><b><?= $copies ?></b><span>Copies available</span></div></a>
  <a href="students.php" class="stat"><div class="ico"><i class="fa-solid fa-user-graduate"></i></div><div><b><?= $students ?></b><span>Students</span></div></a>
  <a href="requests.php" class="stat"><div class="ico"><i class="fa-solid fa-hourglass-half"></i></div><div><b><?= $pending ?></b><span>Pending requests</span></div></a>
  <a href="requests.php" class="stat"><div class="ico"><i class="fa-solid fa-book-open-reader"></i></div><div><b><?= $active ?></b><span>Books out</span></div></a>
  <a href="requests.php" class="stat <?= $overdue ? 'alert' : '' ?>"><div class="ico"><i class="fa-solid fa-calendar-xmark"></i></div><div><b><?= $overdue ?></b><span>Overdue</span></div></a>
</div>
<div class="grid g2">
  <section class="panel">
    <h2>Recent Transactions</h2>
    <?php if (!$recent): ?><div class="empty"><i class="fa-solid fa-right-left"></i>No transactions yet.</div><?php else: ?>
    <div class="table-wrap"><table><tr><th>Student</th><th>Book</th><th>Status</th></tr>
      <?php foreach ($recent as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><?= e($r['title']) ?></td><td><span class="pill <?= $r['status'] ?>"><?= ucwords(str_replace('_', ' ', $r['status'])) ?></span></td></tr><?php endforeach; ?>
    </table></div><?php endif; ?>
    <p style="margin-top:14px"><a class="btn sm" href="requests.php">Open requests</a></p>
  </section>
  <section class="panel">
    <h2>Overdue Books</h2>
    <?php if (!$late): ?><div class="empty"><i class="fa-solid fa-circle-check"></i>No overdue books. Great!</div><?php else: ?>
    <div class="table-wrap"><table><tr><th>Student</th><th>Book</th><th>Due</th></tr>
      <?php foreach ($late as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><?= e($r['title']) ?></td><td><?= date('M d', strtotime($r['due_date'])) ?></td></tr><?php endforeach; ?>
    </table></div><?php endif; ?>
  </section>
</div>
<?php layout_end($base);
