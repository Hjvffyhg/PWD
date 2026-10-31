<?php
// Database connection (XAMPP default: user root, no password)
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'baps_elibrary';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed. Make sure MySQL is running in XAMPP and the database was imported. Error: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../includes/functions.php';
seed_librarians($conn);   // creates the 4 default librarian accounts on first run
