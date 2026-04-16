<?php
session_start();
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit();
}

$title = trim($_POST['title'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$uploaderName = trim($_POST['uploader_name'] ?? '');

if ($uploaderName === '' && isset($_SESSION['username'])) {
    $uploaderName = trim($_SESSION['username']);
}

if ($title === '' || $subject === '' || $uploaderName === '') {
    header("Location: index.html?note_error=" . urlencode("Please fill in all note details."));
    exit();
}

$subjectStmt = mysqli_prepare($conn, "SELECT subject_name FROM subjects WHERE subject_name = ?");
mysqli_stmt_bind_param($subjectStmt, "s", $subject);
mysqli_stmt_execute($subjectStmt);
$subjectResult = mysqli_stmt_get_result($subjectStmt);
$subjectRow = mysqli_fetch_assoc($subjectResult);
mysqli_stmt_close($subjectStmt);

if (!$subjectRow) {
    header("Location: index.html?note_error=" . urlencode("Selected subject was not found."));
    exit();
}

if (!isset($_FILES['pdf_file']) || ($_FILES['pdf_file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    header("Location: index.html?note_error=" . urlencode("Please upload a PDF file."));
    exit();
}

if ($_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    header("Location: index.html?note_error=" . urlencode("PDF upload failed. Please try again."));
    exit();
}

$originalName = basename($_FILES['pdf_file']['name']);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$mimeType = '';

if (function_exists('mime_content_type')) {
    $mimeType = (string) mime_content_type($_FILES['pdf_file']['tmp_name']);
}

$allowedMimeTypes = [
    '',
    'application/pdf',
    'application/x-pdf',
    'application/acrobat',
    'applications/vnd.pdf',
    'text/pdf',
    'text/x-pdf'
];

if ($extension !== 'pdf' || !in_array($mimeType, $allowedMimeTypes, true)) {
    header("Location: index.html?note_error=" . urlencode("Only PDF files are allowed."));
    exit();
}

$safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
$fileName = $safeBaseName . '_' . time() . '.pdf';
$uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'notes';

if (!is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}

$targetPath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;
$databasePath = 'notes/' . $fileName;

if (!move_uploaded_file($_FILES['pdf_file']['tmp_name'], $targetPath)) {
    header("Location: index.html?note_error=" . urlencode("Could not save the uploaded PDF."));
    exit();
}

$status = 'pending';
$insertStmt = mysqli_prepare(
    $conn,
    "INSERT INTO notes (title, subject, file_path, uploader_name, status) VALUES (?, ?, ?, ?, ?)"
);
mysqli_stmt_bind_param($insertStmt, "sssss", $title, $subject, $databasePath, $uploaderName, $status);

if (!mysqli_stmt_execute($insertStmt)) {
    mysqli_stmt_close($insertStmt);
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    header("Location: index.html?note_error=" . urlencode("Notes could not be added."));
    exit();
}

mysqli_stmt_close($insertStmt);

header("Location: index.html?note_added=1");
exit();
?>
