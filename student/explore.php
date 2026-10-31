<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];

$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['category'] ?? '');
$only = isset($_GET['available']);

$sql = book_select_for($uid) . " WHERE 1=1";
$types = ''; $vals = [];
if ($q !== '') { $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ?)"; $like = "%$q%"; $types .= 'sss'; array_push($vals, $like, $like, $like); }
if ($cat !== '') { $sql .= " AND b.category = ?"; $types .= 's'; $vals[] = $cat; }
if ($only) $sql .= " AND b.available_copies > 0";
$sql .= " ORDER BY b.title";
$st = $conn->prepare($sql);
if ($types) $st->bind_param($types, ...$vals);
$st->execute();
$books = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$cats = $conn->query("SELECT DISTINCT category FROM books ORDER BY category")->fetch_all(MYSQLI_ASSOC);

layout_start('Explore', $base, 'explore');
?>
<h1 class="page-title">Explore Books <small>Search the catalog and click a book to see its details.</small></h1>
<?php show_flash(); ?>
<section class="panel">
  <div class="toolbar">
    <form method="get">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title, author or ISBN">
      <select name="category">
        <option value="">All categories</option>
        <?php foreach ($cats as $c): ?><option <?= $cat === $c['category'] ? 'selected' : '' ?>><?= e($c['category']) ?></option><?php endforeach; ?>
      </select>
      <label style="margin:0;display:flex;align-items:center;gap:6px;text-transform:none"><input type="checkbox" name="available" <?= $only ? 'checked' : '' ?> style="width:auto"> Available only</label>
      <button class="btn" type="submit"><i class="fa-solid fa-filter"></i> Filter</button>
    </form>
  </div>
  <?php if (!$books): ?><div class="empty"><i class="fa-solid fa-magnifying-glass"></i>No books found.</div>
  <?php else: ?><div class="books"><?php foreach ($books as $b) echo book_card($b, $base); ?></div><?php endif; ?>
</section>
<?php layout_end($base);
