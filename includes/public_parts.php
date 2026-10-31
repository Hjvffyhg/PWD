<?php
// Pieces shared by the public pages (index, login, register)

function latest_books($conn, $limit = 8) {
    return $conn->query(book_select() . " ORDER BY b.book_id DESC LIMIT " . (int)$limit)->fetch_all(MYSQLI_ASSOC);
}
function top_rated_books($conn, $limit = 5) {
    $rows = $conn->query(book_select() . " ORDER BY avg_rating DESC, times_borrowed DESC, b.book_id DESC LIMIT " . (int)$limit)->fetch_all(MYSQLI_ASSOC);
    return $rows;
}

function welcome_card() { ?>
  <section class="panel hero" aria-label="Welcome">
    <div>
      <h3>Welcome to</h3>
      <h1>BAPS <span class="hl">e</span>-Library</h1>
      <div class="tag">BOOK ACCESS AND PROCESSING SYSTEM</div>
    </div>
    <div class="hero-side">
      <i class="fa-solid fa-book-open-reader"></i>
      <p><b>Instant Book Access:</b> Enables students and staff to search, request, and borrow books online from anywhere at any time.</p>
    </div>
  </section>
<?php }

function newly_added_panel($books, $base, $id = 'newRow') { ?>
  <section class="panel">
    <div class="carousel-nav">
      <button type="button" data-scroll="<?= $id ?>" data-dir="-1" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
      <button type="button" data-scroll="<?= $id ?>" data-dir="1" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
    </div>
    <h2 style="margin-top:-30px">Newly Added Books</h2>
    <?php if (!$books): ?><div class="empty"><i class="fa-solid fa-book"></i>No books yet.</div><?php endif; ?>
    <div class="scroll-row" id="<?= $id ?>"><?php foreach ($books as $b) echo book_card($b, $base); ?></div>
  </section>
<?php }
