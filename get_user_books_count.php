<?php
session_start();
include "db_connect.php";

$username = trim($_GET['username'] ?? '');

if ($username === '') {
    echo "0";
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS count FROM books WHERE owner = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

echo $row ? $row['count'] : "0";
?>
