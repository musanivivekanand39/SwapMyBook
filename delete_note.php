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

if ($noteId <= 0) {
    header("Location: admin_notes.html");
    exit();
}

$selectStmt = mysqli_prepare($conn, "SELECT file_path FROM notes WHERE note_id = ? LIMIT 1");
mysqli_stmt_bind_param($selectStmt, "i", $noteId);
mysqli_stmt_execute($selectStmt);
$result = mysqli_stmt_get_result($selectStmt);
$note = mysqli_fetch_assoc($result);
mysqli_stmt_close($selectStmt);

if (!$note) {
    header("Location: admin_notes.html");
    exit();
}

$deleteStmt = mysqli_prepare($conn, "DELETE FROM notes WHERE note_id = ?");
mysqli_stmt_bind_param($deleteStmt, "i", $noteId);
mysqli_stmt_execute($deleteStmt);
$deletedRows = mysqli_stmt_affected_rows($deleteStmt);
mysqli_stmt_close($deleteStmt);

if ($deletedRows > 0) {
    $filePath = trim($note['file_path'] ?? '');
    if ($filePath !== '') {
        $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
}

header("Location: admin_notes.html");
exit();
?>
