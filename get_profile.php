<?php
session_start();
include "db_connect.php";

header("Content-Type: application/json");

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Please login first."]);
    exit();
}

$username = $_SESSION['username'];

$userStmt = mysqli_prepare($conn, "SELECT name, email FROM users WHERE name = ? LIMIT 1");
mysqli_stmt_bind_param($userStmt, "s", $username);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$userRow = mysqli_fetch_assoc($userResult);
mysqli_stmt_close($userStmt);

$booksCount = 0;
$booksStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM books WHERE owner = ?");
mysqli_stmt_bind_param($booksStmt, "s", $username);
mysqli_stmt_execute($booksStmt);
$booksResult = mysqli_stmt_get_result($booksStmt);
$booksRow = mysqli_fetch_assoc($booksResult);
mysqli_stmt_close($booksStmt);

if ($booksRow) {
    $booksCount = (int) $booksRow['total'];
}

$sentCount = 0;
$sentStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM swap_requests WHERE requester_name = ?");
mysqli_stmt_bind_param($sentStmt, "s", $username);
mysqli_stmt_execute($sentStmt);
$sentResult = mysqli_stmt_get_result($sentStmt);
$sentRow = mysqli_fetch_assoc($sentResult);
mysqli_stmt_close($sentStmt);

if ($sentRow) {
    $sentCount = (int) $sentRow['total'];
}

$receivedCount = 0;
$receivedStmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM swap_requests WHERE owner_name = ?");
mysqli_stmt_bind_param($receivedStmt, "s", $username);
mysqli_stmt_execute($receivedStmt);
$receivedResult = mysqli_stmt_get_result($receivedStmt);
$receivedRow = mysqli_fetch_assoc($receivedResult);
mysqli_stmt_close($receivedStmt);

if ($receivedRow) {
    $receivedCount = (int) $receivedRow['total'];
}

echo json_encode([
    "success" => true,
    "name" => $userRow['name'] ?? $username,
    "email" => $userRow['email'] ?? '',
    "initial" => strtoupper(substr($username, 0, 1)),
    "booksCount" => $booksCount,
    "sentCount" => $sentCount,
    "receivedCount" => $receivedCount
]);
exit();
?>
