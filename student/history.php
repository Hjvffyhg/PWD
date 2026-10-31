<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];

// Rate a returned book (CREATE / UPDATE rating)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bid = (int)($_POST['book_id'] ?? 0);
    $rate = (int)($_POST['rating'] ?? 0);
    $ok = $conn->query("SELECT 1 FROM transactions WHERE user_id=$uid AND book_id=$bid AND status='returned'")->num_rows;
    if ($ok && $rate >= 1 && $rate <= 5) {
        $st = $conn->prepare("INSERT INTO ratings (user_id, book_id, rating) VALUES (?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating)");
        $st->bind_param('iii', $uid, $bid, $rate); $st->execute();
        flash('Thanks for rating this book!');
    } else flash('You can only rate books you have returned.', 'error');
    header('Location: history.php'); exit;
}

$rows = $conn->query("SELECT t.*, b.title, b.author,
        (SELECT rating FROM ratings r WHERE r.user_id=$uid AND r.book_id=t.book_id) AS my_rating
        FROM transactions t JOIN books b ON b.book_id=t.book_id WHERE t.user_id=$uid ORDER BY t.trans_id DESC")->fetch_all(MYSQLI_ASSOC);
layout_start('History', $base, 'history');
?>
<h1 class="page-title">Borrowing History <small>All your past and current transactions.</small></h1>
<?php show_flash(); ?>
<section class="panel">
<?php if (!$rows): ?><div class="empty"><i class="fa-solid fa-clock-rotate-left"></i>No history yet.</div>
<?php else: ?>
<div class="table-wrap"><table>
  <tr><th>Book</th><th>Requested</th><th>Borrowed</th><th>Returned</th><th>Status</th><th>Your Rating</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><b><?= e($r['title']) ?></b><br><small style="color:var(--muted)"><?= e($r['author']) ?></small></td>
    <td><?= date('M d, Y', strtotime($r['requested_at'])) ?></td>
    <td><?= $r['borrowed_at'] ? date('M d, Y', strtotime($r['borrowed_at'])) : '&mdash;' ?></td>
    <td><?= $r['returned_at'] ? date('M d, Y', strtotime($r['returned_at'])) : '&mdash;' ?></td>
    <td><span class="pill <?= $r['status'] ?>"><?= ucwords(str_replace('_', ' ', $r['status'])) ?></span></td>
    <td>
      <?php if ($r['status'] === 'returned'): ?>
      <form method="post" style="display:flex;gap:8px;align-items:center">
        <input type="hidden" name="book_id" value="<?= $r['book_id'] ?>">
        <select name="rating" style="width:auto;padding:5px 8px">
          <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int)$r['my_rating'] === $i ? 'selected' : '' ?>><?= $i ?> &#9733;</option><?php endfor; ?>
        </select>
        <button class="btn sm yellow"><?= $r['my_rating'] ? 'Update' : 'Rate' ?></button>
      </form>
      <?php else: ?>&mdash;<?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php endif; ?>
</section>
<?php layout_end($base);
