<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/layout.php';
$base = '../';
require_role('librarian', $base);

// ---------- CREATE / UPDATE / DELETE ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['action'] ?? '';
    $id = (int)($_POST['book_id'] ?? 0);

    if ($act === 'delete') {
        $busy = $conn->query("SELECT 1 FROM transactions WHERE book_id=$id AND status IN ('pending','borrowed','return_requested')")->num_rows;
        if ($busy) flash('Cannot delete: this book still has active requests or loans.', 'error');
        else {
            $b = $conn->query("SELECT cover FROM books WHERE book_id=$id")->fetch_assoc();
            $conn->query("DELETE FROM books WHERE book_id=$id");
            if ($b && $b['cover'] && $b['cover'] !== 'sample.jpg') @unlink(__DIR__ . '/../uploads/covers/' . $b['cover']);
            flash('Book deleted.');
        }
    } else {
        $title = trim($_POST['title'] ?? ''); $author = trim($_POST['author'] ?? ''); $isbn = trim($_POST['isbn'] ?? '');
        $cat = trim($_POST['category'] ?? ''); $abs = trim($_POST['abstract'] ?? '');
        $pages = (int)($_POST['pages'] ?? 0); $total = (int)($_POST['total_copies'] ?? 0);
        $err = '';
        if ($title === '' || $author === '' || $isbn === '' || $cat === '' || $abs === '') $err = 'Title, author, ISBN, category and abstract are required.';
        elseif ($total < 1 || $total > 100) $err = 'Total copies must be between 1 and 100.';
        elseif ($pages < 1 || $pages > 1200) $err = 'Pages must be between 1 and 1,200.';
        elseif (!preg_match('/^(\d{3}-\d-\d{2}-\d{6}-\d|\d{13}|\d{10})$/', $isbn)) $err = 'ISBN must be 13 digits in the format 978-0-00-000000-0.';
        elseif ($act === 'add' && empty($_FILES['cover']['name'])) $err = 'Please upload a book cover picture.';
        if (!$err) {
            $dup = $conn->prepare("SELECT book_id FROM books WHERE isbn=? AND book_id<>?");
            $dup->bind_param('si', $isbn, $id); $dup->execute();
            if ($dup->get_result()->num_rows) $err = 'A book with this ISBN already exists.';
        }
        $cover = null;
        if (!$err) $cover = save_image('cover', 'covers', $err);

        if ($err) flash($err, 'error');
        elseif ($act === 'add') {
            $st = $conn->prepare("INSERT INTO books (title,author,isbn,category,abstract,pages,total_copies,available_copies,cover) VALUES (?,?,?,?,?,?,?,?,?)");
            $st->bind_param('sssssiiis', $title, $author, $isbn, $cat, $abs, $pages, $total, $total, $cover);
            $st->execute(); flash('Book added to the inventory.');
        } elseif ($act === 'edit') {
            $old = $conn->query("SELECT * FROM books WHERE book_id=$id")->fetch_assoc();
            $out = $old['total_copies'] - $old['available_copies'];     // copies currently out
            if ($total < $out) flash("Total copies cannot be lower than the $out copy/copies currently borrowed.", 'error');
            else {
                $avail = $total - $out;
                $cv = $cover ?: $old['cover'];
                $st = $conn->prepare("UPDATE books SET title=?,author=?,isbn=?,category=?,abstract=?,pages=?,total_copies=?,available_copies=?,cover=? WHERE book_id=?");
                $st->bind_param('sssssiiisi', $title, $author, $isbn, $cat, $abs, $pages, $total, $avail, $cv, $id);
                $st->execute(); flash('Book updated.');
            }
        }
    }
    header('Location: inventory.php'); exit;
}

// ---------- READ ----------
$q = trim($_GET['q'] ?? '');
$like = "%$q%";
$st = $conn->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ? OR category LIKE ? ORDER BY book_id DESC");
$st->bind_param('ssss', $like, $like, $like, $like); $st->execute();
$books = $st->get_result()->fetch_all(MYSQLI_ASSOC);

