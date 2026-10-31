<?php
// Shared helper functions

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

// Create the 4 default librarian accounts once (passwords are hashed)
function seed_librarians($conn) {
    $r = $conn->query("SELECT COUNT(*) c FROM users WHERE role='librarian'")->fetch_assoc();
    if ($r['c'] > 0) return;
    $libs = [
        ['Prof. Nico Barlizo', 'nicofbarlizo@cccc.edu.ph', 'barlizonico', 'nicobarlizo1', 'assets/images/librarians/nico.jpg'],
        ['Prof. Mark Justine Almario', 'mjalmario@cccc.edu.ph', 'almariojustine2', 'justinealmario2', 'assets/images/librarians/mark.jpg'],
        ['Prof. Byron Pajac', 'bypajac@cccc.edu.ph', 'pajacbyron', 'byronpajac3', 'assets/images/librarians/byron.jpg'],
        ['Prof. Juan Miguel Saligumba', 'jmsaligumbao@cccc.edu.ph', 'saligumbamiguel', 'miguelsaligumba4', 'assets/images/librarians/miguel.jpg'],
    ];
    $st = $conn->prepare("INSERT INTO users (full_name,email,username,password,role,profile_pic) VALUES (?,?,?,?, 'librarian', ?)");
    foreach ($libs as $l) {
        $hash = password_hash($l[3], PASSWORD_DEFAULT);
        $st->bind_param('sssss', $l[0], $l[1], $l[2], $hash, $l[4]);
        $st->execute();
    }
}

function pic_url($path, $base) {
    return $path ? $base . $path : $base . 'assets/images/default-avatar.svg';
}
function cover_url($file, $base) {
    return $file ? $base . 'uploads/covers/' . $file : $base . 'assets/images/no-cover.svg';
}

// Login / role guards
function require_role($role, $base) {
    if (empty($_SESSION['user_id']) || $_SESSION['role'] !== $role) {
        header('Location: ' . $base . 'login.php');
        exit;
    }
}
function flash($msg, $type = 'success') { $_SESSION['flash'] = [$msg, $type]; }
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        [$m, $t] = $_SESSION['flash']; unset($_SESSION['flash']);
        echo '<div class="alert alert-' . e($t) . '">' . e($m) . '</div>';
    }
}

function notify($conn, $user_id, $msg) {
    $st = $conn->prepare("INSERT INTO notifications (user_id,message) VALUES (?,?)");
    $st->bind_param('is', $user_id, $msg); $st->execute();
}

// Handle image upload. Returns saved file name or null.
function save_image($field, $folder, &$error) {
    if (empty($_FILES[$field]['name'])) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) { $error = 'Upload failed.'; return null; }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) { $error = 'Only JPG, PNG or WEBP images are allowed.'; return null; }
    if ($f['size'] > 2 * 1024 * 1024) { $error = 'Image must be 2MB or smaller.'; return null; }
    $name = uniqid('img_', true) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], __DIR__ . '/../uploads/' . $folder . '/' . $name)) { $error = 'Could not save the image.'; return null; }
    return $name;
}

function stars($avg) {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($avg >= $i) $out .= '<i class="fa-solid fa-star"></i>';
        elseif ($avg >= $i - 0.5) $out .= '<i class="fa-solid fa-star-half-stroke"></i>';
        else $out .= '<i class="fa-regular fa-star"></i>';
    }
    return '<span class="stars">' . $out . '</span>';
}

// Book query with average rating + times borrowed
function book_select() {
    return "SELECT b.*, COALESCE((SELECT AVG(rating) FROM ratings r WHERE r.book_id=b.book_id),0) AS avg_rating,
            (SELECT COUNT(*) FROM transactions t WHERE t.book_id=b.book_id AND t.status IN ('borrowed','return_requested','returned')) AS times_borrowed
            FROM books b";
}

define('LOAN_DAYS', 7);
define('FINE_PER_DAY', 5);
define('MAX_BOOKS_PER_STUDENT', 5);   // max different books a student can have borrowed/requested at once (1 copy each)

// Same as book_select() but also tells whether the student favorited the book
function book_select_for($uid) {
    $uid = (int)$uid;
    return "SELECT b.*, COALESCE((SELECT AVG(rating) FROM ratings r WHERE r.book_id=b.book_id),0) AS avg_rating,
            (SELECT COUNT(*) FROM transactions t WHERE t.book_id=b.book_id AND t.status IN ('borrowed','return_requested','returned')) AS times_borrowed,
            (SELECT COUNT(*) FROM favorites f WHERE f.book_id=b.book_id AND f.user_id=$uid) AS is_fav
            FROM books b";
}
function fine_for($due) {
    $days = (int)floor((strtotime(date('Y-m-d')) - strtotime($due)) / 86400);
    return $days > 0 ? [$days, $days * FINE_PER_DAY] : [0, 0];
}
