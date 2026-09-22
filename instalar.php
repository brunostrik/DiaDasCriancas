<?php
/**
 * Script de instalação — execute uma vez para criar o banco e o admin.
 *
 * ATENÇÃO: Delete ou proteja este arquivo após a instalação!
 *
 * Acesse via navegador: https://astorgaifpr.online/diadascriancas/instalar.php
 */

require_once __DIR__ . '/config.php';

$mensagens = [];
$erro = false;

// --- Criar banco + tabelas ---
try {
  $sql = file_get_contents(__DIR__ . '/db.sql');

  // Conecta sem DB para criar o banco
  $pdo = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);

  // Executa SQL que contém "CREATE DATABASE" e "USE"
  $pdo->exec($sql);
  $mensagens[] = '✅ Banco de dados e tabelas criados com sucesso.';
} catch (PDOException $e) {
  $mensagens[] = '❌ Erro ao criar banco: ' . $e->getMessage();
  $erro = true;
}

// --- Admin padrão (já vem no db.sql, mas garantimos) ---
if (!$erro) {
  try {
    $db = getDB();
    $stmt = $db->query('SELECT COUNT(*) AS total FROM admins');
    $count = $stmt->fetch();

    if ((int)$count['total'] === 0) {
      $hash = password_hash('admin123', PASSWORD_BCRYPT);
      $stmt = $db->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
      $stmt->execute(['admin', $hash]);
      $mensagens[] = '✅ Admin padrão criado: <strong>admin</strong> / <strong>admin123</strong>';
    } else {
      $mensagens[] = 'ℹ️ Admin já existe, pulando criação.';
    }
  } catch (PDOException $e) {
    $mensagens[] = '❌ Erro ao verificar/criar admin: ' . $e->getMessage();
    $erro = true;
  }
}

// --- Verificar diretório uploads ---
$uploadsDir = __DIR__ . '/uploads';
if (!is_dir($uploadsDir)) {
  mkdir($uploadsDir, 0755, true);
  $mensagens[] = '✅ Diretório uploads criado.';
} else {
  $mensagens[] = 'ℹ️ Diretório uploads já existe.';
}

// --- Criar .htaccess no uploads para segurança ---
$htaccess = $uploadsDir . '/.htaccess';
if (!file_exists($htaccess)) {
  file_put_contents($htaccess, "Options -Indexes\nDeny from all\n");
  $mensagens[] = '✅ .htaccess de proteção criado em uploads/.';
} else {
  $mensagens[] = 'ℹ️ .htaccess já existe em uploads/.';
}

// --- Verificar extensão GD (para manipulação de imagens) ---
if (extension_loaded('gd')) {
  $mensagens[] = '✅ Extensão GD disponível.';
} else {
  $mensagens[] = '⚠️ Extensão GD não encontrada (não é crítica, mas útil).';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Instalação — Dia das Crianças</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container" style="max-width:600px;padding-top:40px;">
    <div class="card">
      <h1 style="text-align:center;margin-bottom:20px;">🛠 Instalação</h1>
      <?php foreach ($mensagens as $m): ?>
        <div style="padding:8px 0;font-size:0.95rem;"><?= $m ?></div>
      <?php endforeach; ?>

      <?php if ($erro): ?>
        <div class="flash flash-error" style="margin-top:16px;">
          Erro durante a instalação. Verifique as configurações em <code>config.php</code>
        </div>
      <?php else: ?>
        <div class="flash flash-success" style="margin-top:16px;">
          ✅ Instalação concluída!<br>
          <a href="admin/index.php" style="display:inline-block;margin-top:8px;">→ Acessar Painel Admin</a><br>
          <a href="index.php" style="display:inline-block;margin-top:4px;">→ Ir para o site público</a>
        </div>
        <p style="text-align:center;margin-top:16px;font-size:0.8rem;color:#e74c3c;">
          ⚠️ <strong>Delete o arquivo <code>instalar.php</code> após a instalação!</strong>
        </p>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>