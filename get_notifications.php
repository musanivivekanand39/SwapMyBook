<?php
session_start();
include "db_connect.php";
include "notification_helpers.php";

header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    echo json_encode([
        "success" => false,
        "count" => 0
    ]);
    exit();
}

$user = $_SESSION['username'];

ensureNotificationColumns($conn);

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM swap_requests
     WHERE (owner_name = ? AND status = 'pending' AND owner_seen = 0)
        OR (requester_name = ? AND status = 'accepted' AND requester_seen = 0)"
);
mysqli_stmt_bind_param($stmt, "ss", $user, $user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

echo json_encode([
    "success" => true,
    "count" => (int) ($row['total'] ?? 0)
]);
?>
