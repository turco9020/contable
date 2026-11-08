<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
$conexion = $conn; 

// Activar reporte de errores MySQLi para que el try/catch capture fallas
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$accion = $_GET['accion'] ?? '';

if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Si viene un ID oculto en el formulario, se trata de una EDICIÓN
    $id              = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $usuario_id      = $_SESSION['id'] ?? NULL; // el sistema guarda el usuario logueado en $_SESSION['id']
    $titulo          = mysqli_real_escape_string($conexion, trim($_POST['titulo']));
    
    $categoria_id    = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : "NULL";
    $subcategoria_id = !empty($_POST['subcategoria_id']) ? intval($_POST['subcategoria_id']) : "NULL";
    $proveedor_id    = !empty($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : "NULL";
    
    $monto_ingresado = floatval($_POST['monto']);
    $fecha_venc      = mysqli_real_escape_string($conexion, $_POST['fecha_vencimiento']);
    $dias_aviso      = intval($_POST['dias_aviso'] ?? 7);
    $descripcion     = mysqli_real_escape_string($conexion, trim($_POST['descripcion'] ?? ''));
    
    $es_cuotas       = isset($_POST['es_cuotas']) ? true : false;
    $total_cuotas    = $es_cuotas ? intval($_POST['total_cuotas']) : 1;
    $modo_calculo    = $_POST['modo_calculo'] ?? 'total';

    // RUTA Y MANEJO DE SUBIDA DE ARCHIVOS
    $directorio_destino = __DIR__ . '/../../uploads/vencimientos/';
    $sql_archivo_update = "";
    $nombre_archivo_insert = "NULL";

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $file_name_gen = 'venc_' . time() . '_' . uniqid() . '.' . $ext;
        
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $directorio_destino . $file_name_gen)) {
            $nombre_archivo_insert = "'$file_name_gen'";
            $sql_archivo_update = ", archivo = '$file_name_gen'";
        }
    }

    mysqli_begin_transaction($conexion);

    try {
        if ($id) {
            // ==========================================
            // 1. ACTUALIZACIÓN (EDITAR REGISTRO EXISTENTE)
            // ==========================================
            $sql = "UPDATE vencimientos SET 
                        titulo = '$titulo',
                        descripcion = '$descripcion',
                        monto = $monto_ingresado,
                        fecha_vencimiento = '$fecha_venc',
                        categoria_id = $categoria_id,
                        subcategoria_id = $subcategoria_id,
                        proveedor_id = $proveedor_id,
                        dias_aviso = $dias_aviso
                        $sql_archivo_update
                    WHERE id = $id";

            mysqli_query($conexion, $sql);

        } else {
            // ==========================================
            // 2. CREACIÓN (NUEVO REGISTRO / CUOTAS)
            // ==========================================
            if ($es_cuotas && $modo_calculo === 'total') {
                $monto_cuota = round($monto_ingresado / $total_cuotas, 2);
            } else {
                $monto_cuota = $monto_ingresado;
            }

            $padre_id_real = "NULL";
            $fecha_base = new DateTime($fecha_venc);
            $dia_base = (int)$fecha_base->format('j');

            for ($i = 1; $i <= $total_cuotas; $i++) {
                // Mismo día cada mes; si el mes no tiene ese día (29, 30, 31), el último día del mes.
                // (modify('+1 month') desde un 31/01 da 03/03 y febrero queda sin cuota)
                $fecha_obj = (clone $fecha_base)->modify('first day of +' . ($i - 1) . ' month');
                $fecha_obj->setDate((int)$fecha_obj->format('Y'), (int)$fecha_obj->format('n'), min($dia_base, (int)$fecha_obj->format('t')));
                $fecha_cuota = $fecha_obj->format('Y-m-d');

                // Con "monto total", la última cuota absorbe los centavos para que sumen exacto
                // (ej: $ 1.000 en 3 cuotas = 333,33 + 333,33 + 333,34)
                $monto_esta_cuota = $monto_cuota;
                if ($es_cuotas && $modo_calculo === 'total' && $i === $total_cuotas) {
                    $monto_esta_cuota = round($monto_ingresado - $monto_cuota * ($total_cuotas - 1), 2);
                }

                $titulo_final = $es_cuotas ? "$titulo (Cuota $i/$total_cuotas)" : $titulo;
                $usr_sql = $usuario_id ? $usuario_id : "NULL";

                // En la primera cuota vencimiento_padre_id es NULL. A partir de la 2da se asigna el ID padre.
                $venc_padre_val = ($i === 1) ? "NULL" : $padre_id_real;

                $sql = "INSERT INTO vencimientos 
                        (vencimiento_padre_id, titulo, descripcion, monto, nro_cuota, total_cuotas, fecha_vencimiento, categoria_id, subcategoria_id, proveedor_id, dias_aviso, archivo, usuario_id) 
                        VALUES 
                        ($venc_padre_val, '$titulo_final', '$descripcion', $monto_esta_cuota, $i, $total_cuotas, '$fecha_cuota', $categoria_id, $subcategoria_id, $proveedor_id, $dias_aviso, $nombre_archivo_insert, $usr_sql)";

                mysqli_query($conexion, $sql);

                // Guardar el ID del primer registro como padre de la serie
                if ($i === 1 && $es_cuotas) {
                    $padre_id_real = mysqli_insert_id($conexion);
                }
            }
        }

        mysqli_commit($conexion);
        header("Location: index.php?res=success");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        header("Location: index.php?res=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
}

if ($accion === 'eliminar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Elimina el vencimiento principal y sus cuotas hijas vinculadas
    mysqli_query($conexion, "DELETE FROM vencimientos WHERE id = $id OR vencimiento_padre_id = $id");
    header("Location: index.php?res=success");
    exit;
}