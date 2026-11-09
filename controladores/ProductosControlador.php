<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getDB();
$accion = $_POST['accion'] ?? $_GET['accion'] ?? 'listar';

try {
    if ($accion === 'exportar_excel') {
        $buscar = trim($_GET['buscar'] ?? '');
        $categoria = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;
        $marca = !empty($_GET['marca']) ? (int)$_GET['marca'] : null;

        $sqlWhere = " WHERE 1=1";
        $params = [];

        if (!empty($buscar)) {
            $sqlWhere .= " AND (p.nombre_prod LIKE :b1 OR p.descripcion LIKE :b2 OR p.codigo_barra LIKE :b3 OR m.descripcion LIKE :b4)";
            $term = "%$buscar%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
            $params[':b4'] = $term;
        }

        if ($categoria) {
            $sqlWhere .= " AND p.id_categoria = :cat";
            $params[':cat'] = $categoria;
        }

        if ($marca) {
            $sqlWhere .= " AND p.id_marcas = :mar";
            $params[':mar'] = $marca;
        }

        $sql = "SELECT p.id_producto, p.codigo_barra, p.nombre_prod, p.descripcion, 
                       m.descripcion AS marca_nombre, c.descripcion AS categoria_nombre,
                       p.precio_compra, p.precio_venta, 
                       (p.precio_venta - p.precio_compra) AS margen_ganancia,
                       p.stock_sucursal1, p.stock_sucursal2, p.stock_actual, p.estado
                FROM productos p
                LEFT JOIN marcas m ON p.id_marcas = m.id_marcas
                LEFT JOIN categorias c ON p.id_categoria = c.id_categorias
                $sqlWhere
                ORDER BY p.id_producto DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filename = "catalogo_productos_" . date('Ymd_His') . ".xls";
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        ?>
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
          <meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8">
          <style>
            th { background-color: #0d6efd; color: #ffffff; font-weight: bold; border: 1px solid #000; padding: 6px; }
            td { border: 1px solid #dcdcdc; padding: 5px; }
            .num { mso-number-format: '\#\,\#\#0'; text-align: right; }
            .txt { mso-number-format: '\@'; text-align: center; }
            .cnt { text-align: center; }
          </style>
        </head>
        <body>
          <h3>CATÁLOGO DE PRODUCTOS & PRECIOS - SISTEMA MAYORISTA</h3>
          <p>Generado el: <?= date('d/m/Y H:i:s') ?> | Total registros: <?= count($productos) ?></p>
          <table border="1">
            <thead>
              <tr>
                <th>ID</th>
                <th>CÓDIGO DE BARRAS</th>
                <th>NOMBRE DEL PRODUCTO</th>
                <th>DESCRIPCIÓN</th>
                <th>MARCA</th>
                <th>CATEGORÍA</th>
                <th>COSTO COMPRA (Gs.)</th>
                <th>PRECIO VENTA (Gs.)</th>
                <th>MARGEN ESTIMADO (Gs.)</th>
                <th>STOCK SUC. 1</th>
                <th>STOCK SUC. 2</th>
                <th>STOCK TOTAL</th>
                <th>ESTADO</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($productos as $p): ?>
              <tr>
                <td class="cnt"><?= $p['id_producto'] ?></td>
                <td class="txt"><?= htmlspecialchars($p['codigo_barra'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['nombre_prod']) ?></td>
                <td><?= htmlspecialchars($p['descripcion'] ?? '') ?></td>
                <td><?= htmlspecialchars($p['marca_nombre'] ?? 'Sin Marca') ?></td>
                <td><?= htmlspecialchars($p['categoria_nombre'] ?? 'Sin Categoría') ?></td>
                <td class="num"><?= (int)$p['precio_compra'] ?></td>
                <td class="num" style="font-weight: bold;"><?= (int)$p['precio_venta'] ?></td>
                <td class="num"><?= (int)$p['margen_ganancia'] ?></td>
                <td class="cnt"><?= (int)$p['stock_sucursal1'] ?></td>
                <td class="cnt"><?= (int)$p['stock_sucursal2'] ?></td>
                <td class="cnt" style="font-weight: bold;"><?= (int)$p['stock_actual'] ?></td>
                <td class="cnt"><?= htmlspecialchars($p['estado']) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </body>
        </html>
        <?php
        exit;
    }

    if ($accion === 'listar') {
        $buscar = trim($_GET['buscar'] ?? '');
        $categoria = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;
        $marca = !empty($_GET['marca']) ? (int)$_GET['marca'] : null;
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $limite = 25;
        $offset = ($pagina - 1) * $limite;

        $sqlWhere = " WHERE 1=1";
        $params = [];

        if (!empty($buscar)) {
            $sqlWhere .= " AND (p.nombre_prod LIKE :b1 OR p.descripcion LIKE :b2 OR p.codigo_barra LIKE :b3 OR m.descripcion LIKE :b4)";
            $term = "%$buscar%";
            $params[':b1'] = $term;
            $params[':b2'] = $term;
            $params[':b3'] = $term;
            $params[':b4'] = $term;
        }

        if ($categoria) {
            $sqlWhere .= " AND p.id_categoria = :cat";
            $params[':cat'] = $categoria;
        }

        if ($marca) {
            $sqlWhere .= " AND p.id_marcas = :mar";
            $params[':mar'] = $marca;
        }

        // Conteo total
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM productos p LEFT JOIN marcas m ON p.id_marcas = m.id_marcas" . $sqlWhere);
        $stmtCount->execute($params);
        $totalRegistros = (int)$stmtCount->fetchColumn();

        // Consulta de datos
        $sql = "SELECT p.id_producto, p.nombre_prod, p.descripcion, p.precio_compra, p.precio_venta, 
                       p.stock_actual, p.stock_sucursal1, p.stock_sucursal2, p.codigo_barra, p.estado, 
                       p.tipo_producto, p.id_marcas, p.id_categoria, p.id_tipo_impuesto,
                       m.descripcion AS marca_nombre, c.descripcion AS categoria_nombre
                FROM productos p
                LEFT JOIN marcas m ON p.id_marcas = m.id_marcas
                LEFT JOIN categorias c ON p.id_categoria = c.id_categorias
                $sqlWhere
                ORDER BY p.id_producto DESC
                LIMIT $limite OFFSET $offset";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => $data,
            'total' => $totalRegistros,
            'pagina' => $pagina,
            'total_paginas' => ceil($totalRegistros / $limite)
        ]);
        exit;
    }

    // Buscador interactivo para Facturación en vivo (compatible con lector de código de barras)
    // REGLA CRÍTICA: En Facturación NUNCA aparecen productos que tengan 0 de stock en la sucursal seleccionada.
    if ($accion === 'buscar_para_factura' || $accion === 'buscar_para_ajuste' || $accion === 'catalogo_factura') {
        $q = trim($_GET['q'] ?? '');
        $sucursal = (int)($_GET['sucursal'] ?? 1);
        $colStock = ($sucursal == 2) ? 'p.stock_sucursal2' : 'p.stock_sucursal1';
        $soloConStock = ($accion !== 'buscar_para_ajuste');
        $idCategoria = !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;

        $condStock = $soloConStock ? "AND COALESCE($colStock, 0) > 0" : "";
        $condCat = $idCategoria ? "AND p.id_categoria = $idCategoria" : "";
        $limite = ($accion === 'catalogo_factura') ? 60 : 30;

        if (empty($q)) {
            $sql = "SELECT p.id_producto, p.nombre_prod, p.descripcion, p.precio_compra, p.precio_venta, 
                           p.stock_actual, p.stock_sucursal1, p.stock_sucursal2, p.codigo_barra, 
                           p.id_tipo_impuesto, m.descripcion AS marca_nombre, c.descripcion AS categoria_nombre
                    FROM productos p
                    LEFT JOIN marcas m ON p.id_marcas = m.id_marcas
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categorias
                    WHERE p.estado = 'ACTIVO'
                      $condStock
                      $condCat
                    ORDER BY p.id_producto DESC
                    LIMIT $limite";
            $stmt = $pdo->query($sql);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Si coincide exactamente con código de barra, priorizar
            $sql = "SELECT p.id_producto, p.nombre_prod, p.descripcion, p.precio_compra, p.precio_venta, 
                           p.stock_actual, p.stock_sucursal1, p.stock_sucursal2, p.codigo_barra, 
                           p.id_tipo_impuesto, m.descripcion AS marca_nombre, c.descripcion AS categoria_nombre
                    FROM productos p
                    LEFT JOIN marcas m ON p.id_marcas = m.id_marcas
                    LEFT JOIN categorias c ON p.id_categoria = c.id_categorias
                    WHERE p.estado = 'ACTIVO'
                      $condStock
                      $condCat
                      AND (p.codigo_barra = :exactBarra OR p.nombre_prod LIKE :q1 OR p.descripcion LIKE :q2 OR p.codigo_barra LIKE :q3 OR m.descripcion LIKE :q4)
                    ORDER BY (p.codigo_barra = :exactBarra2) DESC, p.nombre_prod ASC
                    LIMIT $limite";
            $stmt = $pdo->prepare($sql);
            $term = "%$q%";
            $stmt->execute([
                ':exactBarra' => $q,
                ':exactBarra2' => $q,
                ':q1' => $term,
                ':q2' => $term,
                ':q3' => $term,
                ':q4' => $term
            ]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Agregar campo stock disponible según sucursal y asegurar precio_venta > 0 para factura
        $resultado = [];
        foreach ($data as $item) {
            $stockDisp = ($sucursal == 2) ? (int)$item['stock_sucursal2'] : (int)$item['stock_sucursal1'];
            $item['stock_disponible'] = $stockDisp;
            $pv = (int)($item['precio_venta'] ?? 0);
            $pc = (int)($item['precio_compra'] ?? 0);
            if ($pv <= 0) {
                $pv = ($pc > 0) ? (int)(round(($pc * 1.35) / 1000) * 1000) : 35000;
                $item['precio_venta'] = $pv;
            }
            if (!$soloConStock || $stockDisp > 0) {
                $resultado[] = $item;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $resultado]);
        exit;
    }

    if ($accion === 'listar_referenciales_aux') {
        $marcas = $pdo->query("SELECT id_marcas, descripcion FROM marcas WHERE estado = 'ACTIVO' ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);
        $categorias = $pdo->query("SELECT id_categorias, descripcion FROM categorias WHERE estado = 'ACTIVO' ORDER BY descripcion ASC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'marcas' => $marcas, 'categorias' => $categorias]);
        exit;
    }

    if ($accion === 'guardar') {
        $id = !empty($_POST['id_producto']) ? (int)$_POST['id_producto'] : null;
        $nombre = trim($_POST['nombre_prod'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $id_marcas = !empty($_POST['id_marcas']) ? (int)$_POST['id_marcas'] : null;
        $id_categoria = !empty($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : null;
        $tipo_producto = $_POST['tipo_producto'] ?? 'EQUIPO';
        $precio_compra = (int)($_POST['precio_compra'] ?? 0);
        $precio_venta = (int)($_POST['precio_venta'] ?? 0);
        $stock_sucursal1 = (int)($_POST['stock_sucursal1'] ?? 0);
        $stock_sucursal2 = (int)($_POST['stock_sucursal2'] ?? 0);
        $stock_actual = $stock_sucursal1 + $stock_sucursal2;
        $codigo_barra = trim($_POST['codigo_barra'] ?? '');
        $id_tipo_impuesto = !empty($_POST['id_tipo_impuesto']) ? (int)$_POST['id_tipo_impuesto'] : 1;
        $estado = $_POST['estado'] ?? 'ACTIVO';

        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre del producto es obligatorio.']);
            exit;
        }

        // Generar código de barra si viene vacío
        if (empty($codigo_barra)) {
            $codigo_barra = 'PRD' . str_pad((string)($id ?: rand(1000, 99999)), 6, '0', STR_PAD_LEFT);
        }

        if ($id) {
            $stmt = $pdo->prepare("UPDATE productos SET 
                nombre_prod = :nom,
                descripcion = :des,
                id_marcas = :mar,
                id_categoria = :cat,
                tipo_producto = :tip,
                precio_compra = :pc,
                precio_venta = :pv,
                stock_sucursal1 = :s1,
                stock_sucursal2 = :s2,
                stock_actual = :st,
                codigo_barra = :cb,
                id_tipo_impuesto = :ti,
                estado = :est
                WHERE id_producto = :id");
            $stmt->execute([
                ':nom' => $nombre,
                ':des' => $descripcion,
                ':mar' => $id_marcas,
                ':cat' => $id_categoria,
                ':tip' => $tipo_producto,
                ':pc' => $precio_compra,
                ':pv' => $precio_venta,
                ':s1' => $stock_sucursal1,
                ':s2' => $stock_sucursal2,
                ':st' => $stock_actual,
                ':cb' => $codigo_barra,
                ':ti' => $id_tipo_impuesto,
                ':est' => $estado,
                ':id' => $id
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Producto actualizado correctamente.']);
        } else {
            $stmt = $pdo->prepare("INSERT INTO productos 
                (nombre_prod, descripcion, id_marcas, id_categoria, tipo_producto, precio_compra, precio_venta, stock_sucursal1, stock_sucursal2, stock_actual, codigo_barra, id_tipo_impuesto, estado)
                VALUES (:nom, :des, :mar, :cat, :tip, :pc, :pv, :s1, :s2, :st, :cb, :ti, :est)");
            $stmt->execute([
                ':nom' => $nombre,
                ':des' => $descripcion,
                ':mar' => $id_marcas,
                ':cat' => $id_categoria,
                ':tip' => $tipo_producto,
                ':pc' => $precio_compra,
                ':pv' => $precio_venta,
                ':s1' => $stock_sucursal1,
                ':s2' => $stock_sucursal2,
                ':st' => $stock_actual,
                ':cb' => $codigo_barra,
                ':ti' => $id_tipo_impuesto,
                ':est' => $estado
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Producto registrado con éxito.']);
        }
        exit;
    }

    if ($accion === 'cambiar_estado') {
        $id = (int)($_POST['id_producto'] ?? 0);
        $estado = $_POST['estado'] ?? 'ACTIVO';
        $nuevoEstado = ($estado === 'ACTIVO') ? 'INACTIVO' : 'ACTIVO';

        $stmt = $pdo->prepare("UPDATE productos SET estado = :est WHERE id_producto = :id");
        $stmt->execute([':est' => $nuevoEstado, ':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => "Estado del producto cambiado a $nuevoEstado.", 'nuevo_estado' => $nuevoEstado]);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = (int)($_POST['id_producto'] ?? 0);

        // Validar si tiene ventas asociadas
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM ventas_detalle WHERE id_producto = :id");
        $stmtCheck->execute([':id' => $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'No se puede eliminar el producto porque tiene ventas asociadas en el historial. Se aconseja desactivarlo.']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM productos WHERE id_producto = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['status' => 'success', 'message' => 'Producto eliminado correctamente.']);
        exit;
    }

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
