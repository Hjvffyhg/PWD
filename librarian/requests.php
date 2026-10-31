<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('librarian', $base);

// ---------- UPDATE transaction status ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid = (int)($_POST['trans_id'] ?? 0);
    $act = $_POST['action'] ?? '';
    $t = $conn->query("SELECT t.*, b.title, b.available_copies FROM transactions t JOIN books b ON b.book_id=t.book_id WHERE t.trans_id=$tid")->fetch_assoc();
    if ($t) {
        $uid = (int)$t['user_id']; $bid = (int)$t['book_id'];
        if ($act === 'approve' && $t['status'] === 'pending') {
            if ($t['available_copies'] < 1) flash('No copies left to lend.', 'error');
            else {
                $due = date('Y-m-d', strtotime('+' . LOAN_DAYS . ' days'));
                $conn->query("UPDATE transactions SET status='borrowed', borrowed_at=NOW(), due_date='$due' WHERE trans_id=$tid");
                $conn->query("UPDATE books SET available_copies = available_copies - 1 WHERE book_id=$bid");
                notify($conn, $uid, "Approved: \"{$t['title']}\". Please get it from the library. Due on " . date('M d, Y', strtotime($due)) . ".");
                flash('Request approved. Due date: ' . date('M d, Y', strtotime($due)));
            }
        } elseif ($act === 'reject' && $t['status'] === 'pending') {
            $conn->query("UPDATE transactions SET status='rejected' WHERE trans_id=$tid");
            notify($conn, $uid, "Your request for \"{$t['title']}\" was declined.");
            flash('Request rejected.', 'info');
        } elseif ($act === 'returned' && in_array($t['status'], ['borrowed', 'return_requested'])) {
            [$late, $fine] = fine_for($t['due_date']);
            $conn->query("UPDATE transactions SET status='returned', returned_at=NOW(), days_late=$late, penalty_amount=$fine WHERE trans_id=$tid");
            $conn->query("UPDATE books SET available_copies = LEAST(total_copies, available_copies + 1) WHERE book_id=$bid");
            if ($fine > 0) {
                notify($conn, $uid, "\"{$t['title']}\" was returned $late day(s) late. Penalty: P$fine. Please pay at the school library.");
                flash("Book marked as returned. Penalty to collect in person: P$fine ($late day(s) late).");
            } else {
                notify($conn, $uid, "Thank you! \"{$t['title']}\" was marked as returned. You can now rate it in History.");
                flash('Book marked as returned.');
            }
        }
    }
    header('Location: requests.php'); exit;
}

function tx_rows($conn, $where) {
    return $conn->query("SELECT t.*, u.full_name, u.student_no, b.title FROM transactions t JOIN users u ON u.user_id=t.user_id
                         JOIN books b ON b.book_id=t.book_id WHERE $where ORDER BY t.trans_id DESC")->fetch_all(MYSQLI_ASSOC);
}
$pending = tx_rows($conn, "t.status='pending'");
$out = tx_rows($conn, "t.status IN ('borrowed','return_requested')");
$done = $conn->query("SELECT t.*, u.full_name, b.title FROM transactions t JOIN users u ON u.user_id=t.user_id JOIN books b ON b.book_id=t.book_id
                      WHERE t.status IN ('returned','rejected') ORDER BY t.trans_id DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);

function act_btn($tid, $act, $label, $cls) {
    return '<form method="post" style="display:inline"><input type="hidden" name="trans_id" value="' . $tid . '"><input type="hidden" name="action" value="' . $act . '"><button class="btn sm ' . $cls . '">' . $label . '</button></form>';
}
layout_start('Borrowed/Return Requests', $base, 'requests');
?>
<h1 class="page-title">Borrowed / Return Requests <small>Approve borrow requests and confirm returned books.</small></h1>
<?php show_flash(); ?>

<section class="panel">
  <h2>Pending Borrow Requests (<?= count($pending) ?>)</h2>
  <?php if (!$pending): ?><div class="empty"><i class="fa-solid fa-inbox"></i>No pending requests.</div><?php else: ?>
  <div class="table-wrap"><table><tr><th>Student</th><th>Book</th><th>Requested</th><th>Action</th></tr>
  <?php foreach ($pending as $r): ?><tr>
    <td><b><?= e($r['full_name']) ?></b><br><small><?= e($r['student_no']) ?></small></td><td><?= e($r['title']) ?></td>
    <td><?= date('M d, Y g:i A', strtotime($r['requested_at'])) ?></td>
    <td><div class="actions"><?= act_btn($r['trans_id'], 'approve', '<i class="fa-solid fa-check"></i> Approve', 'ok') ?><?= act_btn($r['trans_id'], 'reject', '<i class="fa-solid fa-xmark"></i> Reject', 'danger') ?></div></td>
  </tr><?php endforeach; ?></table></div><?php endif; ?>
</section>

<section class="panel">
  <h2>Borrowed Books &amp; Return Requests (<?= count($out) ?>)</h2>
  <?php if (!$out): ?><div class="empty"><i class="fa-solid fa-book-open"></i>No books are currently borrowed.</div><?php else: ?>
  <div class="table-wrap"><table><tr><th>Student</th><th>Book</th><th>Due Date</th><th>Status</th><th>Action</th></tr>
  <?php foreach ($out as $r): [$late, $fine] = fine_for($r['due_date']); ?><tr>
    <td><b><?= e($r['full_name']) ?></b><br><small><?= e($r['student_no']) ?></small></td><td><?= e($r['title']) ?></td>
    <td><?= date('M d, Y', strtotime($r['due_date'])) ?><?php if ($late): ?><br><small style="color:var(--danger)"><?= $late ?> day(s) late &middot; &#8369;<?= $fine ?></small><?php endif; ?></td>
    <td><span class="pill <?= $late ? 'overdue' : $r['status'] ?>"><?= $late ? 'Overdue' : ucwords(str_replace('_', ' ', $r['status'])) ?></span></td>
    <td><?= act_btn($r['trans_id'], 'returned', '<i class="fa-solid fa-rotate-left"></i> Mark Returned', 'ok') ?></td>
  </tr><?php endforeach; ?></table></div><?php endif; ?>
</section>

<section class="panel">
  <h2>Recently Closed</h2>
  <?php if (!$done): ?><div class="empty">Nothing yet.</div><?php else: ?>
  <div class="table-wrap"><table><tr><th>Student</th><th>Book</th><th>Status</th><th>Date</th></tr>
  <?php foreach ($done as $r): ?><tr><td><?= e($r['full_name']) ?></td><td><?= e($r['title']) ?></td>
    <td><span class="pill <?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
    <td><?= $r['returned_at'] ? date('M d, Y', strtotime($r['returned_at'])) : date('M d, Y', strtotime($r['requested_at'])) ?></td></tr><?php endforeach; ?></table></div><?php endif; ?>
</section>
<?php layout_end($base);
