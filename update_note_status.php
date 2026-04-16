<?php
session_start();
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_notes.html");
    exit();
}

if (!isset($_SESSION['admin_username'])) {
    header("Location: admin_login.html");
    exit();
}

$noteId = (int) ($_POST['note_id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if ($noteId <= 0 || !in_array($status, ['approved', 'rejected'], true)) {
    header("Location: admin_notes.html");
    exit();
}

$stmt = mysqli_prepare($conn, "UPDATE notes SET status = ? WHERE note_id = ?");
mysqli_stmt_bind_param($stmt, "si", $status, $noteId);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: admin_notes.html");
exit();
?>
