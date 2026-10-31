<?php
require_once __DIR__ . '/config/db.php';
if (!empty($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $st = $conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
    $st->bind_param('i', $_SESSION['user_id']); $st->execute();
}
