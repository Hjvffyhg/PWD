<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
$base = '';
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
layout_start('About', $base, 'about');
?>
<?php $isLib = ($_SESSION['role'] ?? '') === 'librarian'; ?>
<h1 class="page-title">About BAPS e-Library <small>Book Access and Processing System</small></h1>
<?php if ($isLib): ?>
<div class="grid g2">
  <section class="panel">
    <h2>For Librarians</h2>
    <p class="abstract" style="max-height:none">BAPS e-Library gives you one place to run the school library. Keep the book inventory up to date, register and manage student accounts, and handle every borrow and return request without paper logbooks.</p>
    <h2 style="margin-top:20px">What you can do</h2>
    <p class="abstract" style="max-height:none">Add, edit and remove books with their cover, ISBN, category, pages and stock. Register students and update their details. Review the dashboard for titles, available copies, pending requests, books out and overdue books.</p>
  </section>
  <section class="panel">
    <h2>Daily workflow</h2>
    <p class="abstract" style="max-height:none"><b>1.</b> Open <i>Borrowed/Return Request</i> and approve or reject pending requests.<br>
    <b>2.</b> Hand the book to the student &mdash; the due date is set automatically (<?= LOAN_DAYS ?> days).<br>
    <b>3.</b> When a book comes back, confirm the return so the stock is updated.<br>
    <b>4.</b> Check overdue books and the <i>Report</i> page to follow up with students.<br><br>
    <b>Penalty:</b> &#8369;<?= FINE_PER_DAY ?> per day is added automatically for late books.</p>
  </section>
</div>
<?php else: ?>
<div class="grid g2">
  <section class="panel">
    <h2>For Students</h2>
    <p class="abstract" style="max-height:none">BAPS e-Library lets you find books and borrow them online, anytime. Browse the catalog, read a short intro of each title, save your favorites and keep track of your due dates from your own dashboard.</p>
    <h2 style="margin-top:20px">What you can do</h2>
    <p class="abstract" style="max-height:none">Explore books by title, author, ISBN or category. Send borrow requests, see the status of your borrowed books, mark books as favorites and review your borrowing history and penalties.</p>
  </section>
  <section class="panel">
    <h2>How to borrow</h2>
    <p class="abstract" style="max-height:none"><b>1.</b> Go to <i>Explore</i> and click a book to read its details.<br>
    <b>2.</b> Press <i>Borrow Now</i> and wait for the librarian's approval.<br>
    <b>3.</b> Get the book from the library and return it before the due date (<?= LOAN_DAYS ?> days).<br>
    <b>4.</b> Check <i>My Borrowed Books</i> to follow the status of your requests.<br><br>
    <b>Penalty:</b> &#8369;<?= FINE_PER_DAY ?> per day for late books.</p>
  </section>
</div>
<?php endif; ?>
<?php layout_end($base);
