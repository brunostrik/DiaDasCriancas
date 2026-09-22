/**
 * Dia das Crianças — Main JS
 * Máscara de telefone + requisições AJAX
 */

(function () {

  /* ==============================
     1. Máscara de Telefone (xx) xxxxx-xxxx
     ============================== */
  function phoneMask(input) {
    input.addEventListener('input', function () {
      let value = this.value.replace(/\D/g, ''); // só dígitos
      if (value.length > 11) value = value.slice(0, 11);

      let formatted = '';
      if (value.length > 0) {
        formatted += '(' + value.slice(0, 2);
        if (value.length > 2) {
          formatted += ') ' + value.slice(2, 7);
          if (value.length > 7) {
            formatted += '-' + value.slice(7);
          }
        } else {
          formatted += ') ';
        }
      }
      this.value = formatted;
    });
  }

  /* ==============================
     2. Validação do formulário inicial
     ============================== */
  function initForm() {
    const form = document.getElementById('entryForm');
    if (!form) return;

    const nameInput  = document.getElementById('name');
    const phoneInput = document.getElementById('phone');
    const submitBtn  = form.querySelector('button[type="submit"]');
    const errorMsg   = document.getElementById('formError');

    phoneMask(phoneInput);

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      errorMsg.classList.remove('show');
      phoneInput.classList.remove('error');
      nameInput.classList.remove('error');

      // validação client-side simples
      if (!nameInput.value.trim()) {
        nameInput.classList.add('error');
        showError(nameInput, 'Preencha seu nome');
        return;
      }

      const phoneRegex = /^\(\d{2}\)\s\d{5}-\d{4}$/;
      if (!phoneRegex.test(phoneInput.value.trim())) {
        phoneInput.classList.add('error');
        showError(phoneInput, 'Telefone inválido. Use o formato (xx) xxxxx-xxxx');
        return;
      }

      // submit via AJAX
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span> Verificando...';

      const data = new URLSearchParams();
      data.set('name', nameInput.value.trim());
      data.set('phone', phoneInput.value.trim());

      fetch('process.php', {
        method: 'POST',
        body: data,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
      })
        .then(function (r) { return r.json(); })
        .then(function (json) {
          if (json.success) {
            window.location.href = 'guess.php';
          } else {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Entrar no Jogo';
            phoneInput.classList.add('error');
            errorMsg.textContent = json.message || 'Erro desconhecido.';
            errorMsg.classList.add('show');
          }
        })
        .catch(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Entrar no Jogo';
          errorMsg.textContent = 'Erro de conexão. Tente novamente.';
          errorMsg.classList.add('show');
        });
    });
  }

  /* ==============================
     3. Submissão dos palpites
     ============================== */
  function initGuessForm() {
    const form = document.getElementById('guessForm');
    if (!form) return;

    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      const selects = form.querySelectorAll('select');
      let allFilled = true;
      selects.forEach(function (sel) {
        sel.classList.remove('error');
        if (!sel.value) {
          sel.classList.add('error');
          allFilled = false;
        }
      });

      if (!allFilled) {
        alert('Escolha um nome para cada foto antes de enviar!');
        return;
      }

      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span> Enviando...';

      const data = new URLSearchParams();
      selects.forEach(function (sel) {
        data.append('guesses[' + sel.dataset.photoId + ']', sel.value);
      });

      fetch('submit_guesses.php', {
        method: 'POST',
        body: data,
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
      })
        .then(function (r) { return r.json(); })
        .then(function (json) {
          if (json.success) {
            form.innerHTML = '<div class="confirmation">' +
              '<div class="icon">&#127881;</div>' +
              '<h2>Obrigado por participar!</h2>' +
              '<p>Seus palpites foram registrados com sucesso!</p>' +
              '</div>';
          } else {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Palpites';
            alert(json.message || 'Erro ao enviar. Tente novamente.');
          }
        })
        .catch(function () {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Enviar Palpites';
          alert('Erro de conexão. Tente novamente.');
        });
    });
  }

  /* ==============================
     4. Helpers
     ============================== */
  function showError(input, msg) {
    const parent = input.closest('.form-group');
    if (!parent) return;
    const el = parent.querySelector('.error-msg');
    if (el) {
      el.textContent = msg;
      el.classList.add('show');
    }
  }

  /* ==============================
     5. Init
     ============================== */
  document.addEventListener('DOMContentLoaded', function () {
    initForm();
    initGuessForm();
  });

})();