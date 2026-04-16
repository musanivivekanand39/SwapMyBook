<?php
session_start();
include "db_connect.php";
include "notification_helpers.php";

ensureNotificationColumns($conn);

if (!isset($_SESSION['username'])) {
    echo "Please login first.";
    exit();
}

$requestId = (int) ($_GET['id'] ?? 0);
$status = trim($_GET['status'] ?? '');
$allowedStatuses = ['accepted', 'rejected'];

if ($requestId <= 0 || !in_array($status, $allowedStatuses, true)) {
    header("Location: my_requests.html");
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "UPDATE swap_requests
     SET status = ?, owner_seen = 1, requester_seen = 0
     WHERE request_id = ? AND owner_name = ?"
);
$ownerName = $_SESSION['username'];
mysqli_stmt_bind_param($stmt, "sis", $status, $requestId, $ownerName);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

header("Location: my_requests.html");
exit();
?>
