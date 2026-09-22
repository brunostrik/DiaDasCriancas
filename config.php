<?php
/**
 * Configuração do banco de dados
 *
 * Altere HOST, DB_NAME, USER e PASS conforme seu ambiente.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'diadascriancas');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', 'https://astorgaifpr.online/diadascriancas');
define('ADMIN_USER', 'admin');
define('UPLOADS_DIR', __DIR__ . '/uploads');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);

/**
 * Retorna uma conexão PDO com o banco MySQL.
 */
function getDB(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
  }
  return $pdo;
}

/**
 * Inicia sessão se ainda não iniciada.
 */
function ensureSession(): void {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

/**
 * Redireciona com Exit.
 */
function redirect(string $url): void {
  header('Location: ' . $url);
  exit;
}