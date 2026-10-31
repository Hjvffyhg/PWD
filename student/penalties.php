<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];

// Current penalties: overdue books that are not yet returned (amount keeps growing daily)
$cur = $conn->query("SELECT t.*, b.title, b.author FROM transactions t JOIN books b ON b.book_id=t.book_id
                     WHERE t.user_id=$uid AND t.status IN ('borrowed','return_requested') AND t.due_date < CURDATE() ORDER BY t.due_date")->fetch_all(MYSQLI_ASSOC);
// Penalty history: books returned late
$hist = $conn->query("SELECT t.*, b.title, b.author FROM transactions t JOIN books b ON b.book_id=t.book_id
                      WHERE t.user_id=$uid AND t.status='returned' AND t.penalty_amount > 0 ORDER BY t.returned_at DESC")->fetch_all(MYSQLI_ASSOC);
$curTotal = 0; foreach ($cur as $c) $curTotal += fine_for($c['due_date'])[1];
$histTotal = 0; foreach ($hist as $h) $histTotal += (int)$h['penalty_amount'];

layout_start('Penalties', $base, 'penalties');
?>
<h1 class="page-title">Penalties <small>Late books are charged &#8369;<?= FINE_PER_DAY ?> per day.</small></h1>
<?php show_flash(); ?>
<div class="alert alert-info"><i class="fa-solid fa-store"></i> Penalties are paid in person at the school library. There is no online payment.</div>

<div class="grid g-stats" style="margin-bottom:24px">
  <div class="stat <?= $curTotal ? 'alert' : '' ?>"><div class="ico"><i class="fa-solid fa-peso-sign"></i></div><div><b>&#8369;<?= $curTotal ?></b><span>Current penalty</span></div></div>
  <div class="stat"><div class="ico"><i class="fa-solid fa-calendar-xmark"></i></div><div><b><?= count($cur) ?></b><span>Overdue book(s)</span></div></div>
  <div class="stat"><div class="ico"><i class="fa-solid fa-clock-rotate-left"></i></div><div><b>&#8369;<?= $histTotal ?></b><span>Past penalties total</span></div></div>
</div>

<section class="panel">
  <h2>Current Penalties</h2>
  <?php if (!$cur): ?><div class="empty"><i class="fa-solid fa-circle-check"></i>You have no overdue books. Great job!</div><?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>Book</th><th>Due Date</th><th>Days Late</th><th>Penalty</th><th>Status</th></tr>
    <?php foreach ($cur as $c): [$d, $f] = fine_for($c['due_date']); ?>
    <tr><td><b><?= e($c['title']) ?></b><br><small style="color:var(--muted)"><?= e($c['author']) ?></small></td>
        <td><?= date('M d, Y', strtotime($c['due_date'])) ?></td><td><?= $d ?></td><td><b>&#8369;<?= $f ?></b></td>
        <td><span class="pill overdue">Overdue</span></td></tr>
    <?php endforeach; ?>
  </table></div>
  <p style="font-size:.78rem;color:var(--muted);margin-top:12px">Return the book(s) to the library and pay the penalty at the counter.</p>
  <?php endif; ?>
</section>

<section class="panel">
  <h2>Penalty History</h2>
  <?php if (!$hist): ?><div class="empty"><i class="fa-solid fa-clock-rotate-left"></i>No penalty history yet.</div><?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>Book</th><th>Due Date</th><th>Returned</th><th>Days Late</th><th>Penalty</th></tr>
    <?php foreach ($hist as $h): ?>
    <tr><td><b><?= e($h['title']) ?></b><br><small style="color:var(--muted)"><?= e($h['author']) ?></small></td>
        <td><?= date('M d, Y', strtotime($h['due_date'])) ?></td><td><?= date('M d, Y', strtotime($h['returned_at'])) ?></td>
        <td><?= (int)$h['days_late'] ?></td><td>&#8369;<?= (int)$h['penalty_amount'] ?></td></tr>
    <?php endforeach; ?>
  </table></div><?php endif; ?>
</section>
<?php layout_end($base);
