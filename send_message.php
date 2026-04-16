<?php
session_start();
include "db_connect.php";

if(isset($_POST['message'])){

$book_id = $_POST['book_id'];
$sender = $_POST['sender'];
$message = $_POST['message'];

$sql = "INSERT INTO chat_messages (book_id, sender, message)
VALUES ('$book_id','$sender','$message')";

if(mysqli_query($conn,$sql)){
echo "Message saved";
}else{
echo "Error";
}

}
?>