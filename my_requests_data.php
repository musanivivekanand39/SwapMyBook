<?php
session_start();
include "db_connect.php";
include "notification_helpers.php";

header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}

$user = $_SESSION['username'];

ensureNotificationColumns($conn);

$markOwnerSeenStmt = mysqli_prepare(
    $conn,
    "UPDATE swap_requests
     SET owner_seen = 1
     WHERE owner_name = ? AND status = 'pending' AND owner_seen = 0"
);
mysqli_stmt_bind_param($markOwnerSeenStmt, "s", $user);
mysqli_stmt_execute($markOwnerSeenStmt);
mysqli_stmt_close($markOwnerSeenStmt);

$markRequesterSeenStmt = mysqli_prepare(
    $conn,
    "UPDATE swap_requests
     SET requester_seen = 1
     WHERE requester_name = ? AND status = 'accepted' AND requester_seen = 0"
);
mysqli_stmt_bind_param($markRequesterSeenStmt, "s", $user);
mysqli_stmt_execute($markRequesterSeenStmt);
mysqli_stmt_close($markRequesterSeenStmt);

$sentRequests = [];
$sentStmt = mysqli_prepare(
    $conn,
    "SELECT swap_requests.request_id, swap_requests.book_id, swap_requests.status, books.title, books.contact, swap_requests.owner_name
     FROM swap_requests
     LEFT JOIN books ON swap_requests.book_id = books.book_id
     WHERE swap_requests.requester_name = ?"
);
mysqli_stmt_bind_param($sentStmt, "s", $user);
mysqli_stmt_execute($sentStmt);
$sentResult = mysqli_stmt_get_result($sentStmt);

while ($row = mysqli_fetch_assoc($sentResult)) {
    $sentRequests[] = $row;
}

mysqli_stmt_close($sentStmt);

$receivedRequests = [];
$receivedStmt = mysqli_prepare(
    $conn,
    "SELECT swap_requests.request_id, swap_requests.requester_name, swap_requests.status, books.title
     FROM swap_requests
     INNER JOIN books ON swap_requests.book_id = books.book_id
     WHERE swap_requests.owner_name = ?"
);
mysqli_stmt_bind_param($receivedStmt, "s", $user);
mysqli_stmt_execute($receivedStmt);
$receivedResult = mysqli_stmt_get_result($receivedStmt);

while ($row = mysqli_fetch_assoc($receivedResult)) {
    $receivedRequests[] = $row;
}

mysqli_stmt_close($receivedStmt);

echo json_encode([
    "success" => true,
    "username" => $user,
    "sentRequests" => $sentRequests,
    "receivedRequests" => $receivedRequests
]);
exit();
?>
