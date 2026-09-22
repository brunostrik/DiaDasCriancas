<?php
/**
 * admin/upload.php — Upload de foto + nome da pessoa
 * Redireciona de volta ao dashboard com mensagem flash na sessão.
 */
require_once __DIR__ . '/../config.php';
ensureSession();

if (empty($_SESSION['admin_logged_in'])) {
  redirect('index.php');
}

$msg  = '';
$type = 'error'; // success | error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $photoName = trim($_POST['photo_name'] ?? '');

  if ($photoName === '') {
    $msg = 'O nome da pessoa é obrigatório.';
  } elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    $msg = 'Erro no upload da foto.';
  } else {
    $file = $_FILES['photo'];

    // Valida tamanho
    if ($file['size'] > MAX_UPLOAD_SIZE) {
      $msg = 'A imagem deve ter no máximo 2MB.';
    } else {
      // Valida extensão
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        $msg = 'Formato não permitido. Use JPG, PNG ou WebP.';
      } else {
        // Valida se é imagem real
        $info = @getimagesize($file['tmp_name']);
        if ($info === false) {
          $msg = 'O arquivo enviado não é uma imagem válida.';
        } else {
          // Gera nome único
          $filename = uniqid('photo_', true) . '.' . $ext;
          $dest = UPLOADS_DIR . '/' . $filename;

          if (!is_dir(UPLOADS_DIR)) {
            mkdir(UPLOADS_DIR, 0755, true);
          }

          if (move_uploaded_file($file['tmp_name'], $dest)) {
            try {
              $db = getDB();
              $stmt = $db->prepare('INSERT INTO photos (photo_name, photo_filename) VALUES (?, ?)');
              $stmt->execute([$photoName, $filename]);
              $msg  = 'Foto cadastrada com sucesso!';
              $type = 'success';
            } catch (PDOException $e) {
              $msg = 'Erro no banco de dados.';
              error_log('upload.php PDO error: ' . $e->getMessage());
              // Remove arquivo se DB falhou
              @unlink($dest);
            }
          } else {
            $msg = 'Erro ao salvar a imagem.';
          }
        }
      }
    }
  }
}

// Guarda mensagem na sessão e redireciona
$_SESSION['flash_msg']  = $msg;
$_SESSION['flash_type'] = $type;
redirect('dashboard.php');