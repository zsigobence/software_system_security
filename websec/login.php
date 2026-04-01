<?php
require("dbAuth.inc");
session_start();

if (isset($_SESSION['lockout_time'])) {
    $time_passed = time() - $_SESSION['lockout_time'];
    if ($time_passed < 300) {
        $remaining_minutes = ceil((300 - $time_passed) / 60);
        die("</br><div class=\"alert alert-danger\">Túl sok sikertelen bejelentkezés. Próbálja újra $remaining_minutes perc múlva.</div>");
    } else {
        unset($_SESSION['lockout_time']);
        $_SESSION['failed_attempts'] = 0;
    }
}

$userName = isset($_POST['userName']) ? $_POST['userName'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$twofa_code = isset($_POST['twofa_code']) ? $_POST['twofa_code'] : '';

echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">';
echo '<div class="container mt-5">';

if (!empty($twofa_code) && isset($_SESSION['pending_2fa_user'])) {
    $secret = $_SESSION['pending_2fa_secret'];
    $current_slice = floor(time() / 30);
    $expected_code_1 = str_pad(abs(crc32($secret . $current_slice)) % 1000000, 6, '0', STR_PAD_LEFT);
    $expected_code_2 = str_pad(abs(crc32($secret . ($current_slice - 1))) % 1000000, 6, '0', STR_PAD_LEFT);

    if ($twofa_code === $expected_code_1 || $twofa_code === $expected_code_2) {
        $_SESSION['userName'] = $_SESSION['pending_2fa_user'];
        setcookie('userName', $_SESSION['pending_2fa_user'], false, "/", false);
        $userToken = $_SESSION['pending_2fa_token'];
        
        unset($_SESSION['pending_2fa_user']);
        unset($_SESSION['pending_2fa_secret']);
        unset($_SESSION['pending_2fa_token']);
        $_SESSION['failed_attempts'] = 0;

        echo "<div class=\"alert alert-success\">Bejelentkezés sikeres!</div>";
        echo "<a class=\"btn btn-success\" href='transactions.php?userToken=" . $userToken ."'> Tranzakciók megtekintése</a>";
        echo '</div>';
        exit;
    } else {
        echo "<div class=\"alert alert-danger\">Hibás 2FA kód!</div>";
        $_SESSION['failed_attempts'] = isset($_SESSION['failed_attempts']) ? $_SESSION['failed_attempts'] + 1 : 1;
        if ($_SESSION['failed_attempts'] >= 3) {
            $_SESSION['lockout_time'] = time();
        }
    }
}

if(!empty($userName) and !empty($password)){
    $connect = mysqli_connect($hostDB, $userDB, $passwordDB, $databaseDB);
    if(mysqli_connect_errno()){
        die(" cannot connect to database ". mysqli_connect_error());
    }

    $query ="select * from users where userName='" . $userName ."' and password='" . $password ."'" ;
    $result= mysqli_query($connect,$query);
    
    if (!$result){
        die(' error while running query');
    }

    $loginInUser = null;
    $userToken = null;
    $twofa_secret = null;
    
    while ($row = mysqli_fetch_assoc($result)) {
        $loginInUser = $row["userName"];
        $userToken = $row["userToken"];
        $twofa_secret = $row["twofa_secret"];
        break;
    }
    
    mysqli_free_result($result);
    mysqli_close($connect);
    
    if (!empty($loginInUser)) {
        $_SESSION['pending_2fa_user'] = $loginInUser;
        $_SESSION['pending_2fa_secret'] = $twofa_secret;
        $_SESSION['pending_2fa_token'] = $userToken;

        echo '<form method="post" action="login.php">';
        echo '<div class="form-group mb-3">';
        echo '<label>Add meg a 6 jegyű 2FA kódot</label>';
        echo '<input type="text" name="twofa_code" class="form-control" maxlength="6" required />';
        echo '</div>';
        echo '<input type="submit" class="btn btn-primary" value="Hitelesítés" />';
        echo '</form>';
        echo '</div>';
        exit;
    } else {
        $_SESSION['failed_attempts'] = isset($_SESSION['failed_attempts']) ? $_SESSION['failed_attempts'] + 1 : 1;
        if ($_SESSION['failed_attempts'] >= 3   ) {
            $_SESSION['lockout_time'] = time();
            echo "<div class=\"alert alert-danger\">3 hibás próbálkozás. A bejelentkezés 5 percre zárolva lett.</div>";
        } else {
            $remaining_attempts = 3 - $_SESSION['failed_attempts'];
            echo "<div class=\"alert alert-danger\">Adatbázis bejelentkezés sikertelen. Még $remaining_attempts próbálkozása maradt.</div>";
        }
    }
}
echo '</div>';
?>