<?php
/**
 * submit_guesses.php
 *
 * Recebe AJAX com {guesses: {photo_id: guessed_name, ...}} via POST.
 * Registra cada palpite na tabela guesses e calcula acertos.
 * Retorna JSON: {success: bool, message?: string}
 */

require_once __DIR__ . '/config.php';
ensureSession();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método inválido.']);
  exit;
}

$participantId = $_SESSION['participant_id'] ?? null;
if (!$participantId) {
  echo json_encode(['success' => false, 'message' => 'Sessão expirada. Volte ao início.']);
  exit;
}

$guesses = $_POST['guesses'] ?? [];
if (empty($guesses) || !is_array($guesses)) {
  echo json_encode(['success' => false, 'message' => 'Nenhum palpite enviado.']);
  exit;
}

try {
  $db = getDB();

  // Buscar os nomes corretos de todas as fotos de uma vez
  $stmt = $db->query('SELECT id, photo_name FROM photos');
  $correctMap = [];
  while ($row = $stmt->fetch()) {
    $correctMap[$row['id']] = $row['photo_name'];
  }

  // Preparar INSERT uma vez
  $insert = $db->prepare(
    'INSERT INTO guesses (participant_id, photo_id, guessed_name, is_correct)
     VALUES (?, ?, ?, ?)'
  );

  $now = date('Y-m-d H:i:s');

  foreach ($guesses as $photoId => $guessedName) {
    $photoId   = (int) $photoId;
    $guessName = trim($guessedName);

    if ($photoId <= 0 || $guessName === '') continue;

    $correctName  = $correctMap[$photoId] ?? '';
    $isCorrect    = ($guessName === $correctName) ? 1 : 0;

    $insert->execute([$participantId, $photoId, $guessName, $isCorrect]);
  }

  // Limpa a sessão do participante para não permitir novo palpite
  unset($_SESSION['participant_id']);

  echo json_encode(['success' => true]);

} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Erro no servidor. Tente novamente.']);
  error_log('submit_guesses.php PDO error: ' . $e->getMessage());
}