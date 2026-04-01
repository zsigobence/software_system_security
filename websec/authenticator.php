<?php
require("dbAuth.inc");

$secret = isset($_POST['secret']) ? $_POST['secret'] : (isset($_GET['secret']) ? $_GET['secret'] : '');
$code = '';
$remaining = 0;
$message = '';
$msg_type = '';

if (isset($_POST['recover_userName']) && isset($_POST['recover_password']) && isset($_POST['recover_email'])) {
    $connect = mysqli_connect($hostDB, $userDB, $passwordDB, $databaseDB);
    
    if (!mysqli_connect_errno()) {
        $u = mysqli_real_escape_string($connect, $_POST['recover_userName']);
        $p = mysqli_real_escape_string($connect, $_POST['recover_password']);
        $email = filter_var($_POST['recover_email'], FILTER_SANITIZE_EMAIL);

        $query = "SELECT twofa_secret FROM users WHERE userName='$u' AND password='$p'";
        $result = mysqli_query($connect, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $secret = $row['twofa_secret']; 
            
            $to = $email;
            $subject = "2FA Titkos Kulcs Helyreallitas";
            $body = "Kedves $u!\n\nAz OTP (2FA) autentikatorodhoz tartozo titkos kulcs (secret) a kovetkezo:\n\n" . $secret . "\n\nKerjuk, ird be ezt a kodot az autentikator alkalmazasodba, es tartsd biztonsagos helyen!\n\nUdvözlettel,\nA Rendszergazda";
            
            $email_content = "Dátum: " . date("Y-m-d H:i:s") . "\n";
            $email_content .= "Címzett: " . $to . "\n";
            $email_content .= "Tárgy: " . $subject . "\n";
            $email_content .= "----------------------------------------\n";
            $email_content .= $body . "\n";
            $email_content .= "========================================\n\n";

            $filename = "email_log_" . date("Ymd_His") . ".txt";

            if (file_put_contents($filename, $email_content)) {
                $message = "Sikeres helyreállítás! A szimulált e-mailt kimentettük a(z) <strong>$filename</strong> fájlba. A kulcsot automatikusan betöltöttük az autentikátorba.";
                $msg_type = "success";
            } else {
                $message = "Sikeres helyreállítás, de hiba történt a fájl mentésekor.";
                $msg_type = "warning";
            }
        } else {
            $message = "Hibás felhasználónév vagy jelszó!";
            $msg_type = "danger";
        }
        if ($result) mysqli_free_result($result);
        mysqli_close($connect);
    }
}

if (!empty($secret)) {
    $current_time = time();
    $current_slice = floor($current_time / 30);
    $code = str_pad(abs(crc32($secret . $current_slice)) % 1000000, 6, '0', STR_PAD_LEFT);
    $remaining = 30 - ($current_time % 30);
}
?>
<!doctype html>
<html lang="hu">
<head>
    <meta charset="utf-8">
    <title>2FA Authenticator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (!empty($secret)): ?>
    <meta http-equiv="refresh" content="<?php echo $remaining; ?>;url=authenticator.php?secret=<?php echo urlencode($secret); ?>">
    <?php endif; ?>
</head>
<body>
    <div class="container mt-5" style="max-width: 500px;">
        <h2 class="mb-4">Saját 2FA Autentikátor</h2>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="authenticator.php">
            <div class="form-group mb-3">
                <label>2FA Titkos Kulcs (Secret)</label>
                <input type="text" name="secret" class="form-control" value="<?php echo htmlspecialchars($secret); ?>" required />
            </div>
            <button type="submit" class="btn btn-primary w-100">Kód Generálása</button>
        </form>

        <?php if (!empty($code)): ?>
        <div class="alert alert-success mt-4 text-center">
            <p class="mb-1">Aktuális kód:</p>
            <h1 class="display-4 fw-bold tracking-widest"><?php echo $code; ?></h1>
            <hr>
            <p class="mb-0 text-muted">Új kód generálódik: <strong id="timer"><?php echo $remaining; ?></strong> másodperc múlva.</p>
        </div>
        <script>
            let timeLeft = <?php echo $remaining; ?>;
            setInterval(() => {
                timeLeft--;
                if(timeLeft >= 0) {
                    document.getElementById('timer').innerText = timeLeft;
                }
            }, 1000);
        </script>
        <?php endif; ?>

        <div class="card mt-5 border-secondary">
            <div class="card-header bg-secondary text-white">
                Elfelejtetted a kulcsot? (Szimulált E-mail helyreállítás)
            </div>
            <div class="card-body">
                <form method="post" action="authenticator.php">
                    <div class="mb-3">
                        <label class="form-label">Felhasználónév</label>
                        <input type="text" name="recover_userName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jelszó</label>
                        <input type="password" name="recover_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail cím (ide menne a levél)</label>
                        <input type="email" name="recover_email" class="form-control" placeholder="pelda@email.hu" required>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 text-dark">Kulcs kimentése fájlba & Betöltés</button>
                </form>
            </div>
        </div>

    </div>
</body>
</html>
