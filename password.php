<?php 
$password = "1234";
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo $hashedPassword, PHP_EOL;
?>