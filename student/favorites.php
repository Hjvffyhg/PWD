<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];
$books = $conn->query(book_select_for($uid) . " JOIN favorites fv ON fv.book_id=b.book_id AND fv.user_id=$uid ORDER BY fv.fav_id DESC")->fetch_all(MYSQLI_ASSOC);
layout_start('Favorites', $base, 'favorites');
?>
<h1 class="page-title">Favorites <small>Books you saved for later.</small></h1>
<?php show_flash(); ?>
<section class="panel">
<?php if (!$books): ?><div class="empty"><i class="fa-regular fa-heart"></i>No favorites yet. Open a book and press "Favorite".</div>
<?php else: ?><div class="books"><?php foreach ($books as $b) echo book_card($b, $base); ?></div><?php endif; ?>
</section>
<?php layout_end($base);
