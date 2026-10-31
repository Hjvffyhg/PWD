<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];

// UPDATE / DELETE actions on the student's own transactions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $myname = $conn->query("SELECT full_name FROM users WHERE user_id=$uid")->fetch_assoc()['full_name'];
    $tid = (int)($_POST['trans_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    $t = $conn->query("SELECT t.*, b.title FROM transactions t JOIN books b ON b.book_id=t.book_id WHERE t.trans_id=$tid AND t.user_id=$uid")->fetch_assoc();
    if ($t && $act === 'cancel' && $t['status'] === 'pending') {
        $conn->query("DELETE FROM transactions WHERE trans_id=$tid");
        flash('Request cancelled.', 'info');
    } elseif ($t && $act === 'return' && $t['status'] === 'borrowed') {
        $conn->query("UPDATE transactions SET status='return_requested' WHERE trans_id=$tid");
        $libs = $conn->query("SELECT user_id FROM users WHERE role='librarian'");
        foreach ($libs as $l) notify($conn, $l['user_id'], "{$myname} wants to return \"{$t['title']}\".");
        flash('Return request sent. Please bring the book to the library.');
    }
    header('Location: my_books.php'); exit;
}

$rows = $conn->query("SELECT t.*, b.title, b.author, b.cover FROM transactions t JOIN books b ON b.book_id=t.book_id
                      WHERE t.user_id=$uid AND t.status IN ('pending','borrowed','return_requested') ORDER BY t.trans_id DESC")->fetch_all(MYSQLI_ASSOC);
layout_start('My Borrowed Books', $base, 'mybooks');
?>
<h1 class="page-title">My Borrowed Books <small>Books you requested or are currently borrowing. Loan period: <?= LOAN_DAYS ?> days.</small></h1>
<?php show_flash(); ?>
<section class="panel">
<?php if (!$rows): ?><div class="empty"><i class="fa-solid fa-book-open"></i>You have no active books. <a href="explore.php" style="color:var(--navy);font-weight:700">Explore books</a></div>
<?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Cover</th><th>Book</th><th>Requested</th><th>Due Date</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($rows as $r):
      [$late, $fine] = ($r['status'] === 'borrowed' && $r['due_date']) ? fine_for($r['due_date']) : [0, 0]; ?>
  <tr>
    <td><img class="thumb" src="<?= e(cover_url($r['cover'], $base)) ?>" alt=""></td>
    <td><b><?= e($r['title']) ?></b><br><small style="color:var(--muted)"><?= e($r['author']) ?></small></td>
    <td><?= date('M d, Y', strtotime($r['requested_at'])) ?></td>
    <td><?= $r['due_date'] ? date('M d, Y', strtotime($r['due_date'])) : '&mdash;' ?>
        <?php if ($late): ?><br><small style="color:var(--danger)"><?= $late ?> day(s) late &middot; penalty &#8369;<?= $fine ?></small><?php endif; ?></td>
    <td><span class="pill <?= $late ? 'overdue' : $r['status'] ?>"><?= $late ? 'Overdue' : ucwords(str_replace('_', ' ', $r['status'])) ?></span></td>
    <td>
      <?php if ($r['status'] === 'pending'): ?>
        <form method="post" data-confirm="Cancel this request?"><input type="hidden" name="trans_id" value="<?= $r['trans_id'] ?>"><input type="hidden" name="action" value="cancel"><button class="btn sm danger">Cancel</button></form>
      <?php elseif ($r['status'] === 'borrowed'): ?>
        <form method="post" data-confirm="Send a return request for this book?"><input type="hidden" name="trans_id" value="<?= $r['trans_id'] ?>"><input type="hidden" name="action" value="return"><button class="btn sm">Return</button></form>
      <?php else: ?><small>Waiting for librarian</small><?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
</section>
<?php layout_end($base);
