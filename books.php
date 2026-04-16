<?php
session_start();
include "db_connect.php";

header("Content-Type: application/json");

$subject = trim($_GET['subject'] ?? '');

if ($subject === '') {
    echo json_encode([]);
    exit();
}

$normalizedSubject = strtolower($subject);

$stmt = mysqli_prepare(
    $conn,
    "SELECT books.book_id, books.title, books.author, books.image, books.owner, books.user_id, subjects.subject_name
     FROM books
     INNER JOIN subjects ON books.subject_id = subjects.subject_id
     WHERE LOWER(subjects.subject_name) = ?"
);
mysqli_stmt_bind_param($stmt, "s", $normalizedSubject);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$books = [];
$sessionUsername = strtolower(trim($_SESSION['username'] ?? ''));
while ($row = mysqli_fetch_assoc($result)) {
    $owner = strtolower(trim($row['owner'] ?? ''));
    $row['can_delete'] = $sessionUsername !== '' && $owner === $sessionUsername;
    $books[] = $row;
}

mysqli_stmt_close($stmt);

echo json_encode($books);
?>