layout_start('Inventory', $base, 'inventory');
?>
<h1 class="page-title">Inventory <small>Add, edit and remove books in the library.</small></h1>
<?php show_flash(); ?>
<section class="panel">
  <div class="toolbar">
    <form method="get"><input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title, author, ISBN or category"><button class="btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button></form>
    <button class="btn yellow" data-open="bookForm" data-fill='<?= e(json_encode(['action' => 'add', 'book_id' => 0, 'title' => '', 'author' => '', 'isbn' => '', 'category' => '', 'abstract' => '', 'pages' => '', 'total_copies' => 1, '_title' => 'Add New Book'])) ?>'><i class="fa-solid fa-plus"></i> Add Book</button>
  </div>
  <?php if (!$books): ?><div class="empty"><i class="fa-solid fa-book"></i>No books found.</div><?php else: ?>
  <div class="table-wrap"><table>
    <tr><th>Cover</th><th>Title</th><th>Author</th><th>ISBN</th><th>Category</th><th>Stock</th><th>Actions</th></tr>
    <?php foreach ($books as $b): ?>
    <tr>
      <td><img class="thumb" src="<?= e(cover_url($b['cover'], $base)) ?>" alt=""></td>
      <td><b><?= e($b['title']) ?></b></td><td><?= e($b['author']) ?></td><td><?= e($b['isbn']) ?></td><td><?= e($b['category']) ?></td>
      <td><span class="pill <?= $b['available_copies'] > 0 ? 'ok' : 'out' ?>"><?= (int)$b['available_copies'] ?> / <?= (int)$b['total_copies'] ?></span></td>
      <td><div class="actions">
        <button class="btn sm" data-open="bookForm" data-fill='<?= e(json_encode(['action' => 'edit', 'book_id' => $b['book_id'], 'title' => $b['title'], 'author' => $b['author'], 'isbn' => $b['isbn'], 'category' => $b['category'], 'abstract' => $b['abstract'], 'pages' => $b['pages'], 'total_copies' => $b['total_copies'], '_title' => 'Edit Book'])) ?>'><i class="fa-solid fa-pen"></i> Edit</button>
        <form method="post" data-confirm="Delete this book permanently?"><input type="hidden" name="action" value="delete"><input type="hidden" name="book_id" value="<?= $b['book_id'] ?>"><button class="btn sm danger"><i class="fa-solid fa-trash"></i></button></form>
      </div></td>
    </tr>
    <?php endforeach; ?>
  </table></div><?php endif; ?>
</section>

<div class="modal" id="bookForm" aria-hidden="true">
  <div class="modal-box narrow">
    <button class="modal-x" data-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <h2 data-title>Add New Book</h2>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add"><input type="hidden" name="book_id" value="0">
      <div class="form-grid">
        <div class="full"><label>Title</label><input type="text" name="title" required></div>
        <div><label>Author</label><input type="text" name="author" required></div>
        <div><label>ISBN</label><input type="text" name="isbn" data-isbn maxlength="17" inputmode="numeric" placeholder="978-0-00-000000-0" required></div>
        <div><label>Category</label><input type="text" name="category" list="cats" placeholder="Fiction, Science..." required><datalist id="cats"><option>Fiction</option><option>Non-Fiction</option><option>Science</option><option>Technology</option><option>Mathematics</option><option>History</option><option>Romance</option><option>Business</option></datalist></div>
        <div><label>Total Copies / Stock</label><input type="text" name="total_copies" data-max="100" maxlength="3" inputmode="numeric" pattern="[0-9]*" autocomplete="off" value="1" placeholder="1 - 100" required></div>
        <div><label>Pages</label><input type="text" name="pages" data-max="1200" maxlength="4" inputmode="numeric" pattern="[0-9]*" autocomplete="off" placeholder="1 - 1,200" required></div>
        <div><label>Book Cover Picture</label><input type="file" name="cover" data-cover required accept="image/png,image/jpeg,image/webp"></div>
        <div class="full"><label>Abstract / Short Intro</label><textarea name="abstract" placeholder="Short description  of  the book" required></textarea></div>
      </div>
      <button class="btn block" type="submit">Save Book</button>
    </form>
  </div>
</div>
<?php layout_end($base);
