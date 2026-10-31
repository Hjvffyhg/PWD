<?php
// Shared page layout: top bar, sidebar (role based), book detail modal

function menu_for($role) {
    if ($role === 'student') return [
        ['dashboard', 'Dashboard',        'student/dashboard.php', 'fa-gauge-high'],
        ['explore',   'Explore',          'student/explore.php',   'fa-compass'],
        ['mybooks',   'My Borrowed Books','student/my_books.php',  'fa-book-open-reader'],
        ['favorites', 'Favorites',        'student/favorites.php', 'fa-heart'],
        ['penalties', 'Penalties',        'student/penalties.php', 'fa-peso-sign'],
        ['history',   'History',          'student/history.php',   'fa-clock-rotate-left'],
        ['contact',   'Contact',          'contact.php',           'fa-address-book'],
        ['about',     'About',            'about.php',             'fa-circle-info'],
    ];
    return [
        ['dashboard', 'Dashboard',        'librarian/dashboard.php', 'fa-gauge-high'],
        ['inventory', 'Inventory',        'librarian/inventory.php', 'fa-boxes-stacked'],
        ['students',  'Students',         'librarian/students.php',  'fa-user-graduate'],
        ['requests',  'Borrowed/Return Request', 'librarian/requests.php', 'fa-right-left'],
        ['report',    'Report',           'librarian/report.php',    'fa-chart-column'],
        ['contact',   'Contact',          'contact.php',             'fa-address-book'],
        ['about',     'About',            'about.php',               'fa-circle-info'],
    ];
}

