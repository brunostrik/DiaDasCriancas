<?php
/**
 * admin/delete.php — Deletar uma foto e seus registros associados
 */
require_once __DIR__ . '/../config.php';
ensureSession();

if (empty($_SESSION['admin_logged_in'])) {
  redirect('index.php');
}

$photoId = (int) ($_GET['id'] ?? 0);
if ($photoId <= 0) {
  $_SESSION['flash_msg']  = 'ID inválido.';
  $_SESSION['flash_type'] = 'error';
  redirect('dashboard.php');
}

try {
  $db = getDB();

  // Busca o nome do arquivo
  $stmt = $db->prepare('SELECT photo_filename FROM photos WHERE id = ?');
  $stmt->execute([$photoId]);
  $photo = $stmt->fetch();

  if (!$photo) {
    $_SESSION['flash_msg']  = 'Foto não encontrada.';
    $_SESSION['flash_type'] = 'error';
    redirect('dashboard.php');
  }

  // Deleta do banco (CASCADE remove guesses associados)
  $stmt = $db->prepare('DELETE FROM photos WHERE id = ?');
  $stmt->execute([$photoId]);

  // Remove arquivo físico
  $filePath = UPLOADS_DIR . '/' . $photo['photo_filename'];
  if (file_exists($filePath)) {
    @unlink($filePath);
  }

  $_SESSION['flash_msg']  = 'Foto removida com sucesso.';
  $_SESSION['flash_type'] = 'success';

} catch (PDOException $e) {
  $_SESSION['flash_msg']  = 'Erro ao remover foto.';
  $_SESSION['flash_type'] = 'error';
  error_log('delete.php PDO error: ' . $e->getMessage());
}

redirect('dashboard.php');