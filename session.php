<?php 
    include('config.php');
    session_start();

    $user_name=$_SESSION['login_user'];
    $user_password = $_SESSION['login_password'];

    $query = "SELECT owner_id from owners where user_name = ? and user_password = ?";
    $statement = $conn->prepare($query);
    $statement->bindParam(1, $user_name);
    $statement->bindParam(2, $user_password);
    $statement->execute();
    $Items_id = $statement->fetchColumn();

    $login_session = $Items_id;
    if(!isset($_SESSION['login_user'])){
        header("location:Login.php");
        die();
    }

?>