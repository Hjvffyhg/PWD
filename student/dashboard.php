<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/public_parts.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];

$stat = $conn->query("SELECT
    SUM(status='borrowed' OR status='return_requested') AS active,
    SUM(status='pending') AS pending,
    SUM(status='returned') AS returned,
    SUM((status='borrowed') AND due_date < CURDATE()) AS overdue
    FROM transactions WHERE user_id=$uid")->fetch_assoc();

$new = $conn->query(book_select_for($uid) . " ORDER BY b.book_id DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
$top = $conn->query(book_select_for($uid) . " WHERE EXISTS (SELECT 1 FROM ratings r WHERE r.book_id = b.book_id AND r.user_id = $uid) ORDER BY avg_rating DESC, times_borrowed DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);
$active = $conn->query("SELECT b.title, t.due_date, t.status FROM transactions t JOIN books b ON b.book_id=t.book_id
                        WHERE t.user_id=$uid AND t.status IN ('borrowed','return_requested') ORDER BY t.due_date")->fetch_all(MYSQLI_ASSOC);

layout_start('Dashboard', $base, 'dashboard');
?>
<h1 class="page-title">Dashboard <small>Welcome back, <?= e(strtok($me['full_name'], ' ')) ?>! Here is your library summary.</small></h1>
<?php show_flash(); ?>
<?php if ((int)$stat['overdue'] > 0): ?>
  <div class="alert alert-error"><i class="fa-solid fa-triangle-exclamation"></i> You have <?= (int)$stat['overdue'] ?> overdue book(s). Please return them to avoid penalties (<?= FINE_PER_DAY ?> pesos per day). <a href="penalties.php" style="text-decoration:underline;font-weight:700">View penalties</a></div>
<?php endif; ?>

<div class="grid g-stats" style="margin-bottom:24px">
  <a href="my_books.php" class="stat"><div class="ico"><i class="fa-solid fa-book-open-reader"></i></div><div><b><?= (int)$stat['active'] ?></b><span>Currently borrowed</span></div></a>
  <a href="my_books.php" class="stat"><div class="ico"><i class="fa-solid fa-hourglass-half"></i></div><div><b><?= (int)$stat['pending'] ?></b><span>Pending requests</span></div></a>
  <a href="history.php" class="stat"><div class="ico"><i class="fa-solid fa-circle-check"></i></div><div><b><?= (int)$stat['returned'] ?></b><span>Books returned</span></div></a>
  <a href="penalties.php" class="stat <?= $stat['overdue'] ? 'alert' : '' ?>"><div class="ico"><i class="fa-solid fa-calendar-xmark"></i></div><div><b><?= (int)$stat['overdue'] ?></b><span>Overdue</span></div></a>
</div>

<?php if ($active): ?>
<section class="panel">
  <h2>Due Dates</h2>
  <div class="table-wrap"><table>
    <tr><th>Book</th><th>Due Date</th><th>Status</th></tr>
    <?php foreach ($active as $a): $od = $a['status'] === 'borrowed' && $a['due_date'] < date('Y-m-d'); ?>
      <tr><td><?= e($a['title']) ?></td><td><?= date('M d, Y', strtotime($a['due_date'])) ?></td>
      <td><span class="pill <?= $od ? 'overdue' : $a['status'] ?>"><?= $od ? 'Overdue' : ($a['status'] === 'borrowed' ? 'Borrowed' : 'Return requested') ?></span></td></tr>
    <?php endforeach; ?>
  </table></div>
</section>
<?php endif; ?>

<div class="grid g2">
  <?php newly_added_panel($new, $base); ?>
  <?php top_rated_panel($top, $base); ?>
</div>
<?php layout_end($base);
