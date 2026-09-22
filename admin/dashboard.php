<?php
/**
 * admin/dashboard.php — Painel administrativo
 */
require_once __DIR__ . '/../config.php';
ensureSession();

if (empty($_SESSION['admin_logged_in'])) {
  redirect('index.php');
}

// Flash messages
$flashMsg  = $_SESSION['flash_msg'] ?? null;
$flashType = $_SESSION['flash_type'] ?? null;
unset($_SESSION['flash_msg'], $_SESSION['flash_type']);

try {
  $db = getDB();

  // --- Fotos ---
  $photos = $db->query('SELECT id, photo_name, photo_filename, created_at FROM photos ORDER BY created_at DESC')->fetchAll();

  // --- Palpites recentes ---
  $guesses = $db->query('
    SELECT g.id, g.guessed_name, g.is_correct, g.created_at,
           p.name AS participant_name, p.phone,
           ph.photo_name AS correct_name, ph.photo_filename
    FROM guesses g
    JOIN participants p ON p.id = g.participant_id
    JOIN photos ph     ON ph.id = g.photo_id
    ORDER BY g.created_at DESC
    LIMIT 200
  ')->fetchAll();

  // --- Ranking ---
  $ranking = $db->query('
    SELECT
      p.id,
      p.name,
      p.phone,
      COUNT(*) AS total_correct,
      MIN(g.created_at) AS first_guess_time
    FROM guesses g
    JOIN participants p ON p.id = g.participant_id
    WHERE g.is_correct = 1
    GROUP BY p.id
    ORDER BY total_correct DESC, first_guess_time ASC
  ')->fetchAll();

} catch (PDOException $e) {
  $dbError = 'Erro ao carregar dados: ' . $e->getMessage();
  error_log('dashboard.php PDO error: ' . $e->getMessage());
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Admin — Dia das Crianças</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>

  <div class="admin-header">
    <h1>📋 Painel Admin</h1>
    <a href="../index.php">← Site público</a>
    <a href="logout.php">Sair</a>
  </div>

  <div class="container">

    <?php if (!empty($dbError)): ?>
      <div class="flash flash-error"><?= htmlspecialchars($dbError) ?></div>
    <?php endif; ?>

    <?php if ($flashMsg): ?>
      <div class="flash flash-<?= $flashType ?>"><?= htmlspecialchars($flashMsg) ?></div>
    <?php endif; ?>

    <!-- ============================================================
         Gerenciar Fotos
         ============================================================ -->
    <div class="admin-section">
      <h2>📸 Gerenciar Fotos</h2>

      <div class="card">
        <form action="upload.php" method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label for="photo_name">Nome da Pessoa na Foto</label>
            <input type="text" id="photo_name" name="photo_name" required maxlength="255">
          </div>
          <div class="form-group">
            <label for="photo">Foto (JPG, PNG, WebP — max 2MB)</label>
            <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" required>
          </div>
          <button type="submit" class="btn btn-primary">Adicionar Foto</button>
        </form>
      </div>

      <?php if (!empty($photos)): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Foto</th>
                <th>Nome</th>
                <th>Arquivo</th>
                <th>Data</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($photos as $photo): ?>
                <tr>
                  <td>
                    <img src="../uploads/<?= htmlspecialchars($photo['photo_filename']) ?>"
                         alt="<?= htmlspecialchars($photo['photo_name']) ?>"
                         loading="lazy">
                  </td>
                  <td><strong><?= htmlspecialchars($photo['photo_name']) ?></strong></td>
                  <td style="font-size:0.8rem;color:#888;"><?= htmlspecialchars($photo['photo_filename']) ?></td>
                  <td style="white-space:nowrap;font-size:0.8rem;color:#888;">
                    <?= date('d/m/Y H:i', strtotime($photo['created_at'])) ?>
                  </td>
                  <td class="actions">
                    <a href="delete.php?id=<?= $photo['id'] ?>"
                       onclick="return confirm('Remover esta foto e todos os palpites associados?')">
                      Excluir
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p style="text-align:center;color:#888;padding:16px;">Nenhuma foto cadastrada ainda.</p>
      <?php endif; ?>
    </div>

    <!-- ============================================================
         Ranking
         ============================================================ -->
    <div class="admin-section">
      <h2>🏆 Ranking</h2>

      <?php if (!empty($ranking)): ?>
        <div class="card" style="padding:8px 0;">
          <ul class="ranking-list">
            <?php $pos = 1; ?>
            <?php foreach ($ranking as $r): ?>
              <li class="ranking-item">
                <div class="ranking-position"><?= $pos++ ?></div>
                <div class="ranking-info">
                  <div class="rnome"><?= htmlspecialchars($r['name']) ?></div>
                  <div class="racertos">
                    📱 <?= htmlspecialchars($r['phone']) ?> ·
                    ⏱ <?= date('d/m/Y H:i', strtotime($r['first_guess_time'])) ?>
                  </div>
                </div>
                <div class="ranking-score"><?= (int) $r['total_correct'] ?> acertos</div>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php else: ?>
        <p style="text-align:center;color:#888;padding:16px;">Nenhum palpite com acertos ainda.</p>
      <?php endif; ?>
    </div>

    <!-- ============================================================
         Palpites Recentes
         ============================================================ -->
    <div class="admin-section">
      <h2>📝 Palpites Recentes</h2>

      <?php if (!empty($guesses)): ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Participante</th>
                <th>Celular</th>
                <th>Foto</th>
                <th>Palpite</th>
                <th>Correto?</th>
                <th>Data/Hora</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($guesses as $g): ?>
                <tr>
                  <td><?= htmlspecialchars($g['participant_name']) ?></td>
                  <td style="white-space:nowrap;"><?= htmlspecialchars($g['phone']) ?></td>
                  <td>
                    <img src="../uploads/<?= htmlspecialchars($g['photo_filename']) ?>"
                         alt="" loading="lazy">
                  </td>
                  <td>
                    <?= htmlspecialchars($g['guessed_name']) ?>
                    <br>
                    <span style="font-size:0.75rem;color:#888;">
                      (certo: <?= htmlspecialchars($g['correct_name']) ?>)
                    </span>
                  </td>
                  <td>
                    <?php if ($g['is_correct']): ?>
                      <span style="color:#2ecc71;font-weight:700;">✓</span>
                    <?php else: ?>
                      <span style="color:#e74c3c;">✗</span>
                    <?php endif; ?>
                  </td>
                  <td style="white-space:nowrap;font-size:0.8rem;">
                    <?= date('d/m/Y H:i', strtotime($g['created_at'])) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p style="text-align:center;font-size:0.8rem;color:#888;margin-top:8px;">
          Últimos 200 palpites
        </p>
      <?php else: ?>
        <p style="text-align:center;color:#888;padding:16px;">Nenhum palpite registrado ainda.</p>
      <?php endif; ?>
    </div>

  </div>

</body>
</html>