<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('librarian', $base);

$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from))
  $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))
  $to = date('Y-m-d');

$st = $conn->prepare("SELECT COUNT(*) total, SUM(status='returned') returned, SUM(status IN ('borrowed','return_requested')) out_now, SUM(status='rejected') rejected
                      FROM transactions WHERE DATE(requested_at) BETWEEN ? AND ?");
$st->bind_param('ss', $from, $to);
$st->execute();
$sum = $st->get_result()->fetch_assoc();

$st = $conn->prepare("SELECT b.title, b.author, COUNT(*) cnt FROM transactions t JOIN books b ON b.book_id=t.book_id
                      WHERE t.status IN ('borrowed','return_requested','returned') AND DATE(t.requested_at) BETWEEN ? AND ? GROUP BY b.book_id ORDER BY cnt DESC LIMIT 5");
$st->bind_param('ss', $from, $to);
$st->execute();
$top = $st->get_result()->fetch_all(MYSQLI_ASSOC);

$over = $conn->query("SELECT t.due_date, u.full_name, u.student_no, b.title FROM transactions t JOIN users u ON u.user_id=t.user_id JOIN books b ON b.book_id=t.book_id
                      WHERE t.status='borrowed' AND t.due_date < CURDATE() ORDER BY t.due_date")->fetch_all(MYSQLI_ASSOC);
$cats = $conn->query("SELECT category, COUNT(*) titles, SUM(total_copies) copies, SUM(available_copies) avail FROM books GROUP BY category ORDER BY titles DESC")->fetch_all(MYSQLI_ASSOC);
$totalFine = 0;
foreach ($over as $o)
  $totalFine += fine_for($o['due_date'])[1];

layout_start('Report', $base, 'report');
?>
<h1 class="page-title">Library Report <small>Summary of borrowing activity from <?= date('M d, Y', strtotime($from)) ?>
    to <?= date('M d, Y', strtotime($to)) ?>.</small></h1>
<section class="panel no-print">
  <form method="get" class="toolbar" style="margin:0">
    <label style="margin:0">From</label><input type="date" name="from" value="<?= e($from) ?>">
    <label style="margin:0">To</label><input type="date" name="to" value="<?= e($to) ?>">
    <button class="btn" type="submit"><i class="fa-solid fa-filter"></i> Apply</button>
  </form>
</section>
<div class="grid g-stats" style="margin-bottom:24px">
  <div class="stat">
    <div class="ico"><i class="fa-solid fa-right-left"></i></div>
    <div><b><?= (int) $sum['total'] ?></b><span>Total requests</span></div>
  </div>
  <div class="stat">
    <div class="ico"><i class="fa-solid fa-circle-check"></i></div>
    <div><b><?= (int) $sum['returned'] ?></b><span>Returned</span></div>
  </div>
  <div class="stat">
    <div class="ico"><i class="fa-solid fa-book-open-reader"></i></div>
    <div><b><?= (int) $sum['out_now'] ?></b><span>Still borrowed</span></div>
  </div>
  <div class="stat">
    <div class="ico"><i class="fa-solid fa-peso-sign"></i></div>
    <div><b>&#8369;<?= $totalFine ?></b><span>Penalties owed</span></div>
  </div>
</div>
<div class="grid g2">
  <section class="panel">
    <h2>Most Borrowed Books</h2>
    <?php if (!$top): ?>
      <div class="empty">No data for this period.</div><?php else: ?>
      <div class="table-wrap">
        <table>
          <tr>
            <th>#</th>
            <th>Title</th>
            <th>Author</th>
            <th>Times</th>
          </tr>
          <?php foreach ($top as $i => $r): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= e($r['title']) ?></td>
              <td><?= e($r['author']) ?></td>
              <td><b><?= $r['cnt'] ?></b></td>
            </tr><?php endforeach; ?>
        </table>
      </div><?php endif; ?>
  </section>
  <section class="panel">
    <h2>Books by Category</h2>
    <?php if (!$cats): ?>
      <div class="empty">No books yet.</div><?php else: ?>
      <div class="table-wrap">
        <table>
          <tr>
            <th>Category</th>
            <th>Titles</th>
            <th>Copies</th>
            <th>Available</th>
          </tr>
          <?php foreach ($cats as $c): ?>
            <tr>
              <td><?= e($c['category']) ?></td>
              <td><?= $c['titles'] ?></td>
              <td><?= $c['copies'] ?></td>
              <td><?= $c['avail'] ?></td>
            </tr><?php endforeach; ?>
        </table>
      </div><?php endif; ?>
  </section>
</div>
<section class="panel">
  <h2>Overdue Books &amp; Penalties (&#8369;<?= FINE_PER_DAY ?>/day)</h2>
  <?php if (!$over): ?>
    <div class="empty"><i class="fa-solid fa-circle-check"></i>No overdue books.</div><?php else: ?>
    <div class="table-wrap">
      <table>
        <tr>
          <th>Student</th>
          <th>Book</th>
          <th>Due Date</th>
          <th>Days Late</th>
          <th>Penalty</th>
        </tr>
        <?php foreach ($over as $o):
          [$d, $f] = fine_for($o['due_date']); ?>
          <tr>
            <td><?= e($o['full_name']) ?> <small>(<?= e($o['student_no']) ?>)</small></td>
            <td><?= e($o['title']) ?></td>
            <td><?= date('M d, Y', strtotime($o['due_date'])) ?></td>
            <td><?= $d ?></td>
            <td>&#8369;<?= $f ?></td>
          </tr><?php endforeach; ?>
      </table>
    </div><?php endif; ?>
</section>
<?php layout_end($base);
