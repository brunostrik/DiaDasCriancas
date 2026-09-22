<?php
/**
 * admin/index.php — Login do administrador
 */
require_once __DIR__ . '/../config.php';
ensureSession();

// Se já está logado, redireciona para dashboard
if (!empty($_SESSION['admin_logged_in'])) {
  redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if ($username === '' || $password === '') {
    $error = 'Preencha usuário e senha.';
  } else {
    try {
      $db = getDB();
      $stmt = $db->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
      $stmt->execute([$username]);
      $admin = $stmt->fetch();

      if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id']        = $admin['id'];
        redirect('dashboard.php');
      } else {
        $error = 'Usuário ou senha inválidos.';
      }
    } catch (PDOException $e) {
      $error = 'Erro no servidor.';
      error_log('admin login PDO error: ' . $e->getMessage());
    }
  }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — Dia das Crianças</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
  <div class="login-wrap">
    <div class="login-card">
      <h1>🔐 Administrador</h1>
      <p>Faça login para gerenciar o sistema</p>

      <?php if ($error): ?>
        <div class="flash flash-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="post">
        <div class="form-group">
          <label for="username">Usuário</label>
          <input type="text" id="username" name="username" required autocomplete="username" maxlength="100">
        </div>
        <div class="form-group">
          <label for="password">Senha</label>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary">Entrar</button>
      </form>
    </div>
  </div>
</body>
</html>