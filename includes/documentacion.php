<?php
/* ==========================================================================
   DOCUMENTACIÓN MÚLTIPLE DE CLIENTES Y PROVEEDORES
   --------------------------------------------------------------------------
   Funciones compartidas por ajax/clientes.php y ajax/proveedores.php para
   subir, listar y borrar varios archivos por registro.

   Tablas: cliente_archivos / proveedor_archivos
   Carpetas: uploads/clientes/{id}/ y uploads/proveedores/{id}/
   La columna `archivo` guarda la ruta relativa a la carpeta del tipo
   (ej: "12/doc_a1b2c3.pdf"; los adjuntos migrados del campo viejo de
   proveedores quedan como "prov_123_456.pdf").
   ========================================================================== */

const DOC_EXT_PERMITIDAS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
const DOC_CAMPO_FORM = 'doc_archivos';

function doc_config($tipo) {
    $cfg = [
        'cliente'   => ['tabla' => 'cliente_archivos',   'fk' => 'cliente_id',   'carpeta' => 'clientes'],
        'proveedor' => ['tabla' => 'proveedor_archivos', 'fk' => 'proveedor_id', 'carpeta' => 'proveedores'],
    ];
    return $cfg[$tipo];
}

function doc_dir_base($cfg) {
    return $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/' . $cfg['carpeta'] . '/';
}

// Ruta física segura de un archivo guardado (sin "..", siempre dentro de la carpeta del tipo)
function doc_ruta_fisica($cfg, $archivo) {
    if ($archivo === '' || strpos($archivo, '..') !== false || strpos($archivo, "\0") !== false) {
        return null;
    }
    return doc_dir_base($cfg) . ltrim($archivo, '/\\');
}

// Lista de nombres con extensión no permitida (se valida ANTES de guardar nada)
function doc_rechazados() {
    $mal = [];
    $f = $_FILES[DOC_CAMPO_FORM] ?? null;
    if (!$f || !is_array($f['name'])) return $mal;

    foreach ($f['name'] as $i => $nombre) {
        if ($nombre === '' || $f['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if (!in_array($ext, DOC_EXT_PERMITIDAS, true)) $mal[] = $nombre;
    }
    return $mal;
}

// Guarda los archivos subidos. Devuelve los nombres que no se pudieron subir (ej: muy pesados)
function doc_guardar_subidos($conn, $tipo, $entidad_id, $usuario_id) {
    $no_subidos = [];
    $f = $_FILES[DOC_CAMPO_FORM] ?? null;
    if ($entidad_id <= 0 || !$f || !is_array($f['name'])) return $no_subidos;

    $cfg = doc_config($tipo);
    $dir = doc_dir_base($cfg) . $entidad_id . '/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $stmt = $conn->prepare("INSERT INTO {$cfg['tabla']} ({$cfg['fk']}, archivo, nombre_original, usuario_id) VALUES (?, ?, ?, ?)");

    foreach ($f['name'] as $i => $nombre) {
        if ($nombre === '' || $f['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        if ($f['error'][$i] !== UPLOAD_ERR_OK || !in_array($ext, DOC_EXT_PERMITIDAS, true)) {
            $no_subidos[] = $nombre;
            continue;
        }

        $nombre_sistema = 'doc_' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (!move_uploaded_file($f['tmp_name'][$i], $dir . $nombre_sistema)) {
            $no_subidos[] = $nombre;
            continue;
        }

        $ruta     = $entidad_id . '/' . $nombre_sistema;
        $original = mb_substr(basename($nombre), 0, 255);
        $stmt->bind_param('issi', $entidad_id, $ruta, $original, $usuario_id);
        $stmt->execute();
    }

    $stmt->close();
    return $no_subidos;
}

function doc_listar($conn, $tipo, $entidad_id) {
    $cfg = doc_config($tipo);
    $stmt = $conn->prepare("SELECT id, archivo, nombre_original, fecha_subida FROM {$cfg['tabla']} WHERE {$cfg['fk']} = ? ORDER BY id DESC");
    $stmt->bind_param('i', $entidad_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $archivos = [];
    while ($row = $res->fetch_assoc()) {
        $row['url'] = '/contable/uploads/' . $cfg['carpeta'] . '/' . $row['archivo'];
        $archivos[] = $row;
    }
    $stmt->close();
    return $archivos;
}

function doc_eliminar_uno($conn, $tipo, $archivo_id) {
    $cfg = doc_config($tipo);
    $stmt = $conn->prepare("SELECT archivo FROM {$cfg['tabla']} WHERE id = ?");
    $stmt->bind_param('i', $archivo_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) return false;

    $ruta = doc_ruta_fisica($cfg, $row['archivo']);
    if ($ruta && is_file($ruta)) unlink($ruta);

    $stmt = $conn->prepare("DELETE FROM {$cfg['tabla']} WHERE id = ?");
    $stmt->bind_param('i', $archivo_id);
    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Al eliminar un cliente/proveedor: se toman las rutas ANTES de borrar el registro
// (las filas se van por ON DELETE CASCADE) y los archivos se borran del disco
// solo si el registro realmente se eliminó.
function doc_rutas_registro($conn, $tipo, $entidad_id) {
    $cfg = doc_config($tipo);
    $rutas = [];
    foreach (doc_listar($conn, $tipo, $entidad_id) as $a) {
        $ruta = doc_ruta_fisica($cfg, $a['archivo']);
        if ($ruta) $rutas[] = $ruta;
    }
    return $rutas;
}

function doc_borrar_del_disco($tipo, $entidad_id, $rutas) {
    foreach ($rutas as $ruta) {
        if (is_file($ruta)) unlink($ruta);
    }
    $dir = doc_dir_base(doc_config($tipo)) . (int)$entidad_id;
    if (is_dir($dir)) @rmdir($dir); // solo si quedó vacía
}

// Subconsulta para mostrar la cantidad de archivos en el listado
function doc_sql_cantidad($tipo, $alias_tabla) {
    $cfg = doc_config($tipo);
    return "(SELECT COUNT(*) FROM {$cfg['tabla']} d WHERE d.{$cfg['fk']} = {$alias_tabla}.id) AS cantidad_archivos";
}
