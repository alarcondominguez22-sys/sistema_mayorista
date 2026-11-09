<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Acceso al Sistema Mayorista">
  <title>Iniciar Sesión | Sistema Mayorista</title>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body class="auth-body">
  <button class="icon-button theme-toggle auth-theme-toggle" type="button" data-theme-toggle aria-label="Cambiar tema" title="Cambiar tema">
    <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
  </button>
  <main class="auth-page">
    <section class="auth-card">
      <a class="auth-brand" href="<?= BASE_URL ?>index.php">
        <span class="brand-icon"><i class="bi bi-shop" aria-hidden="true"></i></span>
        <span><strong>SISTEMA MAYORISTA</strong><small>Gestión, Inventario & Facturación</small></span>
      </a>
      <div class="auth-visual">
        <img src="<?= BASE_URL ?>assets/images/svg/login-mayorista.svg" alt="Panel Sistema Mayorista">
      </div>
      <form id="formLogin" onsubmit="iniciarSesion(event)" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1">Acceso al Sistema</p>
          <h1 class="h3 mb-1">Iniciar Sesión</h1>
          <p class="text-muted mb-0">Ingresa tus credenciales para acceder al sistema.</p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="loginUsuario">Usuario</label>
          <input class="form-control" id="loginUsuario" name="usuario" type="text" placeholder="Ingresa tu usuario" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold" for="loginPassword">Contraseña</label>
          <input class="form-control" id="loginPassword" name="password" type="password" placeholder="Ingresa tu contraseña" required>
        </div>
        <button class="btn btn-primary w-100 py-2 fw-bold" type="submit" id="btnLogin">
          <i class="bi bi-box-arrow-in-right me-1" aria-hidden="true"></i> Ingresar al Sistema
        </button>
      </form>
      
      <div class="auth-footer">
        ¿Nuevo usuario? <a href="<?= BASE_URL ?>register.php">Crear una cuenta</a> • <a href="<?= BASE_URL ?>index.php">Ir al Panel</a>
      </div>
    </section>
  </main>

  <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?= BASE_URL ?>assets/js/main.js"></script>
  <script>
  async function iniciarSesion(e) {
    e.preventDefault();
    const btn = document.getElementById('btnLogin');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Verificando...';

    const fd = new FormData(document.getElementById('formLogin'));
    fd.append('accion', 'login');

    try {
      const res = await fetch('<?= BASE_URL ?>controladores/UsuarioSession.php', {
        method: 'POST',
        body: fd
      });
      const json = await res.json();
      if (json.status === 'success') {
        window.location.href = json.redirect || '<?= BASE_URL ?>index.php';
      } else {
        Swal.fire('Atención', json.message || 'Credenciales inválidas', 'warning');
      }
    } catch (err) {
      Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-box-arrow-in-right me-1"></i> Ingresar al Sistema';
    }
  }
  </script>
</body>
</html>
