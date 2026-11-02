<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/public_parts.php';
$base = '';

// Logged-in users go straight to their dashboard
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'librarian' ? 'librarian/dashboard.php' : 'student/dashboard.php'));
    exit;
}

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $st = $conn->prepare(book_select() . " WHERE b.title LIKE ? OR b.author LIKE ? OR b.category LIKE ? OR b.isbn LIKE ? ORDER BY b.title");
    $st->bind_param('ssss', $like, $like, $like, $like); $st->execute();
    $results = $st->get_result()->fetch_all(MYSQLI_ASSOC);
}
$new = latest_books($conn, 8);
$top = top_rated_books($conn, 5);

layout_start('Home', $base);
welcome_card();

if ($q !== ''): ?>
  <section class="panel">
    <h2>Search results for "<?= e($q) ?>"</h2>
    <?php if (!$results): ?><div class="empty"><i class="fa-solid fa-magnifying-glass"></i>No books found.</div>
    <?php else: ?><div class="books"><?php foreach ($results as $b) echo book_card($b, $base); ?></div><?php endif; ?>
  </section>
<?php endif; ?>

<div class="grid g2">
  <?php newly_added_panel($new, $base); ?>
  <?php top_rated_panel($top, $base); ?>
</div>
<?php layout_end($base);
