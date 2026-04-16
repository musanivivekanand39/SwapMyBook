<?php
include "db_connect.php";

header("Content-Type: application/json");

$status = 'approved';
$stmt = mysqli_prepare($conn, "SELECT note_id, title, subject, file_path, uploader_name FROM notes WHERE status = ? ORDER BY note_id DESC");
mysqli_stmt_bind_param($stmt, "s", $status);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notes[] = $row;
}

mysqli_stmt_close($stmt);

echo json_encode($notes);
?>
