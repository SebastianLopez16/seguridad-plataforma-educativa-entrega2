<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if(!$username || !$password){
        $errors[] = 'Por favor ingresa usuario y contraseña.';
    } else {
        $user = getUserByUsername($pdo, $username);
        if(!$user || !password_verify($password, $user['password_hash'])){
            $errors[] = 'Usuario o contraseña incorrectos.';
            record_login_attempt($pdo, $user ? $user['id'] : null, 0, 'login');
            // small delay to mitigate brute force
            usleep(200000);
        } else {
            // generate 6-digit code, save to session and optionally to DB
            $code = random_int(100000, 999999);
            $_SESSION['2fa_user_id'] = $user['id'];
            $_SESSION['2fa_code'] = $code;
            $_SESSION['2fa_expires'] = time() + 300; // 5 minutes
            $_SESSION['2fa_attempts'] = 0;
            // In a real system you'd send email/SMS. Here we simulate by storing in DB and showing a message.
            save_2fa_code($pdo, $user['id'], $code, $_SESSION['2fa_expires']);
            record_login_attempt($pdo, $user['id'], 1, 'login');
            $_SESSION['flash'] = 'Código 2FA generado y enviado (simulado). Revisa la pantalla de verificación.';
            header('Location: verify.php');
            exit;
        }
    }
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Login - Plataforma Educativa</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="card">
  <h1>Plataforma Educativa</h1>
  <p class="muted">Acceso seguro — segundo avance</p>

  <?php if($flash): ?>
    <div class="alert success"><?=htmlspecialchars($flash)?></div>
  <?php endif; ?>

  <?php if($errors): ?>
    <div class="alert error">
      <?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <label>Usuario
      <input name="username" required />
    </label>
    <label>Contraseña
      <input name="password" type="password" required />
    </label>
    <button type="submit">Iniciar sesión</button>
  </form>

  <div class="help">
    <p>Usuarios de prueba (ver README): <strong>estudiante1 / pass123</strong></p>
  </div>
</main>
<script src="assets/js/script.js"></script>
</body>
</html>
