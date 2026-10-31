<?php
// Student sends a borrow request (CREATE) - librarian must approve it
require_once __DIR__ . '/../config/db.php';
$base = '../';
require_role('student', $base);
$uid = (int)$_SESSION['user_id'];
$bid = (int)($_POST['book_id'] ?? 0);
$back = 'explore.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$bid) { header("Location: $back"); exit; }

$book = $conn->query("SELECT * FROM books WHERE book_id=$bid")->fetch_assoc();
$dup  = $conn->query("SELECT 1 FROM transactions WHERE user_id=$uid AND book_id=$bid AND status IN ('pending','borrowed','return_requested')")->num_rows;
$cnt  = $conn->query("SELECT COUNT(*) c FROM transactions WHERE user_id=$uid AND status IN ('pending','borrowed','return_requested')")->fetch_assoc()['c'];
$over = $conn->query("SELECT COUNT(*) c FROM transactions WHERE user_id=$uid AND status='borrowed' AND due_date < CURDATE()")->fetch_assoc()['c'];

if (!$book) flash('Book not found.', 'error');
elseif ($book['available_copies'] < 1) flash('Sorry, this book is out of stock.', 'error');
elseif ($dup) flash('You can only borrow one copy of each book. You already requested or borrowed this one.', 'error');
elseif ($over > 0) flash('Please return your overdue book(s) first.', 'error');
elseif ($cnt >= MAX_BOOKS_PER_STUDENT) flash('You can only borrow up to ' . MAX_BOOKS_PER_STUDENT . ' different books at a time (including pending requests).', 'error');
else {
    $st = $conn->prepare("INSERT INTO transactions (user_id, book_id, status) VALUES (?, ?, 'pending')");
    $st->bind_param('ii', $uid, $bid); $st->execute();
    $name = $conn->query("SELECT full_name FROM users WHERE user_id=$uid")->fetch_assoc()['full_name'];
    $libs = $conn->query("SELECT user_id FROM users WHERE role='librarian'");
    foreach ($libs as $l) notify($conn, $l['user_id'], "$name requested to borrow \"{$book['title']}\".");
    flash('Request sent! Please wait for the librarian to approve it.');
    $back = 'my_books.php';
}
header("Location: $back");
