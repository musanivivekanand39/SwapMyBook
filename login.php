<?php
session_start();
include "db_connect.php";

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    echo "<script>alert('Please enter username and password.'); window.location.href='login_page.html';</script>";
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT name, password FROM users WHERE name = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($user) {
    $storedPassword = $user['password'];
    $isValidPassword = password_verify($password, $storedPassword);

    if (!$isValidPassword && hash_equals($storedPassword, $password)) {
        $isValidPassword = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $upgradeStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE name = ?");
        mysqli_stmt_bind_param($upgradeStmt, "ss", $newHash, $username);
        mysqli_stmt_execute($upgradeStmt);
        mysqli_stmt_close($upgradeStmt);
    }

    if ($isValidPassword) {
        $_SESSION['username'] = $user['name'];
        header("Location: index.html");
        exit();
    }
}

echo "<script>alert('Invalid Login'); window.location.href='login_page.html';</script>";
?>
