<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
$conexion = $conn; 

// Activar reporte de errores MySQLi para que el try/catch realmente capture cualquier falla
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$accion = $_GET['accion'] ?? '';

if ($accion === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id      = $_SESSION['usuario_id'] ?? NULL;
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

    // RUTA DE SUBIDA DE ARCHIVOS
    $directorio_destino = __DIR__ . '/../../uploads/vencimientos/';
    $nombre_archivo = "NULL";

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION);
        $file_name_gen = 'venc_' . time() . '_' . uniqid() . '.' . $ext;
        
        if (!is_dir($directorio_destino)) {
            mkdir($directorio_destino, 0777, true);
        }
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $directorio_destino . $file_name_gen)) {
            $nombre_archivo = "'$file_name_gen'";
        }
    }

    // Cálculo del monto por cuota
    if ($es_cuotas && $modo_calculo === 'total') {
        $monto_cuota = round($monto_ingresado / $total_cuotas, 2);
    } else {
        $monto_cuota = $monto_ingresado;
    }

    mysqli_begin_transaction($conexion);

    try {
        $padre_id_real = "NULL";

        for ($i = 1; $i <= $total_cuotas; $i++) {
            $fecha_obj = new DateTime($fecha_venc);
            if ($i > 1) {
                $fecha_obj->modify('+' . ($i - 1) . ' month');
            }
            $fecha_cuota = $fecha_obj->format('Y-m-d');

            $titulo_final = $es_cuotas ? "$titulo (Cuota $i/$total_cuotas)" : $titulo;
            $usr_sql = $usuario_id ? $usuario_id : "NULL";

            // En la primera cuota (o pago único) vencimiento_padre_id se guarda como NULL
            // A partir de la cuota 2, se asigna el ID de la primera cuota
            $venc_padre_val = ($i === 1) ? "NULL" : $padre_id_real;

            $sql = "INSERT INTO vencimientos 
                    (vencimiento_padre_id, titulo, descripcion, monto, nro_cuota, total_cuotas, fecha_vencimiento, categoria_id, subcategoria_id, proveedor_id, dias_aviso, archivo, usuario_id) 
                    VALUES 
                    ($venc_padre_val, '$titulo_final', '$descripcion', $monto_cuota, $i, $total_cuotas, '$fecha_cuota', $categoria_id, $subcategoria_id, $proveedor_id, $dias_aviso, $nombre_archivo, $usr_sql)";

            mysqli_query($conexion, $sql);

            // Guardamos el ID del primer registro para vincular las cuotas hijas posteriores
            if ($i === 1 && $es_cuotas) {
                $padre_id_real = mysqli_insert_id($conexion);
            }
        }

        mysqli_commit($conexion);
        header("Location: index.php?res=success");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        // Te redirige mostrando el error exacto si falla MySQL
        header("Location: index.php?res=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
}

if ($accion === 'eliminar' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Elimina el vencimiento principal y sus cuotas hijas si existieran
    mysqli_query($conexion, "DELETE FROM vencimientos WHERE id = $id OR vencimiento_padre_id = $id");
    header("Location: index.php?res=success");
    exit;
}