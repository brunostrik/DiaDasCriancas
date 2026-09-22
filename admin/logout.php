<?php
/**
 * admin/logout.php — Logout do administrador
 */
require_once __DIR__ . '/../config.php';
ensureSession();

unset($_SESSION['admin_logged_in'], $_SESSION['admin_id']);
redirect('index.php');