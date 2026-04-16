<?php
session_start();
include "db_connect.php";

header("Content-Type: application/json");

if (!isset($_SESSION['admin_username'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Admin login required."
    ]);
    exit();
}

$result = mysqli_query($conn, "SELECT note_id, title, subject, file_path, uploader_name, status FROM notes ORDER BY note_id DESC");

$notes = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $notes[] = $row;
    }
}

echo json_encode([
    "success" => true,
    "notes" => $notes
]);
?>
