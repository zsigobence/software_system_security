<?php
session_start();
if($_POST) {
    // if token not vaild reject request
    if($_POST["csrf"] != $_SESSION["token"]) {
    echo " not valid request";
    return;
    }
    }
    // create new token for every new request
    $_SESSION["token"] = md5(uniqid(mt_rand(),true));
// generate a verification string from IP and user agent
function getUserPCInfo() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $ip = null;

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip . ":" . $user_agent;
}
if(isset($_SESSION["userName"])){
 echo "Serving user: ". $_SESSION["userName"];
}else{
 die("You have no permission to load the page");
 return;
}
/*
if(empty($_SESSION['UPCI'])) {
    $_SESSION['UPCI'] = md5(getUserPCInfo(), PASSWORD_DEFAULT);
   } else {
    // ask user to re-open the browser
    if(!password_verify( getUserPCInfo(),$_SESSION['UPCI'] ) ) {
    die("You are not using a valid Token, close the browser and open it
   again");
    }}*/
?>