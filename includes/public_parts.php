<?php
// Pieces shared by the public pages (index, login, register)

function latest_books($conn, $limit = 8) {
    return $conn->query(book_select() . " ORDER BY b.book_id DESC LIMIT " . (int)$limit)->fetch_all(MYSQLI_ASSOC);
}
function top_rated_books($conn, $limit = 5) {
    $rows = $conn->query(book_select() . " WHERE (SELECT COUNT(*) FROM ratings r WHERE r.book_id=b.book_id) > 0 ORDER BY avg_rating DESC, times_borrowed DESC, b.book_id DESC LIMIT " . (int)$limit)->fetch_all(MYSQLI_ASSOC);
    return $rows;
}

function welcome_card() { ?>
  <section class="panel hero" aria-label="Welcome">
    <div class="hero-text">
      <div class="hero-eyebrow"><i class="fa-solid fa-graduation-cap"></i> BOOK ACCESS &amp; PROCESSING SYSTEM</div>
      <h1>Read Freely.<br><span class="hl">Borrow Wisely.</span></h1>
      <p class="hero-desc">Your modern academic gateway. Explore curated collections, request titles in seconds, and stay on top of your learning journey.</p>
      <div class="hero-features">
        <span class="hero-chip"><i class="fa-solid fa-bolt"></i> Fast Requests</span>
        <span class="hero-chip"><i class="fa-solid fa-shield-halved"></i> 24/7 Catalog</span>
        <span class="hero-chip"><i class="fa-solid fa-bell"></i> Due Alerts</span>
      </div>
    </div>
    <div class="hero-side">
      <div class="book-stack" aria-hidden="true">
        <div class="spine s1"><span class="spine-title">LITERATURE</span></div>
        <div class="spine s2"><span class="spine-title">SCIENCE</span></div>
        <div class="spine s3"><span class="spine-title">TECHNOLOGY</span></div>
        <div class="spine s4"><span class="spine-title">RESEARCH</span></div>
        <div class="spine s5"><span class="spine-title">HISTORY</span></div>
      </div>
      <div class="hero-quote">
        <i class="fa-solid fa-quote-left"></i>
        <span>"A library is not a luxury but one of the necessities of life."</span>
      </div>
    </div>
  </section>
<?php }

function newly_added_panel($books, $base, $id = 'newRow') { ?>
  <section class="panel">
    <div class="panel-head">
      <h2><i class="fa-solid fa-sparkles" style="color:var(--yellow-dark);font-size:0.95em"></i> Newly Added Books</h2>
      <div class="carousel-nav">
        <button type="button" data-scroll="<?= $id ?>" data-dir="-1" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" data-scroll="<?= $id ?>" data-dir="1" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>
    <?php if (!$books): ?><div class="empty"><i class="fa-solid fa-book"></i>No books yet.</div><?php endif; ?>
    <div class="scroll-row" id="<?= $id ?>"><?php foreach ($books as $b) echo book_card($b, $base); ?></div>
  </section>
<?php }

function top_rated_panel($books, $base, $id = 'topRow') { ?>
  <section class="panel">
    <div class="panel-head">
      <h2><i class="fa-solid fa-crown" style="color:var(--yellow-dark);font-size:0.95em"></i> High Rating Books</h2>
      <div class="carousel-nav">
        <button type="button" data-scroll="<?= $id ?>" data-dir="-1" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
        <button type="button" data-scroll="<?= $id ?>" data-dir="1" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
      </div>
    </div>
    <?php if (!$books): ?><div class="empty"><i class="fa-solid fa-star"></i>No rated books yet.</div><?php endif; ?>
    <div class="scroll-row" id="<?= $id ?>"><?php foreach ($books as $b) echo book_card($b, $base); ?></div>
  </section>
<?php }
