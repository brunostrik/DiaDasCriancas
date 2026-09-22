<?php
/**
 * guess.php
 *
 * Página de palpites: mostra todas as fotos com um combobox abaixo de cada.
 * Exige que o participante tenha se registrado via index.php (sessão).
 */

require_once __DIR__ . '/config.php';
ensureSession();

// Se não há participante na sessão, redireciona para o início
if (empty($_SESSION['participant_id'])) {
  redirect('index.php');
}

try {
  $db = getDB();

  // Buscar todas as fotos
  $photos = $db->query('SELECT id, photo_name, photo_filename FROM photos ORDER BY id')->fetchAll();

  // Buscar todos os nomes distintos para o combobox
  $names = $db->query('SELECT DISTINCT photo_name FROM photos ORDER BY photo_name')->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
  die('Erro ao carregar fotos: ' . $e->getMessage());
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Palpites — Dia das Crianças</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="header header--small">
    <h1>📸 Quem é quem?</h1>
    <p>Escolha um nome para cada foto e clique em enviar!</p>
  </div>

  <div class="container">
    <?php if (empty($photos)): ?>
      <div class="card" style="text-align:center;color:#888;">
        <p>Ainda não há fotos cadastradas. Volte mais tarde!</p>
      </div>
    <?php else: ?>
      <form id="guessForm" novalidate>
        <div class="photo-grid">
          <?php foreach ($photos as $photo):
            $imgSrc = 'uploads/' . htmlspecialchars($photo['photo_filename']);
          ?>
            <div class="photo-card">
              <img src="<?= $imgSrc ?>" alt="Foto" loading="lazy">
              <div class="photo-label">Foto #<?= $photo['id'] ?></div>
              <select data-photo-id="<?= $photo['id'] ?>" required>
                <option value="">Selecione quem é...</option>
                <?php foreach ($names as $name): ?>
                  <option value="<?= htmlspecialchars($name) ?>">
                    <?= htmlspecialchars($name) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:16px;">Enviar Palpites</button>
      </form>
    <?php endif; ?>
  </div>

  <script src="js/main.js"></script>
</body>
</html>