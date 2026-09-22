<?php
require_once __DIR__ . '/config.php';
ensureSession();

// Se já participou nesta sessão, redireciona para guess
if (!empty($_SESSION['participant_id'])) {
  redirect('guess.php');
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dia das Crianças</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="header">
    <h1>🎈 Dia das Crianças</h1>
    <p>Descubra quem está nas fotos e participe!</p>
  </div>

  <div class="container">
    <div class="card">
      <form id="entryForm" novalidate>
        <!-- Nome -->
        <div class="form-group">
          <label for="name">Seu Nome</label>
          <input type="text" id="name" name="name" placeholder="Digite seu nome" required maxlength="255">
          <div class="error-msg"></div>
        </div>

        <!-- Celular -->
        <div class="form-group">
          <label for="phone">Celular</label>
          <input type="tel" id="phone" name="phone" placeholder="(xx) xxxxx-xxxx" required maxlength="16">
          <div class="error-msg"></div>
        </div>

        <!-- Erro geral (ex: já participou) -->
        <div class="error-msg" id="formError" style="margin-bottom:12px;"></div>

        <button type="submit" class="btn btn-primary">Entrar no Jogo</button>
      </form>
    </div>
  </div>

  <script src="js/main.js"></script>
</body>
</html>