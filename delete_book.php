<?php
session_start();
include "db_connect.php";

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);
    exit();
}

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit();
}

$bookId = (int) ($_POST['book_id'] ?? 0);
$username = trim($_SESSION['username']);
$normalizedUsername = strtolower($username);

if ($bookId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid book selected."
    ]);
    exit();
}

$selectStmt = mysqli_prepare($conn, "SELECT image, owner FROM books WHERE book_id = ? LIMIT 1");
mysqli_stmt_bind_param($selectStmt, "i", $bookId);
mysqli_stmt_execute($selectStmt);
$result = mysqli_stmt_get_result($selectStmt);
$book = mysqli_fetch_assoc($result);
mysqli_stmt_close($selectStmt);

if (!$book) {
    http_response_code(404);
    echo json_encode([
        "success" => false,
        "message" => "Book not found."
    ]);
    exit();
}

$bookOwner = trim($book['owner'] ?? '');

if (strtolower($bookOwner) !== $normalizedUsername) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Only the owner can delete this book."
    ]);
    exit();
}

$deleteStmt = mysqli_prepare($conn, "DELETE FROM books WHERE book_id = ? AND LOWER(TRIM(owner)) = ?");
mysqli_stmt_bind_param($deleteStmt, "is", $bookId, $normalizedUsername);

if (!mysqli_stmt_execute($deleteStmt)) {
    mysqli_stmt_close($deleteStmt);
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not delete the book."
    ]);
    exit();
}

$deletedRows = mysqli_stmt_affected_rows($deleteStmt);
mysqli_stmt_close($deleteStmt);

if ($deletedRows < 1) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Could not delete the book."
    ]);
    exit();
}

$imageName = trim($book['image'] ?? '');
if ($imageName !== '') {
    $imagePath = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . basename($imageName);
    if (is_file($imagePath)) {
        @unlink($imagePath);
    }
}

echo json_encode([
    "success" => true,
    "message" => "Book deleted successfully."
]);
exit();
?>
