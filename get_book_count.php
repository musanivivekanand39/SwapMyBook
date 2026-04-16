<?php
include "db_connect.php";

$subject = strtolower(trim($_GET['subject'] ?? ''));

if ($subject === '') {
    echo "0";
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM books
     INNER JOIN subjects ON books.subject_id = subjects.subject_id
     WHERE LOWER(subjects.subject_name) = ?"
);
mysqli_stmt_bind_param($stmt, "s", $subject);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

echo $row ? $row['total'] : "0";
?>
