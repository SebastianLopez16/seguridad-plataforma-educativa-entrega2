<?php
session_start();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

if(!isset($_SESSION['2fa_user_id'])){
    header('Location: index.php');
    exit;
}

$user = getUserById($pdo, $_SESSION['2fa_user_id']);
if(!$user){
    session_destroy();
    header('Location: index.php');
    exit;
}

$errors = [];
$locked = is_user_locked($pdo, $user['id']);
if($locked){
    $errors[] = 'Tu cuenta está temporalmente bloqueada por intentos fallidos. Intenta más tarde.';
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked){
    $code = trim($_POST['code'] ?? '');
    $_SESSION['2fa_attempts'] = ($_SESSION['2fa_attempts'] ?? 0) + 1;
    $valid = validate_2fa_code($pdo, $user['id'], $code);
    if($valid){
        // success: create session and redirect based on role
        $_SESSION['user_id'] = $user['id'];
        clear_2fa_code($pdo, $user['id']);
        record_login_attempt($pdo, $user['id'], 1, '2fa');

        // redirect by role
        if($user['role'] === 'profesor'){
            header('Location: dashboard/profesor.php');
        } elseif($user['role'] === 'invitado'){
            header('Location: dashboard/invitado.php');
        } else {
            header('Location: dashboard/estudiante.php');
        }
        exit;
    } else {
        record_login_attempt($pdo, $user['id'], 0, '2fa');
        $errors[] = 'Código inválido o expirado.';
        // lock if too many attempts
        if(($_SESSION['2fa_attempts'] ?? 0) >= 3){
            lock_user_temporarily($pdo, $user['id'], 300); // 5 minutes
            $errors[] = 'Has superado los intentos permitidos. Cuenta bloqueada 5 minutos.';
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
<title>Verificación - Plataforma Educativa</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="card">
  <h1>Verificación 2FA</h1>
  <p class="muted">Se envió un código al correo (simulado). Código para pruebas: <strong><?=htmlspecialchars(get_last_2fa_code($pdo, $user['id']))?></strong></p>

  <?php if($flash): ?>
    <div class="alert success"><?=htmlspecialchars($flash)?></div>
  <?php endif; ?>

  <?php if($errors): ?>
    <div class="alert error">
      <?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?>
    </div>
  <?php endif; ?>

  <form method="post" novalidate>
    <label>Código de 6 dígitos
      <input name="code" pattern="\d{6}" inputmode="numeric" required />
    </label>
    <button type="submit">Verificar</button>
  </form>

  <div class="help">
    <form method="post" action="includes/functions.php">
      <input type="hidden" name="resend_2fa" value="1">
      <button type="submit" class="link">Reenviar código (simulado)</button>
    </form>
  </div>
</main>
<script src="assets/js/script.js"></script>
</body>
</html>
