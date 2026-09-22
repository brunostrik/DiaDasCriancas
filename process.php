<?php
/**
 * process.php
 *
 * Recebe AJAX com {name, phone} via POST.
 * Valida, verifica duplicidade, cria participante e inicia sessão.
 * Retorna JSON: {success: bool, message?: string, redirect?: string}
 */

require_once __DIR__ . '/config.php';
ensureSession();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Método inválido.']);
  exit;
}

$name  = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');

// --- Validações ---
if ($name === '') {
  echo json_encode(['success' => false, 'message' => 'O nome é obrigatório.']);
  exit;
}

$phoneRegex = '/^\(\d{2}\)\s\d{5}-\d{4}$/';
if (!preg_match($phoneRegex, $phone)) {
  echo json_encode(['success' => false, 'message' => 'Telefone inválido. Use o formato (xx) xxxxx-xxxx']);
  exit;
}

try {
  $db = getDB();

  // --- Verificar duplicidade ---
  $stmt = $db->prepare('SELECT id FROM participants WHERE phone = ?');
  $stmt->execute([$phone]);
  if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Este telefone já participou! Cada celular pode participar apenas uma vez.']);
    exit;
  }

  // --- Criar participante ---
  $stmt = $db->prepare('INSERT INTO participants (name, phone) VALUES (?, ?)');
  $stmt->execute([$name, $phone]);
  $_SESSION['participant_id'] = (int) $db->lastInsertId();

  echo json_encode(['success' => true, 'redirect' => 'guess.php']);

} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Erro no servidor. Tente novamente.']);
  // Log para o admin
  error_log('process.php PDO error: ' . $e->getMessage());
}