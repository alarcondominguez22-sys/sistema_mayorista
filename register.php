<?php
require_once __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Registro de Usuario - Sistema Mayorista">
  <title>Crear Cuenta | Sistema Mayorista</title>

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
        <span><strong>SISTEMA MAYORISTA</strong><small>Registro de Operador</small></span>
      </a>
      <div class="auth-visual">
        <img src="<?= BASE_URL ?>assets/images/svg/login-mayorista.svg" alt="Panel Sistema Mayorista">
      </div>
      <form id="formRegister" onsubmit="registrarUsuario(event)" novalidate>
        <div class="mb-4">
          <p class="eyebrow mb-1">Registro de Personal</p>
          <h1 class="h3 mb-1">Crear Cuenta</h1>
          <p class="text-muted mb-0">Habilita un nuevo usuario para operar en el sistema mayorista.</p>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="regNombre">Nombre y Apellido</label>
          <input class="form-control" id="regNombre" name="nombre_apellido" type="text" placeholder="Ej: Carlos Gómez" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="regNick">Nombre de Usuario (Login)</label>
          <input class="form-control" id="regNick" name="nick_name" type="text" placeholder="Ej: CGOMEZ" required>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold" for="regSucursal">Sucursal Asignada</label>
          <select class="form-select" id="regSucursal" name="id_sucursal">
            <option value="1">Sucursal 1 (Avda Lopez Godoy)</option>
            <option value="2">Sucursal 2 (Avda Mcal Lopez)</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold" for="regPassword">Contraseña</label>
          <input class="form-control" id="regPassword" name="password" type="password" minlength="3" placeholder="Mínimo 3 caracteres" required>
        </div>
        <button class="btn btn-primary w-100 py-2 fw-bold" type="submit" id="btnRegister">
          <i class="bi bi-person-plus me-1" aria-hidden="true"></i> Registrar Usuario
        </button>
      </form>
      
      <div class="auth-footer">¿Ya tienes una cuenta? <a href="<?= BASE_URL ?>login.php">Iniciar Sesión</a></div>
    </section>
  </main>

  <script src="<?= BASE_URL ?>assets/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="<?= BASE_URL ?>assets/js/main.js"></script>
  <script>
  async function registrarUsuario(e) {
    e.preventDefault();
    const btn = document.getElementById('btnRegister');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Registrando...';

    const fd = new FormData(document.getElementById('formRegister'));
    fd.append('accion', 'registrar');

    try {
      const res = await fetch('<?= BASE_URL ?>controladores/UsuarioSession.php', {
        method: 'POST',
        body: fd
      });
      const json = await res.json();
      if (json.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Cuenta creada',
          text: json.message,
          timer: 1500,
          showConfirmButton: false
        }).then(() => {
          window.location.href = json.redirect || '<?= BASE_URL ?>index.php';
        });
      } else {
        Swal.fire('Atención', json.message || 'No se pudo registrar el usuario.', 'warning');
      }
    } catch (err) {
      Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
    } finally {
      btn.disabled = false;
      btn.innerHTML = '<i class="bi bi-person-plus me-1"></i> Registrar Usuario';
    }
  }
  </script>
</body>
</html>
