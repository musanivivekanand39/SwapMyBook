<?php
session_start();
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit();
}

$title = trim($_POST['title'] ?? '');
$author = trim($_POST['author'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$subjectName = trim($_POST['subject_id'] ?? '');

if ($title === '' || $author === '' || $contact === '' || $subjectName === '') {
    echo "<script>alert('Please fill in all book details.'); window.location.href='index.html';</script>";
    exit();
}

$subjectStmt = mysqli_prepare($conn, "SELECT subject_id FROM subjects WHERE subject_name = ?");
mysqli_stmt_bind_param($subjectStmt, "s", $subjectName);
mysqli_stmt_execute($subjectStmt);
$subjectResult = mysqli_stmt_get_result($subjectStmt);
$subjectRow = mysqli_fetch_assoc($subjectResult);
mysqli_stmt_close($subjectStmt);

if (!$subjectRow) {
    echo "<script>alert('Selected subject was not found in the database.'); window.location.href='index.html';</script>";
    exit();
}

$subjectId = (int) $subjectRow['subject_id'];
$ownerName = $_SESSION['username'] ?? null;
$userId = null;

if ($ownerName) {
    $userStmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE name = ?");
    mysqli_stmt_bind_param($userStmt, "s", $ownerName);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    $userRow = mysqli_fetch_assoc($userResult);
    mysqli_stmt_close($userStmt);

    if ($userRow) {
        $userId = (int) $userRow['user_id'];
    }
}

$imageName = null;
if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Image upload failed. Please try again.'); window.location.href='index.html';</script>";
        exit();
    }

    $originalName = basename($_FILES['image']['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    if (!in_array($extension, $allowedExtensions, true)) {
        echo "<script>alert('Please upload a valid image file.'); window.location.href='index.html';</script>";
        exit();
    }

    $safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
    $imageName = $safeBaseName . '_' . time() . '.' . $extension;
    $targetPath = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $imageName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        echo "<script>alert('Could not save the uploaded image.'); window.location.href='index.html';</script>";
        exit();
    }
}

$insertStmt = mysqli_prepare(
    $conn,
    "INSERT INTO books (title, author, subject_id, contact, image, user_id, owner) VALUES (?, ?, ?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param(
    $insertStmt,
    "ssissis",
    $title,
    $author,
    $subjectId,
    $contact,
    $imageName,
    $userId,
    $ownerName
);

if (!mysqli_stmt_execute($insertStmt)) {
    mysqli_stmt_close($insertStmt);
    echo "<script>alert('Book could not be added. Please check the database connection.'); window.location.href='index.html';</script>";
    exit();
}

mysqli_stmt_close($insertStmt);

header("Location: index.html?book_added=1&subject=" . urlencode($subjectName));
exit();
?>