function layout_start($title, $base, $active = '') {
    global $conn, $me;
    $logged = !empty($_SESSION['user_id']);
    $role   = $_SESSION['role'] ?? '';
    $me = null; $notifs = []; $unread = 0;
    if ($logged) {
        $st = $conn->prepare("SELECT * FROM users WHERE user_id=?");
        $st->bind_param('i', $_SESSION['user_id']); $st->execute();
        $me = $st->get_result()->fetch_assoc();
        if (!$me) { session_destroy(); header('Location: ' . $base . 'login.php'); exit; }
        if ($role === 'student') check_overdue($conn, $me['user_id']);
        $st = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY notif_id DESC LIMIT 6");
        $st->bind_param('i', $me['user_id']); $st->execute();
        $notifs = $st->get_result()->fetch_all(MYSQLI_ASSOC);
        $st = $conn->prepare("SELECT COUNT(*) c FROM notifications WHERE user_id=? AND is_read=0");
        $st->bind_param('i', $me['user_id']); $st->execute();
        $unread = $st->get_result()->fetch_assoc()['c'];
    }
    $searchAction = $role === 'student' ? $base . 'student/explore.php' : ($role === 'librarian' ? $base . 'librarian/inventory.php' : $base . 'index.php');
    $q = $_GET['q'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | BAPS e-Library</title>
<link rel="icon" href="<?= $base ?>assets/images/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Anton&family=Oswald:wght@500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body class="<?= $logged ? 'app' : 'public' ?>" data-base="<?= $base ?>" data-role="<?= e($role ?: 'guest') ?>">

<header class="topbar">
  <a class="ribbon" href="<?= $base ?>index.php" title="BAPS e-Library"><img src="<?= $base ?>assets/images/logo.png" alt="BAPS e-Library logo"></a>
  <?php if ($logged): ?><button class="menu-btn" id="menuBtn" aria-label="Menu"><i class="fa-solid fa-bars"></i></button><?php endif; ?>
  <a class="brand" href="<?= $base ?>index.php">BAPS <b>e</b>-Library</a>
  <form class="search" action="<?= $searchAction ?>" method="get" role="search">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search title, author, ISBN..." aria-label="Search">
    <button type="submit" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
  </form>
  <?php if ($logged): ?>
  <div class="top-right">
    <div class="dropdown">
      <button class="icon-btn" id="bellBtn" aria-label="Notifications"><i class="fa-solid fa-bell"></i><?php if ($unread): ?><span class="badge" id="bellCount"><?= $unread ?></span><?php endif; ?></button>
      <div class="drop-menu" id="bellMenu">
        <h4>Notifications</h4>
        <?php if (!$notifs): ?><div class="n">No notifications yet.</div><?php endif; ?>
        <?php foreach ($notifs as $n): ?>
          <div class="n <?= $n['is_read'] ? '' : 'unread' ?>"><?= e($n['message']) ?><small><?= date('M d, Y g:i A', strtotime($n['created_at'])) ?></small></div>
        <?php endforeach; ?>
      </div>
    </div>
    <a class="user-chip" href="<?= $base ?>settings.php" title="My profile">
      <img src="<?= e(pic_url($me['profile_pic'], $base)) ?>" alt="Profile"><span><?= e($me['full_name']) ?></span>
    </a>
  </div>
  <?php else: ?>
  <nav class="nav-links">
    <a class="link" href="<?= $base ?>login.php">LOGIN</a>
    <a class="btn-yellow" href="<?= $base ?>register.php">SIGN UP</a>
  </nav>
  <?php endif; ?>
</header>

<?php if ($logged): ?>
<div class="overlay" id="overlay"></div>
<aside class="sidebar" id="sidebar">
  <?php foreach (menu_for($role) as $m): ?>
    <a href="<?= $base . $m[2] ?>" class="<?= $active === $m[0] ? 'active' : '' ?>"><i class="fa-solid <?= $m[3] ?>"></i><?= $m[1] ?></a>
  <?php endforeach; ?>
  <div class="spacer"></div>
  <a href="<?= $base ?>settings.php" class="<?= $active === 'settings' ? 'active' : '' ?>"><i class="fa-solid fa-gear"></i>Settings</a>
  <a href="<?= $base ?>logout.php"><i class="fa-solid fa-right-from-bracket"></i>Log Out</a>
</aside>
<?php endif; ?>

<main class="main">
<?php
}

// Book card (clickable, opens the preview modal)
function book_card($b, $base) {
    $data = [
        'id' => (int)$b['book_id'], 'title' => $b['title'], 'author' => $b['author'], 'isbn' => $b['isbn'],
        'category' => $b['category'], 'abstract' => $b['abstract'], 'pages' => (int)$b['pages'],
        'available' => (int)$b['available_copies'], 'total' => (int)$b['total_copies'],
        'rating' => round((float)$b['avg_rating'], 1), 'borrowed' => (int)$b['times_borrowed'],
        'cover' => cover_url($b['cover'], $base), 'fav' => !empty($b['is_fav']),
    ];
    return '<button type="button" class="book" data-book="' . e(json_encode($data)) . '">'
        . '<div class="cv"><img src="' . e($data['cover']) . '" alt="Cover of ' . e($b['title']) . '" loading="lazy"></div>'
        . '<h4>' . e($b['title']) . '</h4><p>' . e($b['category']) . '</p>'
        . ($b['avg_rating'] > 0 ? stars((float)$b['avg_rating']) : '') . '</button>';
}

function layout_end($base) {
    $role = $_SESSION['role'] ?? 'guest';
    ?>
</main>

<!-- Book preview modal (opens when a book is clicked) -->
<div class="modal" id="bookModal" aria-hidden="true">
  <div class="modal-box">
    <button class="modal-x" data-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
    <div class="detail">
      <img id="bmCover" src="" alt="">
      <div>
        <span class="pill ok" id="bmCat"></span>
        <h3 id="bmTitle"></h3>
        <p id="bmAuthor" style="color:var(--muted);font-size:.85rem"></p>
        <div id="bmStars" style="margin-top:6px"></div>
        <div class="meta">
          <span><i class="fa-solid fa-file-lines"></i> <b id="bmPages"></b> pages</span>
          <span><i class="fa-solid fa-users"></i> Borrowed <b id="bmBorrowed"></b> times</span>
          <span><i class="fa-solid fa-barcode"></i> <b id="bmIsbn"></b></span>
          <span id="bmStock"></span>
        </div>
        <label>Abstract / Short intro</label>
        <p class="abstract" id="bmAbstract"></p>
        <div class="modal-actions">
        <?php if ($role === 'student'): ?>
          <form method="post" action="<?= $base ?>student/borrow.php">
            <input type="hidden" name="book_id" id="bmBorrowId">
            <button class="btn yellow" id="bmBorrowBtn" type="submit"><i class="fa-solid fa-book"></i> Borrow Now</button>
          </form>
          <form method="post" action="<?= $base ?>student/favorite.php">
            <input type="hidden" name="book_id" id="bmFavId">
            <input type="hidden" name="back" value="<?= e($_SERVER['REQUEST_URI']) ?>">
            <button class="btn ghost" type="submit" id="bmFavBtn"><i class="fa-regular fa-heart"></i> Favorite</button>
          </form>
        <?php elseif ($role === 'librarian'): ?>
          <a class="btn" id="bmEditLink" href="<?= $base ?>librarian/inventory.php"><i class="fa-solid fa-pen"></i> Manage in Inventory</a>
        <?php else: ?>
          <a class="btn yellow" href="<?= $base ?>login.php"><i class="fa-solid fa-right-to-bracket"></i> Login to Borrow</a>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="footer">&copy; <?= date('Y') ?> BAPS e-Library &middot; Book Access and Processing System</div>
<script src="<?= $base ?>assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
</body>
</html>
<?php
}

// Notify a student once per day about each overdue book
function check_overdue($conn, $uid) {
    $st = $conn->prepare("SELECT b.title, t.due_date FROM transactions t JOIN books b ON b.book_id=t.book_id
                          WHERE t.user_id=? AND t.status='borrowed' AND t.due_date < CURDATE()");
    $st->bind_param('i', $uid); $st->execute();
    foreach ($st->get_result() as $r) {
        $msg = 'Overdue: "' . $r['title'] . '" was due on ' . date('M d, Y', strtotime($r['due_date'])) . '. Please return it.';
        $c = $conn->prepare("SELECT 1 FROM notifications WHERE user_id=? AND message=? AND DATE(created_at)=CURDATE()");
        $c->bind_param('is', $uid, $msg); $c->execute();
        if (!$c->get_result()->num_rows) notify($conn, $uid, $msg);
    }
}
