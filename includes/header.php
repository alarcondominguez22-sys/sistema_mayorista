<?php
require_once __DIR__ . '/config.php';
if (!isset($page_title)) {
    $page_title = 'Sistema Mayorista';
}
if (!isset($active_menu)) {
    $active_menu = '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> | Sistema Mayorista</title>

  <!-- Fuentes e Iconos -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">

  <!-- SweetAlert2 para alertas interactivas -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <style>
    /* Mejoras visuales y micro-interacciones */
    .badge-soft-success {
      background-color: rgba(25, 135, 84, 0.12);
      color: #198754;
      font-weight: 600;
    }
    .badge-soft-danger {
      background-color: rgba(220, 53, 69, 0.12);
      color: #dc3545;
      font-weight: 600;
    }
    .badge-soft-primary {
      background-color: rgba(13, 110, 253, 0.12);
      color: #0d6efd;
      font-weight: 600;
    }
    .badge-soft-warning {
      background-color: rgba(255, 193, 7, 0.18);
      color: #997404;
      font-weight: 600;
    }
    .table-hover tbody tr:hover {
      background-color: rgba(0, 0, 0, 0.02);
    }
    .cursor-pointer {
      cursor: pointer;
    }
    .search-result-dropdown {
      position: absolute;
      width: 100%;
      max-height: 280px;
      overflow-y: auto;
      z-index: 1050;
      background: #fff;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      border-radius: 8px;
      border: 1px solid rgba(0,0,0,0.08);
    }
    .search-result-item {
      padding: 10px 14px;
      border-bottom: 1px solid #f0f0f0;
      transition: all 0.15s ease;
      cursor: pointer;
    }
    .search-result-item:hover, .search-result-item.selected {
      background-color: #f3f6ff;
      border-left: 3px solid #0d6efd;
    }
    .factura-grid th {
      background-color: #f8fafc;
      font-size: 0.82rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #64748b;
    }
    .total-box {
      background: linear-gradient(135deg, #0f172a, #1e293b);
      color: #fff;
      border-radius: 12px;
      padding: 18px;
      box-shadow: 0 4px 15px rgba(15, 23, 42, 0.15);
    }
    .total-highlight {
      font-size: 2.2rem;
      font-weight: 700;
      color: #38bdf8;
      letter-spacing: -0.5px;
    }
    .sidebar-heading {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.7px;
      color: #94a3b8;
      font-weight: 700;
      padding: 8px 14px 2px 14px;
    }

    /* ==========================================================
       AJUSTE COMPACTO PARA PANTALLAS (1366x768 y Escritorio)
       Evita cortes horizontales y permite ver más filas en pantalla
       ========================================================== */
    :root {
      --sidebar-width: 228px !important;
    }
    body {
      font-size: 0.875rem;
    }
    .sidebar-header {
      padding: 0.8rem 0.9rem !important;
    }
    .brand-icon {
      width: 34px !important;
      height: 34px !important;
    }
    .brand-title {
      font-size: 0.95rem !important;
    }
    .brand-subtitle {
      font-size: 0.72rem !important;
    }
    .sidebar-nav {
      gap: 0.2rem !important;
      padding: 0.55rem 0.65rem !important;
    }
    .sidebar-nav .nav-link {
      min-height: 36px !important;
      padding: 0.42rem 0.65rem !important;
      font-size: 0.84rem !important;
      gap: 0.55rem !important;
    }
    .sidebar-user {
      margin: 0.5rem 0.65rem !important;
      padding: 0.55rem !important;
    }
    .admin-navbar {
      min-height: 52px !important;
    }
    .dashboard-content > .container-fluid {
      padding: 0.75rem 1rem !important;
    }
    /* Quitar el min-width: 120px de style.css que desbordaba las tablas */
    .table thead th {
      padding: 0.5rem 0.55rem !important;
      font-size: 0.72rem !important;
      white-space: nowrap;
    }
    .table tbody td {
      min-width: 0 !important;
      padding: 0.45rem 0.55rem !important;
      font-size: 0.83rem !important;
    }
    .badge {
      padding: 0.3rem 0.5rem;
      font-size: 0.73rem;
    }
    /* Botones solo ícono compactos y uniformes */
    .btn-icon {
      width: 32px;
      height: 32px;
      padding: 0 !important;
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      border-radius: 8px !important;
      font-size: 0.92rem;
      line-height: 1;
      flex-shrink: 0;
      transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .btn-icon:hover {
      transform: translateY(-1px);
    }
    .btn-icon-lg {
      width: 38px;
      height: 38px;
      padding: 0 !important;
      display: inline-flex !important;
      align-items: center;
      justify-content: center;
      border-radius: 10px !important;
      font-size: 1.08rem;
      line-height: 1;
      flex-shrink: 0;
      transition: transform 0.12s ease, box-shadow 0.12s ease;
    }
    .btn-icon-lg:hover {
      transform: translateY(-1px);
    }
    .action-icons {
      display: inline-flex;
      align-items: center;
      justify-content: flex-end;
      gap: 4px;
    }
    .total-box {
      padding: 12px 14px !important;
    }
    .total-highlight {
      font-size: 1.7rem !important;
    }
    h1.h3, .h3 {
      font-size: 1.25rem !important;
    }
    h1.h4, .h4 {
      font-size: 1.15rem !important;
    }
    .modal-header {
      padding: 0.65rem 1rem !important;
    }
    .modal-body {
      padding: 0.85rem 1rem !important;
    }
    .modal-footer {
      padding: 0.55rem 1rem !important;
    }

    /* Correcciones globales de visibilidad en Modo Oscuro (Dark Mode) */
    html[data-theme="dark"] .text-dark,
    html[data-theme="dark"] h1,
    html[data-theme="dark"] h2,
    html[data-theme="dark"] h3,
    html[data-theme="dark"] h4,
    html[data-theme="dark"] h5,
    html[data-theme="dark"] h6,
    html[data-theme="dark"] strong,
    html[data-theme="dark"] .fw-bold,
    html[data-theme="dark"] .fw-semibold {
      color: #ffffff !important;
    }
    html[data-theme="dark"] .text-secondary,
    html[data-theme="dark"] .text-muted,
    html[data-theme="dark"] small {
      color: #e2e8f0 !important;
    }
    html[data-theme="dark"] .text-primary {
      color: #60a5fa !important;
    }
    html[data-theme="dark"] .text-success {
      color: #4ade80 !important;
    }
    html[data-theme="dark"] .text-danger {
      color: #f87171 !important;
    }
    html[data-theme="dark"] .text-warning {
      color: #facc15 !important;
    }
    html[data-theme="dark"] .card,
    html[data-theme="dark"] .card-header.bg-white,
    html[data-theme="dark"] .card-footer.bg-white,
    html[data-theme="dark"] .bg-white {
      background-color: var(--admin-surface) !important;
      color: #ffffff !important;
      border-color: var(--admin-border) !important;
    }
    html[data-theme="dark"] .bg-light,
    html[data-theme="dark"] .card-footer.bg-light,
    html[data-theme="dark"] .modal-header.bg-light,
    html[data-theme="dark"] .modal-footer.bg-light,
    html[data-theme="dark"] .input-group-text.bg-light,
    html[data-theme="dark"] .form-control.bg-light,
    html[data-theme="dark"] .form-select.bg-light {
      background-color: #1e293b !important;
      color: #ffffff !important;
      border-color: var(--admin-border) !important;
    }
    html[data-theme="dark"] .table {
      color: #ffffff !important;
    }
    html[data-theme="dark"] .table td,
    html[data-theme="dark"] .table th {
      color: #ffffff !important;
      border-color: var(--admin-border) !important;
    }
    html[data-theme="dark"] .table-light,
    html[data-theme="dark"] .table thead.table-light th,
    html[data-theme="dark"] .factura-grid th {
      background-color: #0f172a !important;
      color: #ffffff !important;
      border-color: var(--admin-border) !important;
    }
    html[data-theme="dark"] .badge.bg-light,
    html[data-theme="dark"] .badge.bg-light.text-dark,
    html[data-theme="dark"] .badge.bg-light.text-muted,
    html[data-theme="dark"] .badge.bg-light.text-secondary {
      background-color: #334155 !important;
      color: #ffffff !important;
      border-color: #475569 !important;
    }
    html[data-theme="dark"] .btn-outline-secondary,
    html[data-theme="dark"] .btn-light {
      color: #ffffff !important;
      border-color: #475569 !important;
    }
    html[data-theme="dark"] .search-result-dropdown {
      background-color: #1e293b !important;
      color: #ffffff !important;
      border-color: #334155 !important;
    }
    html[data-theme="dark"] .search-result-item {
      border-bottom-color: #334155 !important;
      color: #ffffff !important;
    }
    html[data-theme="dark"] .search-result-item:hover,
    html[data-theme="dark"] .search-result-item.selected {
      background-color: #334155 !important;
    }
    html[data-theme="dark"] .list-group-item {
      background-color: var(--admin-surface) !important;
      color: #ffffff !important;
      border-color: var(--admin-border) !important;
    }
  </style>
</head>
<body>
  <div class="admin-shell">
    <div class="sidebar-backdrop" data-sidebar-close></div>
