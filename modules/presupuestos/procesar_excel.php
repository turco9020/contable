<?php
session_start();
require_once '../../config/database.php';

// Detectar variable de conexión MySQLi (misma lógica que materiales.php)
$db_conn = $conn ?? $conexion ?? $db ?? null;

// Obtener el nombre de usuario de la sesión para registrar quién actualizó
$user_id = $_SESSION['id'] ?? 0;
$usuario_actual = 'Sistema';

if ($db_conn && $user_id > 0) {
    $stmt_u = $db_conn->prepare("SELECT usuario FROM usuarios WHERE id = ?");
    if ($stmt_u) {
        $stmt_u->bind_param("i", $user_id);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        if ($row_u = $res_u->fetch_assoc()) {
            $usuario_actual = $row_u['usuario'];
        }
    }
}

function convertir_letra_columna($celda) {
    preg_match('/([A-Z]+)/', $celda, $coincidencias);
    if (empty($coincidencias[1])) return 0;
    $letras = $coincidencias[1];
    $indice = 0;
    for ($i = 0; $i < strlen($letras); $i++) {
        $indice = $indice * 26 + (ord($letras[$i]) - 64);
    }
    return $indice - 1;
}

function limpiar_numero($valor) {
    if (empty($valor)) return 0.0;
    $valor = str_replace(',', '.', $valor);
    $valor = preg_replace('/[^\d.]/', '', $valor);
    return floatval($valor);
}

function leer_xlsx_nativo($ruta_archivo) {
    $zip = new ZipArchive();
    $filas_datos = [];

    if ($zip->open($ruta_archivo) === true) {
        $strings = [];
        if (($data_strings = $zip->getFromName('xl/sharedStrings.xml'))) {
            $xml_strings = simplexml_load_string($data_strings);
            foreach ($xml_strings->si as $val) {
                $strings[] = (string)$val->t;
            }
        }

        if (($data_hoja = $zip->getFromName('xl/worksheets/sheet1.xml'))) {
            $xml_hoja = simplexml_load_string($data_hoja);

            foreach ($xml_hoja->sheetData->row as $row) {
                $fila = [];
                foreach ($row->c as $c) {
                    $ref_celda = (string)$c['r'];
                    $col_idx = convertir_letra_columna($ref_celda);
                    $valor = (string)$c->v;

                    if (isset($c['t']) && (string)$c['t'] == 's') {
                        $valor = $strings[(int)$valor] ?? '';
                    }

                    $fila[$col_idx] = trim($valor);
                }
                
                if (!empty($fila)) {
                    $filas_datos[] = $fila;
                }
            }
        }
        $zip->close();
    }
    return $filas_datos;
}

// Responder siempre en JSON para AJAX
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_excel'])) {
    $archivo = $_FILES['archivo_excel']['tmp_name'];

    if (file_exists($archivo) && $db_conn) {
        $filas = leer_xlsx_nativo($archivo);
        
        $insertados = 0;
        $actualizados = 0;

        foreach ($filas as $col) {
            $posible_nombre = $col[1] ?? ($col[0] ?? '');
            if ($posible_nombre === 'Sistema Contable' || $posible_nombre === 'Material' || $posible_nombre === 'ID') {
                continue;
            }

            // Mapeo según el Excel real:
            // Col 0: ID (si existe)
            // Col 1: Material (Nombre)
            // Col 2: Precio Presentacion (Precio bulto)
            // Col 3: Unidad
            // Col 4: Precio Unitario
            // Col 5: Proveedor
            $id          = !empty($col[0]) ? intval($col[0]) : null;
            $nombre      = $col[1] ?? '';
            $precio_b    = limpiar_numero($col[2] ?? 0);
            $unidad      = $col[3] ?? '';
            $precio_u    = limpiar_numero($col[4] ?? 0);
            $proveedor   = $col[5] ?? '';
            $fecha_act   = date('Y-m-d');

            if (empty($nombre)) continue;

            if ($id && $id > 0) {
                // UPDATE en la tabla presupuesto_materiales
                $stmt = $db_conn->prepare("UPDATE presupuesto_materiales SET 
                    nombre=?, unidad_medida=?, precio_bulto=?, precio_unitario=?, proveedor=?, fecha_actualizacion=?, usuario_nombre=? 
                    WHERE id=?");
                $stmt->bind_param("ssddsssi", $nombre, $unidad, $precio_b, $precio_u, $proveedor, $fecha_act, $usuario_actual, $id);
                $stmt->execute();
                $actualizados++;
            } else {
                // INSERT en la tabla presupuesto_materiales
                $stmt = $db_conn->prepare("INSERT INTO presupuesto_materiales 
                    (nombre, unidad_medida, precio_bulto, precio_unitario, proveedor, fecha_actualizacion, usuario_nombre) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssddsss", $nombre, $unidad, $precio_b, $precio_u, $proveedor, $fecha_act, $usuario_actual);
                $stmt->execute();
                $insertados++;
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "Importación exitosa: $insertados registros creados y $actualizados actualizados."
        ]);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'No se pudo procesar el archivo.']);
exit;