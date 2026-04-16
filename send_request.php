<?php
session_start();
include "db_connect.php";
include "notification_helpers.php";

header("Content-Type: application/json");

ensureNotificationColumns($conn);

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Please login first"]);
    exit();
}

$requesterName = $_SESSION['username'];
$bookId = (int) ($_POST['book_id'] ?? 0);
$status = 'pending';

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid book selected."]);
    exit();
}

$bookStmt = mysqli_prepare($conn, "SELECT owner, user_id FROM books WHERE book_id = ?");
mysqli_stmt_bind_param($bookStmt, "i", $bookId);
mysqli_stmt_execute($bookStmt);
$bookResult = mysqli_stmt_get_result($bookStmt);
$bookRow = mysqli_fetch_assoc($bookResult);
mysqli_stmt_close($bookStmt);

if (!$bookRow) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Book not found."]);
    exit();
}

$requesterId = null;
$userStmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE name = ?");
mysqli_stmt_bind_param($userStmt, "s", $requesterName);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$userRow = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($userStmt);

if ($userRow) {
    $requesterId = (int) $userRow['user_id'];
}

$ownerName = $bookRow['owner'];

$insertStmt = mysqli_prepare(
    $conn,
    "INSERT INTO swap_requests (book_id, requester_id, status, owner_name, requester_name, owner_seen, requester_seen) VALUES (?, ?, ?, ?, ?, 0, 1)"
);
mysqli_stmt_bind_param($insertStmt, "iisss", $bookId, $requesterId, $status, $ownerName, $requesterName);

if (!mysqli_stmt_execute($insertStmt)) {
    mysqli_stmt_close($insertStmt);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Could not create request."]);
    exit();
}

mysqli_stmt_close($insertStmt);

echo json_encode(["success" => true, "message" => "Request successfully sent."]);
exit();
?>
