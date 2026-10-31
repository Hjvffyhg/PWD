<?php
// Toggle favorite (CREATE / DELETE)
require_once __DIR__ . '/../config/db.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];
$bid = (int)($_POST['book_id'] ?? 0);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bid) {
    $has = $conn->query("SELECT 1 FROM favorites WHERE user_id=$uid AND book_id=$bid")->num_rows;
    if ($has) { $conn->query("DELETE FROM favorites WHERE user_id=$uid AND book_id=$bid"); flash('Removed from favorites.', 'info'); }
    else      { $conn->query("INSERT INTO favorites (user_id, book_id) VALUES ($uid, $bid)"); flash('Added to favorites!'); }
}
$back = $_POST['back'] ?? 'favorites.php';
// only allow redirects inside this site
if (!preg_match('#^/[^/\\\\]#', $back) || strpos($back, '//') !== false) $back = 'favorites.php';
header('Location: ' . $back);
