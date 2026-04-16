<?php
session_start();
include "db_connect.php";

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || $email === '' || $password === '') {
    echo "All fields are required.";
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);

if(mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    header("Location: login_page.html");
    exit();
}
else{
    mysqli_stmt_close($stmt);
    echo "Error creating account";
}
?>
