/**
 * SISTEMA MAYORISTA - MOTOR Y VISOR DE IMPRESIONES PROFESIONAL
 * Soporta Ticket Térmico (80mm / 58mm), Factura Comercial A4, Recibos y Cierres de Caja.
 */

const ImpresorMayorista = {
  // Datos comerciales por defecto
  empresa: {
    nombre: 'SISTEMA MAYORISTA',
    razonSocial: 'COMERCIAL & DISTRIBUIDORA MAYORISTA S.A.',
    actividad: 'VENTA AL POR MAYOR Y MENOR DE ARTÍCULOS DE TECNOLOGÍA Y VARIEDADES',
    ruc: '80012345-6',
    timbrado: '18216563',
    vigenciaTimbradoDesde: '01/01/2026',
    vigenciaTimbradoHasta: '31/12/2026',
    direccion: 'Avda. Principal c/ Rodríguez de Francia',
    ciudad: 'San Lorenzo - Paraguay',
    telefono: '(021) 580-000 / (0981) 123-456',
    email: 'contacto@sistemamayorista.com.py'
  },

  estadoActual: {
    tipo: 'factura_venta', // 'factura_venta', 'recibo_cobro', 'cierre_caja', 'compra'
    formato: 'ticket',     // 'ticket' o 'a4'
    datos: null
  },

  // Conversión de números enteros a letras (Guaraníes)
  numeroALetras(monto) {
    monto = Math.round(Number(monto) || 0);
    if (monto === 0) return 'CERO GUARANÍES';

    const unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    const especiales = ['DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'];
    const decenas = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    const centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

    function convertirGrupo(n) {
      let salida = '';
      const c = Math.floor(n / 100);
      const d = Math.floor((n % 100) / 10);
      const u = n % 10;

      if (n === 100) return 'CIEN';
      if (c > 0) salida += centenas[c] + ' ';

      if (d === 1) {
        salida += especiales[u] + ' ';
      } else if (d === 2 && u > 0) {
        salida += 'VEINTI' + unidades[u] + ' ';
      } else {
        if (d > 0) salida += decenas[d] + (u > 0 ? ' Y ' : ' ');
        if (u > 0) salida += unidades[u] + ' ';
      }
      return salida.trim();
    }

    let millones = Math.floor(monto / 1000000);
    let miles = Math.floor((monto % 1000000) / 1000);
    let resto = monto % 1000;
    let texto = '';

    if (millones > 0) {
      if (millones === 1) texto += 'UN MILLÓN ';
      else texto += convertirGrupo(millones) + ' MILLONES ';
    }
    if (miles > 0) {
      if (miles === 1) texto += 'UN MIL ';
      else texto += convertirGrupo(miles) + ' MIL ';
    }
    if (resto > 0) {
      texto += convertirGrupo(resto) + ' ';
    }

    return (texto.trim() + ' GUARANÍES').toUpperCase();
  },

  formatoGs(num) {
    return new Intl.NumberFormat('es-PY').format(Math.round(num || 0)) + ' Gs.';
  },

  formatoGsSinTexto(num) {
    return new Intl.NumberFormat('es-PY').format(Math.round(num || 0));
  },

  escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  },

  // Inicializa el contenedor del visor interactivo en la página
  asegurarModalVisor() {
    if (document.getElementById('modalVisorImpresionMayorista')) return;

    const htmlModal = `
      <div class="modal fade" id="modalVisorImpresionMayorista" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <i class="bi bi-printer-fill fs-5 text-info"></i>
                <div>
                  <h6 class="modal-title fw-bold mb-0 text-white" id="visorImpresionTitulo">Vista Previa de Impresión</h6>
                  <small class="text-white-50" style="font-size: 0.75rem;">Optimizado para ticket térmico POS y facturación A4</small>
                </div>
              </div>
              <div class="d-flex align-items-center gap-2">
                <!-- Selector de Formato -->
                <div class="btn-group btn-group-sm p-1 bg-secondary bg-opacity-25 rounded-pill" role="group">
                  <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" id="btnVisorFormatoTicket" onclick="ImpresorMayorista.cambiarFormato('ticket')">
                    <i class="bi bi-receipt me-1"></i> Ticket (80mm)
                  </button>
                  <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3" id="btnVisorFormatoA4" onclick="ImpresorMayorista.cambiarFormato('a4')">
                    <i class="bi bi-file-earmark-text me-1"></i> Factura A4
                  </button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-light ms-2" data-bs-dismiss="modal">
                  <i class="bi bi-x-lg"></i>
                </button>
              </div>
            </div>

            <!-- Área de vista previa con scroll y fondo neutro -->
            <div class="modal-body p-3 p-md-4" style="background-color: #525659; min-height: 480px;">
              <div class="d-flex justify-content-center">
                <div id="hojaImpresionPreview" class="shadow-lg bg-white" style="transition: all 0.2s ease;">
                  <!-- Aquí se inyecta el HTML renderizado -->
                </div>
              </div>
            </div>

            <div class="modal-footer bg-light py-2 px-3 d-flex justify-content-between">
              <div class="text-muted small">
                <i class="bi bi-info-circle me-1"></i> Puede imprimir directamente o guardar como PDF desde el cuadro de diálogo de impresión.
              </div>
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                  <i class="bi bi-arrow-left me-1"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" onclick="ImpresorMayorista.ejecutarImpresion()">
                  <i class="bi bi-printer me-1"></i> Imprimir Ahora
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', htmlModal);
  },

  // Abre el visor con los datos proporcionados
  verVenta(datosVenta, formatoInicial = 'ticket') {
    this.asegurarModalVisor();
    this.estadoActual = {
      tipo: 'factura_venta',
      formato: formatoInicial,
      datos: datosVenta
    };

    const tituloEl = document.getElementById('visorImpresionTitulo');
    const nro = (datosVenta.cabecera && datosVenta.cabecera.nro_factura) || datosVenta.nro_factura || 'Comprobante';
    if (tituloEl) tituloEl.innerText = `Factura de Venta ${nro}`;

    // Mostrar los dos botones de formato (Ticket y A4)
    document.getElementById('btnVisorFormatoTicket').style.display = 'inline-block';
    document.getElementById('btnVisorFormatoA4').style.display = 'inline-block';

    this.actualizarPreview();
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVisorImpresionMayorista'));
    modal.show();
  },

  verRecibo(datosRecibo, formatoInicial = 'ticket') {
    this.asegurarModalVisor();
    this.estadoActual = {
      tipo: 'recibo_cobro',
      formato: formatoInicial,
      datos: datosRecibo
    };

    const tituloEl = document.getElementById('visorImpresionTitulo');
    if (tituloEl) tituloEl.innerText = `Recibo Oficial ${datosRecibo.nro_recibo || ''}`;

    document.getElementById('btnVisorFormatoTicket').style.display = 'inline-block';
    document.getElementById('btnVisorFormatoA4').style.display = 'inline-block';

    this.actualizarPreview();
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVisorImpresionMayorista'));
    modal.show();
  },

  verCierreCaja(datosCaja, formatoInicial = 'ticket') {
    this.asegurarModalVisor();
    this.estadoActual = {
      tipo: 'cierre_caja',
      formato: formatoInicial,
      datos: datosCaja
    };

    const tituloEl = document.getElementById('visorImpresionTitulo');
    const idCaja = (datosCaja.caja && datosCaja.caja.id_apertura_cierre_caja) || datosCaja.id_apertura_cierre_caja || '';
    if (tituloEl) tituloEl.innerText = `Arqueo y Cierre de Caja #${idCaja}`;

    document.getElementById('btnVisorFormatoTicket').style.display = 'inline-block';
    document.getElementById('btnVisorFormatoA4').style.display = 'inline-block';

    this.actualizarPreview();
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVisorImpresionMayorista'));
    modal.show();
  },

  verCompra(datosCompra, formatoInicial = 'a4') {
    this.asegurarModalVisor();
    this.estadoActual = {
      tipo: 'compra',
      formato: formatoInicial,
      datos: datosCompra
    };

    const tituloEl = document.getElementById('visorImpresionTitulo');
    const nro = (datosCompra.cabecera && datosCompra.cabecera.nro_factura) || datosCompra.nro_factura || '';
    if (tituloEl) tituloEl.innerText = `Comprobante de Ingreso / Compra #${nro}`;

    document.getElementById('btnVisorFormatoTicket').style.display = 'none';
    document.getElementById('btnVisorFormatoA4').style.display = 'inline-block';

    this.actualizarPreview();
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVisorImpresionMayorista'));
    modal.show();
  },

  cambiarFormato(nuevoFormato) {
    this.estadoActual.formato = nuevoFormato;
    this.actualizarPreview();
  },

  actualizarPreview() {
    const contenedor = document.getElementById('hojaImpresionPreview');
    if (!contenedor) return;

    const btnTicket = document.getElementById('btnVisorFormatoTicket');
    const btnA4 = document.getElementById('btnVisorFormatoA4');

    if (this.estadoActual.formato === 'ticket') {
      btnTicket.className = 'btn btn-sm btn-primary rounded-pill px-3';
      btnA4.className = 'btn btn-sm btn-outline-light rounded-pill px-3';
      contenedor.style.width = '340px';
      contenedor.style.maxWidth = '100%';
      contenedor.style.padding = '18px 14px';
      contenedor.style.borderRadius = '4px';
    } else {
      btnTicket.className = 'btn btn-sm btn-outline-light rounded-pill px-3';
      btnA4.className = 'btn btn-sm btn-primary rounded-pill px-3';
      contenedor.style.width = '820px';
      contenedor.style.maxWidth = '100%';
      contenedor.style.padding = '36px 40px';
      contenedor.style.borderRadius = '2px';
    }

    let html = '';
    const { tipo, formato, datos } = this.estadoActual;

    if (tipo === 'factura_venta') {
      html = formato === 'ticket' ? this.plantillaTicketVenta(datos) : this.plantillaFacturaA4(datos);
    } else if (tipo === 'recibo_cobro') {
      html = formato === 'ticket' ? this.plantillaTicketRecibo(datos) : this.plantillaReciboA4(datos);
    } else if (tipo === 'cierre_caja') {
      html = formato === 'ticket' ? this.plantillaTicketCierreCaja(datos) : this.plantillaCierreCajaA4(datos);
    } else if (tipo === 'compra') {
      html = this.plantillaCompraA4(datos);
    }

    contenedor.innerHTML = html;
  },

  // Genera el SVG decorativo del código de barras
  generarSvgCodigoBarras(codigo) {
    codigo = String(codigo || '800123450001');
    return `
      <svg width="220" height="42" viewBox="0 0 220 42" xmlns="http://www.w3.org/2000/svg" style="display:inline-block;">
        <rect x="0" y="0" width="3" height="30" fill="#000"/>
        <rect x="5" y="0" width="1" height="30" fill="#000"/>
        <rect x="8" y="0" width="4" height="30" fill="#000"/>
        <rect x="15" y="0" width="2" height="30" fill="#000"/>
        <rect x="20" y="0" width="3" height="30" fill="#000"/>
        <rect x="25" y="0" width="1" height="30" fill="#000"/>
        <rect x="29" y="0" width="3" height="30" fill="#000"/>
        <rect x="34" y="0" width="5" height="30" fill="#000"/>
        <rect x="42" y="0" width="2" height="30" fill="#000"/>
        <rect x="47" y="0" width="4" height="30" fill="#000"/>
        <rect x="54" y="0" width="1" height="30" fill="#000"/>
        <rect x="58" y="0" width="3" height="30" fill="#000"/>
        <rect x="64" y="0" width="4" height="30" fill="#000"/>
        <rect x="71" y="0" width="2" height="30" fill="#000"/>
        <rect x="76" y="0" width="1" height="30" fill="#000"/>
        <rect x="80" y="0" width="4" height="30" fill="#000"/>
        <rect x="87" y="0" width="2" height="30" fill="#000"/>
        <rect x="92" y="0" width="4" height="30" fill="#000"/>
        <rect x="99" y="0" width="1" height="30" fill="#000"/>
        <rect x="103" y="0" width="3" height="30" fill="#000"/>
        <rect x="109" y="0" width="5" height="30" fill="#000"/>
        <rect x="117" y="0" width="2" height="30" fill="#000"/>
        <rect x="122" y="0" width="3" height="30" fill="#000"/>
        <rect x="128" y="0" width="2" height="30" fill="#000"/>
        <rect x="133" y="0" width="4" height="30" fill="#000"/>
        <rect x="140" y="0" width="1" height="30" fill="#000"/>
        <rect x="144" y="0" width="3" height="30" fill="#000"/>
        <rect x="150" y="0" width="5" height="30" fill="#000"/>
        <rect x="158" y="0" width="2" height="30" fill="#000"/>
        <rect x="163" y="0" width="4" height="30" fill="#000"/>
        <rect x="170" y="0" width="1" height="30" fill="#000"/>
        <rect x="174" y="0" width="3" height="30" fill="#000"/>
        <rect x="180" y="0" width="4" height="30" fill="#000"/>
        <rect x="187" y="0" width="2" height="30" fill="#000"/>
        <rect x="192" y="0" width="4" height="30" fill="#000"/>
        <rect x="199" y="0" width="2" height="30" fill="#000"/>
        <rect x="204" y="0" width="3" height="30" fill="#000"/>
        <rect x="210" y="0" width="1" height="30" fill="#000"/>
        <rect x="214" y="0" width="4" height="30" fill="#000"/>
        <text x="110" y="40" font-family="monospace" font-size="10" text-anchor="middle" fill="#222">${this.escapeHtml(codigo)}</text>
      </svg>
    `;
  },

  // -------------------------------------------------------------
  // 1. PLANTILLA: TICKET TÉRMICO DE VENTA (80mm)
  // -------------------------------------------------------------
  plantillaTicketVenta(datos) {
    const cab = datos.cabecera || datos;
    const det = datos.detalles || datos.items || [];
    const esCredito = (cab.condicion_pago === 'CREDITO' || cab.condicion === 'CREDITO');
    const nroFac = cab.nro_factura || ('#' + (cab.id_venta || ''));
    const fecha = cab.fecha_venta || cab.fecha || new Date().toISOString().slice(0, 10);
    const cliNom = cab.nombre_cliente ? `${cab.nombre_cliente} ${cab.apellido_cliente || ''}`.trim() : (cab.cliente || 'CONSUMIDOR FINAL');
    const cliDoc = cab.cedula_cliente || cab.ruc_cliente || cab.cedula || '000000';
    const sucNom = cab.sucursal_nombre || `Sucursal #${cab.id_sucursal || 1}`;

    let totalIva10 = 0;
    let totalIva5 = 0;
    let totalExenta = 0;
    let itemsHtml = '';

    det.forEach(it => {
      const cant = Number(it.cantidad) || 1;
      const precio = Number(it.precio_unitario) || 0;
      const sub = cant * precio;
      const desc = this.escapeHtml(it.descripcion || it.nombre_prod || 'Producto');
      const tipoImp = Number(it.id_tipo_impuesto) || 1;

      if (tipoImp === 1) totalIva10 += sub;
      else if (tipoImp === 2) totalIva5 += sub;
      else totalExenta += sub;

      const tasaBadge = tipoImp === 1 ? '10%' : (tipoImp === 2 ? '5%' : 'EX');

      itemsHtml += `
        <div style="margin-bottom: 5px; font-size: 11.5px; line-height: 1.25;">
          <div style="font-weight: 600; color: #111;">${desc}</div>
          <div style="display: flex; justify-content: space-between; color: #333; font-size: 11px;">
            <span>${cant} un. x ${this.formatoGsSinTexto(precio)} [${tasaBadge}]</span>
            <span style="font-weight: bold;">${this.formatoGsSinTexto(sub)}</span>
          </div>
        </div>
      `;
    });

    const totalVenta = Number(cab.total_venta) || Number(cab.total) || (totalIva10 + totalIva5 + totalExenta);
    const liqIva10 = Math.round(totalIva10 / 11);
    const liqIva5 = Math.round(totalIva5 / 21);
    const liqTotal = liqIva10 + liqIva5;

    // Métodos de pago en contado
    let pagosContadoHtml = '';
    let pagosArr = [];
    if (typeof cab.pagos_json === 'string' && cab.pagos_json.trim().startsWith('[')) {
      try { pagosArr = JSON.parse(cab.pagos_json); } catch(e){}
    } else if (Array.isArray(cab.pagos_detalle)) {
      pagosArr = cab.pagos_detalle;
    }

    if (!esCredito && pagosArr.length > 0) {
      pagosContadoHtml = `
        <div style="border-top: 1px dashed #666; margin-top: 6px; padding-top: 5px; font-size: 11px;">
          <div style="font-weight: bold; margin-bottom: 2px;">DESGLOSE FORMA DE PAGO:</div>
          ${pagosArr.map(p => `
            <div style="display:flex; justify-content:space-between;">
              <span>• ${this.escapeHtml(p.metodo)}:</span>
              <span>${this.formatoGs(p.monto)}</span>
            </div>
          `).join('')}
        </div>
      `;
    }

    // Sección de crédito y pagaré si aplica
    let creditoHtml = '';
    const cred = datos.credito || cab.credito;
    if (esCredito && cred) {
      const cuotasList = cred.cuotas || [];
      const entregaInit = Number(cred.entrega_inicial) || 0;
      const saldoFin = Number(cred.saldo_a_financiar || cred.saldo_pendiente) || (totalVenta - entregaInit);

      let lineasCuotas = '';
      cuotasList.forEach(c => {
        const num = c.numero || c.numero_cuota;
        const mon = c.monto || c.monto_cuota;
        const venc = c.vencimiento || c.fecha_vencimiento || '';
        lineasCuotas += `
          <div style="display:flex; justify-content:space-between; font-size: 10.5px; padding: 1px 0;">
            <span>Cuota ${num} (Vto: ${this.escapeHtml(venc)}):</span>
            <span style="font-weight: 600;">${this.formatoGs(mon)}</span>
          </div>
        `;
      });

      creditoHtml = `
        <div style="border: 1px dashed #000; padding: 6px; margin: 8px 0; background: #fafafa; border-radius: 4px;">
          <div style="text-align: center; font-weight: bold; font-size: 11px; margin-bottom: 4px;">PLAN DE CUOTAS A CRÉDITO</div>
          ${entregaInit > 0 ? `
            <div style="display:flex; justify-content:space-between; font-size: 11px; color: #15803d;">
              <span>Entrega Inicial Pagada:</span>
              <strong>${this.formatoGs(entregaInit)}</strong>
            </div>
          ` : ''}
          <div style="display:flex; justify-content:space-between; font-size: 11px;">
            <span>Saldo a Financiar:</span>
            <strong>${this.formatoGs(saldoFin)}</strong>
          </div>
          <div style="display:flex; justify-content:space-between; font-size: 11px; margin-bottom: 4px;">
            <span>Cantidad de Cuotas:</span>
            <span>${cred.cantidad_cuotas || cuotasList.length} cuotas</span>
          </div>
          <div style="border-top: 1px solid #ccc; padding-top: 3px;">
            ${lineasCuotas}
          </div>

          <!-- PAGARÉ A LA ORDEN -->
          <div style="margin-top: 8px; border-top: 1px solid #000; padding-top: 5px; font-size: 9.5px; line-height: 1.25; text-align: justify;">
            <div style="text-align: center; font-weight: bold; font-size: 10px; margin-bottom: 2px;">PAGARÉ A LA ORDEN</div>
            Por este pagaré me comprometo incondicionalmente a abonar a la orden de <strong>${this.escapeHtml(this.empresa.nombre)}</strong> la suma de <strong>${this.formatoGs(saldoFin)}</strong> en las cuotas y vencimientos pactados precedentemente.
            <div style="margin-top: 22px; text-align: center; border-top: 1px solid #000; padding-top: 3px;">
              <div>Firma del Deudor / Comprador</div>
              <div style="font-weight: bold;">${this.escapeHtml(cliNom)}</div>
              <div>C.I. / RUC: ${this.escapeHtml(cliDoc)}</div>
            </div>
          </div>
        </div>
      `;
    }

    return `
      <div class="ticket-impresion" style="font-family: 'Segoe UI', Tahoma, monospace, sans-serif; color: #111; font-size: 12px; line-height: 1.3;">
        <!-- Encabezado de Ticket -->
        <div style="text-align: center; border-bottom: 1px dashed #444; padding-bottom: 8px; margin-bottom: 8px;">
          <div style="font-size: 16px; font-weight: 800; letter-spacing: 0.5px; color: #000;">${this.escapeHtml(this.empresa.nombre)}</div>
          <div style="font-size: 10px; color: #444; text-transform: uppercase;">${this.escapeHtml(this.empresa.razonSocial)}</div>
          <div style="font-size: 10px; color: #555;">RUC: <strong>${this.empresa.ruc}</strong></div>
          <div style="font-size: 10px; color: #555;">${this.escapeHtml(this.empresa.direccion)}</div>
          <div style="font-size: 10px; color: #555;">Tel: ${this.empresa.telefono}</div>
          <div style="font-size: 10.5px; font-weight: bold; margin-top: 3px; color: #000;">${this.escapeHtml(sucNom)}</div>
        </div>

        <!-- Datos Tributarios y Factura -->
        <div style="font-size: 11px; border-bottom: 1px dashed #444; padding-bottom: 6px; margin-bottom: 6px;">
          <div style="display: flex; justify-content: space-between;">
            <span>TIMBRADO N°:</span>
            <strong>${this.empresa.timbrado}</strong>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>VÁLIDO HASTA:</span>
            <span>${this.empresa.vigenciaTimbradoHasta}</span>
          </div>
          <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 800; color: #000; margin: 2px 0;">
            <span>FACTURA:</span>
            <span>${this.escapeHtml(nroFac)}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>FECHA:</span>
            <span>${this.escapeHtml(fecha)}</span>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <span>CONDICIÓN:</span>
            <strong>${esCredito ? 'CRÉDITO' : 'CONTADO'} (${this.escapeHtml(cab.metodo_pago || 'EFECTIVO')})</strong>
          </div>
        </div>

        <!-- Datos del Cliente -->
        <div style="font-size: 11px; border-bottom: 1px dashed #444; padding-bottom: 6px; margin-bottom: 8px;">
          <div>CLIENTE: <strong>${this.escapeHtml(cliNom)}</strong></div>
          <div>CÉDULA / RUC: <strong>${this.escapeHtml(cliDoc)}</strong></div>
          ${cab.direccion_cliente ? `<div>DIR: ${this.escapeHtml(cab.direccion_cliente)}</div>` : ''}
        </div>

        <!-- Encabezado de Productos -->
        <div style="font-size: 11px; font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 5px; display:flex; justify-content:space-between;">
          <span>DESCRIPCIÓN</span>
          <span>SUBTOTAL</span>
        </div>

        <!-- Lista de Productos -->
        <div>
          ${itemsHtml}
        </div>

        <!-- TOTAL GENERAL -->
        <div style="border-top: 1.5px solid #000; border-bottom: 1.5px solid #000; padding: 6px 0; margin-top: 6px; display:flex; justify-content:space-between; align-items:center;">
          <span style="font-size: 13px; font-weight: 800;">TOTAL A PAGAR:</span>
          <span style="font-size: 16px; font-weight: 800; color: #000;">${this.formatoGs(totalVenta)}</span>
        </div>

        ${pagosContadoHtml}
        ${creditoHtml}

        <!-- LIQUIDACIÓN DEL IVA (Paraguay) -->
        <div style="border-top: 1px dashed #666; margin-top: 8px; padding-top: 5px; font-size: 10px; color: #333;">
          <div style="font-weight: bold; text-align: center; margin-bottom: 2px;">LIQUIDACIÓN DEL IVA</div>
          <div style="display:flex; justify-content:space-between;">
            <span>(5%): ${this.formatoGs(liqIva5)}</span>
            <span>(10%): ${this.formatoGs(liqIva10)}</span>
          </div>
          <div style="display:flex; justify-content:space-between; font-weight: bold; margin-top: 1px;">
            <span>TOTAL IVA:</span>
            <span>${this.formatoGs(liqTotal)}</span>
          </div>
        </div>

        <!-- Pie y Código de Barras -->
        <div style="text-align: center; margin-top: 12px; border-top: 1px dashed #444; padding-top: 8px;">
          ${this.generarSvgCodigoBarras(nroFac.replace(/\D/g, '') || cab.id_venta || '100123')}
          <div style="font-size: 10.5px; font-weight: bold; margin-top: 4px;">¡GRACIAS POR SU COMPRA!</div>
          <div style="font-size: 9px; color: #666;">Original: Cliente • Duplicado: Archivo Tributario</div>
        </div>
      </div>
    `;
  },

  // -------------------------------------------------------------
  // 2. PLANTILLA: FACTURA COMERCIAL TAMAÑO A4 (CORPORATIVA)
  // -------------------------------------------------------------
  plantillaFacturaA4(datos) {
    const cab = datos.cabecera || datos;
    const det = datos.detalles || datos.items || [];
    const esCredito = (cab.condicion_pago === 'CREDITO' || cab.condicion === 'CREDITO');
    const nroFac = cab.nro_factura || ('001-001-' + String(cab.id_venta || 1).padStart(7, '0'));
    const fecha = cab.fecha_venta || cab.fecha || new Date().toISOString().slice(0, 10);
    const cliNom = cab.nombre_cliente ? `${cab.nombre_cliente} ${cab.apellido_cliente || ''}`.trim() : (cab.cliente || 'CONSUMIDOR FINAL');
    const cliDoc = cab.cedula_cliente || cab.ruc_cliente || cab.cedula || '000000';
    const cliDir = cab.direccion_cliente || 'Asunción / Central';
    const cliTel = cab.telefono_cliente || cab.telefono || '-';
    const sucNom = cab.sucursal_nombre || `Casa Central / Sucursal #${cab.id_sucursal || 1}`;

    let totalIva10 = 0;
    let totalIva5 = 0;
    let totalExenta = 0;
    let filasItemsHtml = '';

    det.forEach((it, idx) => {
      const cant = Number(it.cantidad) || 1;
      const precio = Number(it.precio_unitario) || 0;
      const sub = cant * precio;
      const desc = this.escapeHtml(it.descripcion || it.nombre_prod || 'Producto');
      const cod = this.escapeHtml(it.codigo_barra || `PRD-${it.id_producto || idx+1}`);
      const tipoImp = Number(it.id_tipo_impuesto) || 1;

      let subExenta = '-';
      let subIva5 = '-';
      let subIva10 = '-';

      if (tipoImp === 1) {
        totalIva10 += sub;
        subIva10 = this.formatoGsSinTexto(sub);
      } else if (tipoImp === 2) {
        totalIva5 += sub;
        subIva5 = this.formatoGsSinTexto(sub);
      } else {
        totalExenta += sub;
        subExenta = this.formatoGsSinTexto(sub);
      }

      filasItemsHtml += `
        <tr>
          <td style="text-align: center; padding: 6px 4px; border: 1px solid #cbd5e1; font-size: 11px;">${cant}</td>
          <td style="padding: 6px 6px; border: 1px solid #cbd5e1; font-size: 11px; font-family: monospace;">${cod}</td>
          <td style="padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11.5px; font-weight: 500;">${desc}</td>
          <td style="text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${this.formatoGsSinTexto(precio)}</td>
          <td style="text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${subExenta}</td>
          <td style="text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px;">${subIva5}</td>
          <td style="text-align: right; padding: 6px 8px; border: 1px solid #cbd5e1; font-size: 11px; font-weight: 600;">${subIva10}</td>
        </tr>
      `;
    });

    // Rellenar con filas vacías para que la tabla conserve un formato elegante uniforme
    const filasFaltantes = Math.max(0, 7 - det.length);
    for (let i = 0; i < filasFaltantes; i++) {
      filasItemsHtml += `
        <tr>
          <td style="border: 1px solid #cbd5e1; padding: 10px;">&nbsp;</td>
          <td style="border: 1px solid #cbd5e1;">&nbsp;</td>
          <td style="border: 1px solid #cbd5e1;">&nbsp;</td>
          <td style="border: 1px solid #cbd5e1;">&nbsp;</td>
          <td style="border: 1px solid #cbd5e1;">&nbsp;</td>
          <td style="border: 1px solid #cbd5e1;">&nbsp;</td>
          <td style="border: 1px solid #cbd5e1;">&nbsp;</td>
        </tr>
      `;
    }

    const totalVenta = Number(cab.total_venta) || Number(cab.total) || (totalIva10 + totalIva5 + totalExenta);
    const liqIva10 = Math.round(totalIva10 / 11);
    const liqIva5 = Math.round(totalIva5 / 21);
    const liqTotal = liqIva10 + liqIva5;
    const totalEnLetras = this.numeroALetras(totalVenta);

    return `
      <div class="factura-a4-impresion" style="font-family: Arial, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.35;">
        <!-- CABECERA DE LA FACTURA A4 -->
        <div style="display: flex; justify-content: space-between; gap: 20px; margin-bottom: 12px;">
          <!-- Bloque Empresa -->
          <div style="flex: 1.2; border: 1.5px solid #0f172a; border-radius: 8px; padding: 12px;">
            <div style="font-size: 20px; font-weight: 900; letter-spacing: -0.5px; color: #0d6efd; margin-bottom: 2px;">
              ${this.escapeHtml(this.empresa.nombre)}
            </div>
            <div style="font-size: 11px; font-weight: bold; color: #334155; margin-bottom: 4px;">
              ${this.escapeHtml(this.empresa.razonSocial)}
            </div>
            <div style="font-size: 9.5px; color: #64748b; line-height: 1.2; margin-bottom: 6px;">
              ${this.escapeHtml(this.empresa.actividad)}
            </div>
            <div style="font-size: 10.5px; color: #1e293b;">
              <strong>Casa Central:</strong> ${this.escapeHtml(this.empresa.direccion)}<br>
              <strong>Teléfono:</strong> ${this.empresa.telefono}<br>
              <strong>Sucursal Emisora:</strong> ${this.escapeHtml(sucNom)}
            </div>
          </div>

          <!-- Bloque Tributario Oficial -->
          <div style="flex: 0.9; border: 2px solid #0f172a; border-radius: 8px; padding: 12px; text-align: center; background: #f8fafc;">
            <div style="font-size: 11px; font-weight: 700; color: #475569;">TIMBRADO N°: <strong>${this.empresa.timbrado}</strong></div>
            <div style="font-size: 9.5px; color: #64748b;">Válido: ${this.empresa.vigenciaTimbradoDesde} hasta ${this.empresa.vigenciaTimbradoHasta}</div>
            <div style="font-size: 13px; font-weight: 800; color: #0f172a; margin: 4px 0;">RUC: ${this.empresa.ruc}</div>
            <div style="background: #0f172a; color: #fff; font-weight: 800; font-size: 14px; padding: 4px; border-radius: 4px; margin: 5px 0; letter-spacing: 1px;">
              FACTURA
            </div>
            <div style="font-size: 16px; font-weight: 900; color: #dc2626; font-family: monospace;">
              N° ${this.escapeHtml(nroFac)}
            </div>
          </div>
        </div>

        <!-- DATOS DEL CLIENTE Y CONDICIONES -->
        <div style="border: 1.5px solid #0f172a; border-radius: 8px; padding: 10px 14px; margin-bottom: 12px; background: #ffffff;">
          <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <div style="flex: 2;"><strong>FECHA DE EMISIÓN:</strong> ${this.escapeHtml(fecha)}</div>
            <div style="flex: 1.5; text-align: right;">
              <strong>CONDICIÓN DE VENTA:</strong> 
              <span style="display: inline-block; padding: 2px 8px; background: ${esCredito ? '#fef3c7' : '#e0f2fe'}; border-radius: 4px; font-weight: bold; border: 1px solid #cbd5e1;">
                ${esCredito ? 'CRÉDITO' : 'CONTADO'}
              </span>
            </div>
          </div>
          <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
            <div style="flex: 2;"><strong>NOMBRE O RAZÓN SOCIAL:</strong> ${this.escapeHtml(cliNom)}</div>
            <div style="flex: 1.5; text-align: right;"><strong>R.U.C. / C.I. N°:</strong> ${this.escapeHtml(cliDoc)}</div>
          </div>
          <div style="display: flex; justify-content: space-between;">
            <div style="flex: 2;"><strong>DIRECCIÓN:</strong> ${this.escapeHtml(cliDir)}</div>
            <div style="flex: 1.5; text-align: right;"><strong>TELÉFONO:</strong> ${this.escapeHtml(cliTel)}</div>
          </div>
        </div>

        <!-- TABLA PRINCIPAL DE PRODUCTOS Y VALORES DE VENTAS -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
          <thead>
            <tr style="background-color: #0f172a; color: #ffffff;">
              <th rowspan="2" style="width: 6%; text-align: center; padding: 6px 4px; font-size: 10.5px; border: 1px solid #0f172a;">CANT.</th>
              <th rowspan="2" style="width: 14%; text-align: center; padding: 6px 4px; font-size: 10.5px; border: 1px solid #0f172a;">CÓDIGO</th>
              <th rowspan="2" style="width: 36%; text-align: left; padding: 6px 8px; font-size: 10.5px; border: 1px solid #0f172a;">DESCRIPCIÓN DE LA MERCADERÍA</th>
              <th rowspan="2" style="width: 14%; text-align: right; padding: 6px 8px; font-size: 10.5px; border: 1px solid #0f172a;">PRECIO UNIT.</th>
              <th colspan="3" style="width: 30%; text-align: center; padding: 4px; font-size: 10px; border: 1px solid #0f172a;">VALOR DE VENTAS</th>
            </tr>
            <tr style="background-color: #1e293b; color: #ffffff;">
              <th style="width: 10%; text-align: right; padding: 4px 6px; font-size: 10px; border: 1px solid #1e293b;">EXENTAS</th>
              <th style="width: 10%; text-align: right; padding: 4px 6px; font-size: 10px; border: 1px solid #1e293b;">5%</th>
              <th style="width: 10%; text-align: right; padding: 4px 6px; font-size: 10px; border: 1px solid #1e293b;">10%</th>
            </tr>
          </thead>
          <tbody>
            ${filasItemsHtml}
          </tbody>
          <tfoot>
            <tr style="background: #f8fafc; font-weight: bold;">
              <td colspan="4" style="text-align: right; padding: 6px 8px; border: 1.5px solid #0f172a; font-size: 11px;">SUBTOTALES:</td>
              <td style="text-align: right; padding: 6px 8px; border: 1.5px solid #0f172a; font-size: 11px;">${this.formatoGsSinTexto(totalExenta)}</td>
              <td style="text-align: right; padding: 6px 8px; border: 1.5px solid #0f172a; font-size: 11px;">${this.formatoGsSinTexto(totalIva5)}</td>
              <td style="text-align: right; padding: 6px 8px; border: 1.5px solid #0f172a; font-size: 11px;">${this.formatoGsSinTexto(totalIva10)}</td>
            </tr>
          </tfoot>
        </table>

        <!-- TOTAL EN LETRAS Y NÚMEROS -->
        <div style="border: 1.5px solid #0f172a; border-radius: 6px; padding: 8px 12px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; background: #ffffff;">
          <div style="font-size: 11px; flex: 1; padding-right: 15px;">
            <strong>VALOR TOTAL EN LETRAS:</strong><br>
            <span style="font-style: italic; color: #1e293b; font-weight: 600;">${totalEnLetras}</span>
          </div>
          <div style="text-align: right; border-left: 1.5px solid #cbd5e1; padding-left: 15px;">
            <div style="font-size: 11px; font-weight: bold; color: #64748b;">TOTAL A PAGAR:</div>
            <div style="font-size: 18px; font-weight: 900; color: #0d6efd;">${this.formatoGs(totalVenta)}</div>
          </div>
        </div>

        <!-- LIQUIDACIÓN DEL IVA Y FIRMAS -->
        <div style="display: flex; gap: 15px;">
          <!-- Liquidación IVA -->
          <div style="flex: 1.2; border: 1.5px solid #0f172a; border-radius: 6px; padding: 6px 10px; font-size: 10.5px;">
            <div style="font-weight: bold; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; margin-bottom: 4px; text-align: center;">
              LIQUIDACIÓN DEL IMPUESTO AL VALOR AGREGADO (I.V.A.)
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
              <span>IVA 5%: <strong>${this.formatoGs(liqIva5)}</strong></span>
              <span>IVA 10%: <strong>${this.formatoGs(liqIva10)}</strong></span>
            </div>
            <div style="display: flex; justify-content: space-between; font-weight: bold; border-top: 1px dashed #cbd5e1; padding-top: 2px; color: #0f172a;">
              <span>TOTAL IVA:</span>
              <span>${this.formatoGs(liqTotal)}</span>
            </div>
          </div>

          <!-- Firmas -->
          <div style="flex: 1.4; border: 1.5px solid #0f172a; border-radius: 6px; padding: 6px 12px; display: flex; justify-content: space-around; text-align: center; font-size: 9.5px; align-items: flex-end;">
            <div style="width: 45%;">
              <div style="border-top: 1px solid #000; padding-top: 4px;">Firma del Receptor / Cliente</div>
            </div>
            <div style="width: 45%;">
              <div style="border-top: 1px solid #000; padding-top: 4px;">Firma Autorizada Emisor</div>
            </div>
          </div>
        </div>

        <div style="text-align: center; margin-top: 8px; font-size: 9px; color: #64748b;">
          Original: Cliente • Duplicado: Archivo Tributario • Triplicado: Contabilidad
        </div>
      </div>
    `;
  },

  // -------------------------------------------------------------
  // 3. PLANTILLA: RECIBO OFICIAL DE COBRO (TICKET Y A4)
  // -------------------------------------------------------------
  plantillaTicketRecibo(r) {
    const nro = r.nro_recibo || 'REC-000000';
    const fecha = r.fecha || new Date().toLocaleString();
    const cliente = r.cliente || 'CLIENTE';
    const cedula = r.cedula || '000000';
    const nroFac = r.nro_factura || '-';
    const cajero = r.cajero || 'Cajero Central';
    const sucursal = r.sucursal || '1';
    const monto = Number(r.monto_pagado || r.monto) || 0;
    const saldoAnt = Number(r.saldo_anterior) || 0;
    const saldoRes = Number(r.saldo_restante) || 0;
    const metodo = r.metodo_pago || 'EFECTIVO';
    const ref = r.referencia ? `(${this.escapeHtml(r.referencia)})` : '';

    return `
      <div style="font-family: 'Segoe UI', monospace, sans-serif; font-size: 12px; line-height: 1.3; color: #111;">
        <div style="text-align: center; border-bottom: 1px dashed #444; padding-bottom: 8px; margin-bottom: 8px;">
          <div style="font-size: 15px; font-weight: 800;">${this.escapeHtml(this.empresa.nombre)}</div>
          <div style="font-size: 10px; color: #555;">RUC: ${this.empresa.ruc}</div>
          <div style="font-size: 10px; color: #555;">Sucursal #${sucursal}</div>
          <div style="font-size: 12px; font-weight: 800; background: #000; color: #fff; padding: 2px 6px; border-radius: 3px; display: inline-block; margin-top: 4px;">
            RECIBO OFICIAL DE COBRO
          </div>
          <div style="font-size: 13px; font-weight: bold; color: #0d6efd; margin-top: 3px;">${this.escapeHtml(nro)}</div>
        </div>

        <div style="font-size: 11px; margin-bottom: 8px; border-bottom: 1px dashed #444; padding-bottom: 6px;">
          <div style="display:flex; justify-content:space-between;"><span>Fecha:</span><strong>${this.escapeHtml(fecha)}</strong></div>
          <div style="display:flex; justify-content:space-between;"><span>Cajero:</span><span>${this.escapeHtml(cajero)}</span></div>
          <div style="display:flex; justify-content:space-between;"><span>Cliente:</span><strong>${this.escapeHtml(cliente)}</strong></div>
          <div style="display:flex; justify-content:space-between;"><span>C.I./RUC:</span><span>${this.escapeHtml(cedula)}</span></div>
          <div style="display:flex; justify-content:space-between;"><span>Factura Afectada:</span><strong>${this.escapeHtml(nroFac)}</strong></div>
          <div style="display:flex; justify-content:space-between;"><span>Forma de Pago:</span><span>${this.escapeHtml(metodo)} ${ref}</span></div>
        </div>

        <div style="background: #f1f5f9; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 8px; text-align: center; margin-bottom: 8px;">
          <div style="font-size: 11px; color: #475569; font-weight: 600;">IMPORTE ABONADO:</div>
          <div style="font-size: 17px; font-weight: 900; color: #15803d;">${this.formatoGs(monto)}</div>
        </div>

        <div style="font-size: 11px; display:flex; justify-content:space-between; margin-bottom: 14px; border-bottom: 1px dashed #444; padding-bottom: 6px;">
          <span>Saldo Anterior: ${this.formatoGs(saldoAnt)}</span>
          <strong style="color: #dc2626;">Saldo Pendiente: ${this.formatoGs(saldoRes)}</strong>
        </div>

        <div style="display: flex; justify-content: space-between; text-align: center; font-size: 9.5px; margin-top: 25px;">
          <div style="width: 45%; border-top: 1px solid #000; padding-top: 2px;">Firma Cajero</div>
          <div style="width: 45%; border-top: 1px solid #000; padding-top: 2px;">Firma Cliente</div>
        </div>
      </div>
    `;
  },

  plantillaReciboA4(r) {
    const nro = r.nro_recibo || 'REC-000000';
    const fecha = r.fecha || new Date().toLocaleString();
    const cliente = r.cliente || 'CLIENTE';
    const cedula = r.cedula || '000000';
    const nroFac = r.nro_factura || '-';
    const cajero = r.cajero || 'Cajero Central';
    const sucursal = r.sucursal || '1';
    const monto = Number(r.monto_pagado || r.monto) || 0;
    const saldoAnt = Number(r.saldo_anterior) || 0;
    const saldoRes = Number(r.saldo_restante) || 0;
    const metodo = r.metodo_pago || 'EFECTIVO';
    const montoLetras = this.numeroALetras(monto);

    return `
      <div style="font-family: Arial, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.4;">
        <div style="border: 2px solid #0f172a; border-radius: 8px; padding: 20px;">
          <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 14px;">
            <div>
              <h3 style="margin: 0; font-weight: 900; color: #0d6efd;">${this.escapeHtml(this.empresa.nombre)}</h3>
              <div style="font-size: 11px; color: #475569;">${this.escapeHtml(this.empresa.razonSocial)}</div>
              <div style="font-size: 11px; color: #64748b;">RUC: ${this.empresa.ruc} • Tel: ${this.empresa.telefono}</div>
              <div style="font-size: 11px; font-weight: bold; margin-top: 2px;">Sucursal #${sucursal}</div>
            </div>
            <div style="text-align: right; border: 1.5px solid #0f172a; border-radius: 6px; padding: 8px 14px; background: #f8fafc;">
              <div style="font-size: 13px; font-weight: 900; color: #0f172a;">RECIBO DE DINERO</div>
              <div style="font-size: 16px; font-weight: 900; color: #dc2626; font-family: monospace;">${this.escapeHtml(nro)}</div>
              <div style="font-size: 10px; color: #64748b;">Fecha: ${this.escapeHtml(fecha)}</div>
            </div>
          </div>

          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 14px; font-size: 12px;">
            <div style="margin-bottom: 6px;">
              Recibimos de: <strong style="font-size: 13px; color: #0f172a;">${this.escapeHtml(cliente)}</strong> 
              (C.I. / RUC: <strong>${this.escapeHtml(cedula)}</strong>)
            </div>
            <div style="margin-bottom: 6px;">
              La cantidad de: <strong style="color: #0d6efd; font-size: 13px;">${this.formatoGs(monto)}</strong>
              <div style="font-style: italic; color: #475569; font-size: 11px;">Son: ${montoLetras}</div>
            </div>
            <div style="margin-bottom: 4px;">
              En concepto de: <strong>Abono a Factura N° ${this.escapeHtml(nroFac)}</strong>
            </div>
            <div>
              Medio de Cobro: <strong>${this.escapeHtml(metodo)}</strong> | Operador / Cajero: <strong>${this.escapeHtml(cajero)}</strong>
            </div>
          </div>

          <div style="display: flex; justify-content: space-between; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 6px; padding: 10px 16px; margin-bottom: 25px;">
            <div>Saldo de Deuda Anterior: <strong>${this.formatoGs(saldoAnt)}</strong></div>
            <div>Monto Abonado: <strong style="color: #15803d;">${this.formatoGs(monto)}</strong></div>
            <div>Saldo Restante: <strong style="color: #dc2626;">${this.formatoGs(saldoRes)}</strong></div>
          </div>

          <div style="display: flex; justify-content: space-around; text-align: center; font-size: 11px; margin-top: 40px; padding-top: 10px;">
            <div style="width: 35%;">
              <div style="border-top: 1px solid #0f172a; padding-top: 5px;">Firma del Cajero / Cobrador</div>
            </div>
            <div style="width: 35%;">
              <div style="border-top: 1px solid #0f172a; padding-top: 5px;">Firma del Cliente</div>
            </div>
          </div>
        </div>
      </div>
    `;
  },

  // -------------------------------------------------------------
  // 4. PLANTILLA: CIERRE Y ARQUEO DE CAJA
  // -------------------------------------------------------------
  plantillaTicketCierreCaja(datos) {
    const c = datos.caja || datos;
    const id = c.id_apertura_cierre_caja || '';
    const suc = c.sucursal_nombre || `Sucursal #${c.id_sucursal || 1}`;
    const cajero = c.cajero_nombre || c.nombre_usuario || 'Cajero';
    const apert = Number(c.monto_apertura) || 0;
    const efec = Number(c.ingreso_efectivo) || 0;
    const transf = Number(c.ingreso_transferencia) || 0;
    const qr = Number(c.ingreso_qr) || 0;
    const tj = Number(c.ingreso_tarjeta) || 0;
    const gastos = Number(c.gastos) || 0;
    const cierreReal = Number(c.monto_cierre) || 0;
    const saldoEsperado = apert + efec - gastos;
    const dif = cierreReal - saldoEsperado;

    return `
      <div style="font-family: 'Segoe UI', monospace, sans-serif; font-size: 12px; line-height: 1.35; color: #111;">
        <div style="text-align: center; border-bottom: 1px dashed #444; padding-bottom: 8px; margin-bottom: 8px;">
          <div style="font-size: 15px; font-weight: 800;">${this.escapeHtml(this.empresa.nombre)}</div>
          <div style="font-size: 12px; font-weight: 800; background: #0f172a; color: #fff; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-top: 4px;">
            CIERRE Y ARQUEO DE CAJA #${id}
          </div>
          <div style="font-size: 11px; margin-top: 3px;">${this.escapeHtml(suc)} • ${this.escapeHtml(cajero)}</div>
        </div>

        <div style="font-size: 11px; border-bottom: 1px dashed #444; padding-bottom: 6px; margin-bottom: 8px;">
          <div>Apertura: ${c.fecha_apertura} ${c.hora_apertura || ''}</div>
          <div>Cierre: ${c.fecha_cierre || c.fecha_apertura} ${c.hora_cierre || ''}</div>
          <div>Estado: <strong>${c.estado || 'CERRADA'}</strong></div>
        </div>

        <div style="font-size: 11px; margin-bottom: 8px;">
          <div style="display:flex; justify-content:space-between;"><span>(+) Fondo Inicial de Apertura:</span><strong>${this.formatoGs(apert)}</strong></div>
          <div style="display:flex; justify-content:space-between;"><span>(+) Ventas / Cobros Efectivo:</span><strong>${this.formatoGs(efec)}</strong></div>
          <div style="display:flex; justify-content:space-between; color: #dc2626;"><span>(-) Gastos / Egresos de Caja:</span><strong>-${this.formatoGs(gastos)}</strong></div>
          <div style="border-top: 1px dashed #666; margin: 4px 0;"></div>
          <div style="display:flex; justify-content:space-between; font-weight: bold;"><span>(=) Saldo Efectivo Esperado:</span><span>${this.formatoGs(saldoEsperado)}</span></div>
          <div style="display:flex; justify-content:space-between; font-weight: 800; font-size: 13px; color: #0d6efd; margin-top: 2px;">
            <span>Efectivo Real Contado:</span><span>${this.formatoGs(cierreReal)}</span>
          </div>
          <div style="display:flex; justify-content:space-between; font-weight: bold; color: ${dif === 0 ? '#15803d' : (dif > 0 ? '#0284c7' : '#dc2626')};">
            <span>Diferencia Arqueo:</span><span>${dif >= 0 ? '+' : ''}${this.formatoGs(dif)}</span>
          </div>
        </div>

        <div style="border-top: 1px dashed #444; padding-top: 6px; font-size: 10.5px; margin-bottom: 14px;">
          <div style="font-weight: bold; margin-bottom: 2px;">OTROS INGRESOS DIGITALES (Bancos / POS):</div>
          <div style="display:flex; justify-content:space-between;"><span>• Transferencias:</span><span>${this.formatoGs(transf)}</span></div>
          <div style="display:flex; justify-content:space-between;"><span>• Cobros QR:</span><span>${this.formatoGs(qr)}</span></div>
          <div style="display:flex; justify-content:space-between;"><span>• Tarjetas:</span><span>${this.formatoGs(tj)}</span></div>
        </div>

        <div style="display: flex; justify-content: space-between; text-align: center; font-size: 9.5px; margin-top: 25px;">
          <div style="width: 45%; border-top: 1px solid #000; padding-top: 2px;">Firma Cajero</div>
          <div style="width: 45%; border-top: 1px solid #000; padding-top: 2px;">Firma Supervisor</div>
        </div>
      </div>
    `;
  },

  plantillaCierreCajaA4(datos) {
    const c = datos.caja || datos;
    const id = c.id_apertura_cierre_caja || '';
    const suc = c.sucursal_nombre || `Sucursal #${c.id_sucursal || 1}`;
    const cajero = c.cajero_nombre || c.nombre_usuario || 'Cajero';
    const apert = Number(c.monto_apertura) || 0;
    const efec = Number(c.ingreso_efectivo) || 0;
    const transf = Number(c.ingreso_transferencia) || 0;
    const qr = Number(c.ingreso_qr) || 0;
    const tj = Number(c.ingreso_tarjeta) || 0;
    const gastos = Number(c.gastos) || 0;
    const cierreReal = Number(c.monto_cierre) || 0;
    const saldoEsperado = apert + efec - gastos;
    const dif = cierreReal - saldoEsperado;

    return `
      <div style="font-family: Arial, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.4;">
        <div style="border: 2px solid #0f172a; border-radius: 8px; padding: 20px;">
          <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 14px;">
            <div>
              <h3 style="margin: 0; font-weight: 900; color: #0d6efd;">${this.escapeHtml(this.empresa.nombre)}</h3>
              <div style="font-size: 11px; color: #64748b;">CONTROL DE CAJA Y ARQUEO DE VALORES</div>
              <div style="font-size: 11px; font-weight: bold; margin-top: 2px;">${this.escapeHtml(suc)}</div>
            </div>
            <div style="text-align: right; border: 1.5px solid #0f172a; border-radius: 6px; padding: 8px 14px; background: #f8fafc;">
              <div style="font-size: 14px; font-weight: 900; color: #0f172a;">PLANILLA DE CIERRE #${id}</div>
              <div style="font-size: 11px; color: #64748b;">Apertura: ${c.fecha_apertura} ${c.hora_apertura || ''}</div>
              <div style="font-size: 11px; color: #64748b;">Cierre: ${c.fecha_cierre || c.fecha_apertura} ${c.hora_cierre || ''}</div>
              <div style="font-size: 11px; font-weight: bold; color: #15803d;">Cajero: ${this.escapeHtml(cajero)}</div>
            </div>
          </div>

          <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
            <thead>
              <tr style="background: #0f172a; color: #fff;">
                <th style="padding: 8px; text-align: left; font-size: 11px;">CONCEPTO DE MOVIMIENTO</th>
                <th style="padding: 8px; text-align: right; font-size: 11px;">IMPORTE (Gs.)</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding: 8px; border: 1px solid #cbd5e1;">Monto Fondo Fijo / Apertura</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold;">${this.formatoGs(apert)}</td>
              </tr>
              <tr>
                <td style="padding: 8px; border: 1px solid #cbd5e1;">(+) Ingresos de Ventas y Cobranzas en Efectivo</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold; color: #15803d;">+${this.formatoGs(efec)}</td>
              </tr>
              <tr>
                <td style="padding: 8px; border: 1px solid #cbd5e1;">(-) Gastos Operativos y Salidas de Caja</td>
                <td style="padding: 8px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold; color: #dc2626;">-${this.formatoGs(gastos)}</td>
              </tr>
              <tr style="background: #f8fafc;">
                <td style="padding: 8px; border: 1.5px solid #0f172a; font-weight: bold;">SALDO TEÓRICO ESPERADO EN CAJÓN:</td>
                <td style="padding: 8px; border: 1.5px solid #0f172a; text-align: right; font-weight: bold; font-size: 13px;">${this.formatoGs(saldoEsperado)}</td>
              </tr>
              <tr style="background: #f1f5f9;">
                <td style="padding: 8px; border: 1.5px solid #0f172a; font-weight: 800; font-size: 13px; color: #0d6efd;">MONTO REAL CONTADO EN ARQUEO (CIERRE):</td>
                <td style="padding: 8px; border: 1.5px solid #0f172a; text-align: right; font-weight: 900; font-size: 15px; color: #0d6efd;">${this.formatoGs(cierreReal)}</td>
              </tr>
              <tr>
                <td style="padding: 8px; border: 1.5px solid #0f172a; font-weight: bold;">Diferencia de Arqueo:</td>
                <td style="padding: 8px; border: 1.5px solid #0f172a; text-align: right; font-weight: 800; font-size: 13px; color: ${dif === 0 ? '#15803d' : (dif > 0 ? '#0284c7' : '#dc2626')};">
                  ${dif >= 0 ? '+' : ''}${this.formatoGs(dif)} ${dif === 0 ? '(Exacto)' : (dif > 0 ? '(Sobrante)' : '(Faltante)')}
                </td>
              </tr>
            </tbody>
          </table>

          <div style="border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; margin-bottom: 25px; background: #fafafa;">
            <div style="font-weight: bold; font-size: 11px; margin-bottom: 4px;">RESUMEN DE COBROS DIGITALES (Cuentas Bancarias / POS):</div>
            <div style="display: flex; justify-content: space-between; font-size: 11.5px;">
              <span>Transferencias Bancarias: <strong>${this.formatoGs(transf)}</strong></span>
              <span>Cobros QR: <strong>${this.formatoGs(qr)}</strong></span>
              <span>Tarjetas Débito/Crédito: <strong>${this.formatoGs(tj)}</strong></span>
            </div>
          </div>

          <div style="display: flex; justify-content: space-around; text-align: center; font-size: 11px; margin-top: 40px; padding-top: 10px;">
            <div style="width: 35%;">
              <div style="border-top: 1px solid #0f172a; padding-top: 5px;">Firma del Cajero</div>
            </div>
            <div style="width: 35%;">
              <div style="border-top: 1px solid #0f172a; padding-top: 5px;">Firma del Supervisor / Auditor</div>
            </div>
          </div>
        </div>
      </div>
    `;
  },

  // -------------------------------------------------------------
  // 5. PLANTILLA: COMPROBANTE DE COMPRA / RECEPCIÓN A4
  // -------------------------------------------------------------
  plantillaCompraA4(datos) {
    const c = datos.cabecera || datos;
    const det = datos.detalles || [];
    const provNom = c.razon_social || c.nombre_apellido_prov || 'PROVEEDOR';
    const sucNom = c.sucursal_nombre || `Sucursal #${c.id_sucursal || 1}`;

    return `
      <div style="font-family: Arial, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.4;">
        <div style="border: 2px solid #0f172a; border-radius: 8px; padding: 20px;">
          <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 14px;">
            <div>
              <h3 style="margin: 0; font-weight: 900; color: #0d6efd;">${this.escapeHtml(this.empresa.nombre)}</h3>
              <div style="font-size: 11px; color: #64748b;">COMPROBANTE DE RECEPCIÓN DE COMPRA Y STOCK</div>
              <div style="font-size: 11px; font-weight: bold; margin-top: 2px;">Sucursal Receptora: ${this.escapeHtml(sucNom)}</div>
            </div>
            <div style="text-align: right; border: 1.5px solid #0f172a; border-radius: 6px; padding: 8px 14px; background: #f8fafc;">
              <div style="font-size: 13px; font-weight: 900; color: #0f172a;">FACTURA PROVEEDOR</div>
              <div style="font-size: 15px; font-weight: 900; color: #dc2626; font-family: monospace;">N° ${this.escapeHtml(c.nro_factura || '')}</div>
              <div style="font-size: 10px; color: #64748b;">Timbrado: ${this.escapeHtml(c.nro_timbrado || 'S/T')} | Fecha: ${c.fecha || ''}</div>
            </div>
          </div>

          <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; margin-bottom: 14px; font-size: 11.5px;">
            <div><strong>PROVEEDOR:</strong> ${this.escapeHtml(provNom)} | <strong>RUC:</strong> ${this.escapeHtml(c.ruc_proveedor || '-')}</div>
            <div><strong>ESTADO:</strong> ${this.escapeHtml(c.estado || 'ACTIVO')} | <strong>FORMA DE PAGO:</strong> ${this.escapeHtml(c.metodo_pago || 'EFECTIVO')}</div>
          </div>

          <table style="width: 100%; border-collapse: collapse; margin-bottom: 14px;">
            <thead>
              <tr style="background: #0f172a; color: #fff;">
                <th style="padding: 6px; text-align: left; font-size: 11px;">CÓDIGO</th>
                <th style="padding: 6px; text-align: left; font-size: 11px;">DESCRIPCIÓN DEL PRODUCTO</th>
                <th style="padding: 6px; text-align: center; font-size: 11px;">CANTIDAD INGRESADA</th>
                <th style="padding: 6px; text-align: right; font-size: 11px;">COSTO UNIT.</th>
                <th style="padding: 6px; text-align: right; font-size: 11px;">SUBTOTAL</th>
              </tr>
            </thead>
            <tbody>
              ${det.map(d => `
                <tr>
                  <td style="padding: 6px; border: 1px solid #cbd5e1; font-family: monospace;">${this.escapeHtml(d.codigo_barra || '')}</td>
                  <td style="padding: 6px; border: 1px solid #cbd5e1;">${this.escapeHtml(d.nombre_prod || '')}</td>
                  <td style="padding: 6px; border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">${d.cantidad}</td>
                  <td style="padding: 6px; border: 1px solid #cbd5e1; text-align: right;">${this.formatoGs(d.costo_unitario)}</td>
                  <td style="padding: 6px; border: 1px solid #cbd5e1; text-align: right; font-weight: bold;">${this.formatoGs(d.cantidad * d.costo_unitario)}</td>
                </tr>
              `).join('')}
            </tbody>
            <tfoot>
              <tr style="background: #f1f5f9; font-weight: 800;">
                <td colspan="4" style="padding: 8px; text-align: right; border: 1.5px solid #0f172a; font-size: 12px;">TOTAL COMPRA:</td>
                <td style="padding: 8px; text-align: right; border: 1.5px solid #0f172a; font-size: 13px; color: #0d6efd;">${this.formatoGs(c.total)}</td>
              </tr>
            </tfoot>
          </table>

          <div style="display: flex; justify-content: space-around; text-align: center; font-size: 11px; margin-top: 35px;">
            <div style="width: 35%;">
              <div style="border-top: 1px solid #0f172a; padding-top: 5px;">Recibido en Depósito por</div>
            </div>
            <div style="width: 35%;">
              <div style="border-top: 1px solid #0f172a; padding-top: 5px;">Firma del Proveedor / Transportista</div>
            </div>
          </div>
        </div>
      </div>
    `;
  },

  // -------------------------------------------------------------
  // EJECUCIÓN NATIVA DE IMPRESIÓN (VENTANA O IFRAME LIMPIO)
  // -------------------------------------------------------------
  ejecutarImpresion() {
    const contenedor = document.getElementById('hojaImpresionPreview');
    if (!contenedor) return;

    const contenidoHtml = contenedor.innerHTML;
    const esTicket = this.estadoActual.formato === 'ticket';

    const win = window.open('', '_blank', esTicket ? 'width=380,height=600' : 'width=900,height=750');
    if (!win) {
      alert('Por favor habilite las ventanas emergentes (popups) para imprimir.');
      return;
    }

    win.document.write(`
      <!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="utf-8">
        <title>Impresión | Sistema Mayorista</title>
        <style>
          @page {
            size: ${esTicket ? '80mm auto' : 'A4 portrait'};
            margin: ${esTicket ? '0mm' : '10mm'};
          }
          *, *::before, *::after {
            box-sizing: border-box;
          }
          body {
            margin: 0;
            padding: ${esTicket ? '10px 8px' : '0'};
            background: #fff;
            color: #000;
            font-family: ${esTicket ? "'Segoe UI', Tahoma, monospace, sans-serif" : "Arial, sans-serif"};
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          @media print {
            body { margin: 0; padding: ${esTicket ? '4px' : '0'}; }
            .no-print { display: none !important; }
          }
        </style>
      </head>
      <body>
        ${contenidoHtml}
        <script>
          window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
              window.print();
            }, 300);
          });
        </script>
      </body>
      </html>
    `);
    win.document.close();
  }
};

// Exportar globalmente
window.ImpresorMayorista = ImpresorMayorista;

