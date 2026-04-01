<?php
$secret = isset($_POST['secret']) ? $_POST['secret'] : (isset($_GET['secret']) ? $_GET['secret'] : '');
$code = '';
$remaining = 0;

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
        <h2 class="mb-4">Saját 2FA Autentikátor</h2>
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
    </div>
</body>
</html>