<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/layout.php';
$base = '';
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$librarians = [
  ['CHIEF LIBRARIAN', 'Prof. Nico Barlizo', '0968-493-1964', 'nicofbarlizo@cccc.edu.ph', 'nico.jpg'],
  ['LIBRARIAN', 'Prof. Mark Justine Almario', '0945-321-9876', 'mjalmario@cccc.edu.ph', 'mark.jpg'],
  ['LIBRARIAN', 'Prof. Juan Miguel Saligumba', '0986-987-1234', 'jmsaligumbao@cccc.edu.ph', 'miguel.jpg'],
  ['LIBRARIAN', 'Prof. Byron Pajac', '0922-345-7896', 'bypajac@cccc.edu.ph', 'byron.jpg'],
];
$db_pics = [];
$res = $conn->query("SELECT email, profile_pic FROM users WHERE role='librarian'");
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $db_pics[$row['email']] = $row['profile_pic'];
  }
}
layout_start('Contact', $base, 'contact');
?>
<h1 class="page-title">Contact <small>Reach our librarians for questions about books, borrowing and returns.</small>
</h1>
<div class="contacts">
  <?php foreach ($librarians as $l):
    $pic = !empty($db_pics[$l[3]]) ? pic_url($db_pics[$l[3]], $base) : $base . 'assets/images/librarians/' . $l[4];
  ?>
    <article class="contact-card">
      <img src="<?= e($pic) ?>" alt="<?= e($l[1]) ?>">
      <div class="info">
        <span class="role"><?= e($l[0]) ?></span>
        <h4><?= e($l[1]) ?></h4>
        <p><i class="fa-solid fa-phone"></i><a href="tel:<?= e(str_replace('-', '', $l[2])) ?>"><?= e($l[2]) ?></a></p>
        <p><i class="fa-solid fa-envelope"></i><a href="mailto:<?= e($l[3]) ?>"><?= e($l[3]) ?></a></p>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<?php layout_end($base);
