<?php
require("dbAuth.inc");

$secret = isset($_POST['secret']) ? $_POST['secret'] : (isset($_GET['secret']) ? $_GET['secret'] : '');
$recovered_msg = '';
$code = '';
$remaining = 0;

if (isset($_POST['recover_user']) && isset($_POST['recover_pass'])) {
    $connect = mysqli_connect($hostDB, $userDB, $passwordDB, $databaseDB);
    if (!mysqli_connect_errno()) {
        $u = mysqli_real_escape_string($connect, $_POST['recover_user']);
        $p = mysqli_real_escape_string($connect, $_POST['recover_pass']);
        $query = "SELECT twofa_secret FROM users WHERE userName='$u' AND password='$p'";
        $result = mysqli_query($connect, $query);
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $secret = $row['twofa_secret'];
            $recovered_msg = "Sikeres helyreállítás!";
        } else {
            $recovered_msg = "Hiba: Érvénytelen adatok!";
        }
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
<html lang="en">
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
        <h2 class="mb-4">2FA Autentikátor és Helyreállítás</h2>
        
        <?php if ($recovered_msg): ?>
            <div class="alert <?php echo (strpos($recovered_msg, 'Hiba') === false) ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $recovered_msg; ?>
            </div>
        <?php endif; ?>

        <form method="post" action="authenticator.php" class="mb-4">
            <div class="form-group mb-3">
                <label>2FA Titkos Kulcs (Secret)</label>
                <input type="text" name="secret" class="form-control" value="<?php echo htmlspecialchars($secret); ?>" required />
            </div>
            <button type="submit" class="btn btn-primary w-100">Kód Generálása</button>
        </form>

        <?php if (!empty($code)): ?>
        <div class="alert alert-success mt-4 text-center">
            <p class="mb-1">Aktuális kód:</p>
            <h1 class="display-4 fw-bold"><?php echo $code; ?></h1>
            <hr>
            <p class="mb-0 text-muted">Új kód: <strong id="timer"><?php echo $remaining; ?></strong> mp múlva.</p>
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

        <div class="card mt-5">
            <div class="card-header">Elveszett kulcs helyreállítása</div>
            <div class="card-body">
                <form method="post" action="authenticator.php">
                    <div class="mb-3">
                        <label class="form-label">Felhasználónév</label>
                        <input type="text" name="recover_user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jelszó</label>
                        <input type="password" name="recover_pass" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm w-100">Kulcs betöltése az adatbázisból</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
