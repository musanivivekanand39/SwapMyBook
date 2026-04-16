<?php
session_start();
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_login.html");
    exit();
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    echo "<script>alert('Please enter admin username and password.'); window.location.href='admin_login.html';</script>";
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT admin_id, password FROM admins WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($admin) {
    $storedPassword = $admin['password'];
    $isValidPassword = password_verify($password, $storedPassword);

    if (!$isValidPassword && hash_equals($storedPassword, $password)) {
        $isValidPassword = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upgradeStmt = mysqli_prepare($conn, "UPDATE admins SET password = ? WHERE username = ?");
        mysqli_stmt_bind_param($upgradeStmt, "ss", $newHash, $username);
        mysqli_stmt_execute($upgradeStmt);
        mysqli_stmt_close($upgradeStmt);
    }

    if ($isValidPassword) {
        $_SESSION['admin_username'] = $username;
        header("Location: admin_notes.html");
        exit();
    }
}

echo "<script>alert('Invalid admin credentials'); window.location.href='admin_login.html';</script>";
exit();
?>
