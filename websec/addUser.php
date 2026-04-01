<?php
require("dbAuth.inc");
require("session.php");

$userName = isset($_POST['userName']) ? $_POST['userName'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$secret_display = '';

if(!empty($userName) and !empty($password)) {
    $connect = mysqli_connect($hostDB, $userDB, $passwordDB, $databaseDB);
    if(mysqli_connect_errno()){
        die(" cannot connect to database ". mysqli_connect_error());
    }
    
    $twofa_secret = bin2hex(random_bytes(8));
    
    $query ="Insert into users(userName, password, twofa_secret) VALUES ('" . $userName ."','" . $password ."', '" . $twofa_secret . "')";
    $result = mysqli_query($connect,$query);
    if (!$result) {
        die(' error while running query');
    }
    
    $secret_display = $twofa_secret;
    mysqli_close($connect);
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <?php if ($secret_display): ?>
    <div class="alert alert-info">
        Sikeres regisztráció! A 2FA titkos kulcsod: <strong><?php echo $secret_display; ?></strong><br>
        Mentsd el ezt a kulcsot az autentikátorba!
    </div>
    <?php endif; ?>

    <form id='adduser' action="addUser.php" method='post' accept-charset='UTF-8'>
        <div class="form-group mb-3">
            <label for="username">User</label>
            <input type="text" name='userName' class="form-control" id="username" placeholder="Enter username" maxlength="50" />
        </div>
        <div class="form-group mb-3">
            <label for="password">Password</label>
            <input type="password" name='password' class="form-control" id="password" placeholder="Password" maxlength="50"/>
        </div>
        <input type="submit" class="btn btn-primary" id="submit" name='Submit' value='Add' disabled/>
    </form>
</div>

<script>
    var passwordField = document.getElementById("password");
    var submitBtn = document.getElementById("submit");
    submitBtn.disabled = true;
    
    passwordField.onkeyup = function() {
        submitBtn.disabled = true;
        var lowerCaseLetters = /[a-z]/g;
        if (!passwordField.value.match(lowerCaseLetters)) {
            return;
        }
        if(passwordField.value.length > 5) {
            submitBtn.disabled = false;
        }
    }
</script>
</body>
</html>