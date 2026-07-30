<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// Captura de variables de sesión del usuario logueado
$usuario_logueado = $_SESSION['id'] ?? 0;
$rol = $_SESSION['rol'] ?? 'user';

// Función auxiliar recursiva para limpiar y borrar directorios
function eliminarDirectorioCompleto($dir) {
    if (!file_exists($dir)) return;
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        $ruta = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($ruta) ? eliminarDirectorioCompleto($ruta) : unlink($ruta);
    }
    return rmdir($dir);
}

// ================= LISTAR OBRAS =================
if($accion == 'listar'){
    // Reemplazá 'u.nombre' por la columna real (ej: u.username, u.usuario, etc.)
    $sql = "SELECT o.*, c.nombre as cliente, u.usuario as usuario_nombre
            FROM obras o
            LEFT JOIN clientes c ON c.id = o.cliente_id
            LEFT JOIN usuarios u ON u.id = o.usuario_id";

    $res = $conn->query($sql);
    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
}

// ================= GUARDAR / ACTUALIZAR =================
if($accion == 'guardar'){
    $id = $_POST['id'] ?? '';

    $nombre = $conn->real_escape_string($_POST['nombre']);
    $cliente_id = $_POST['cliente_id'] ? intval($_POST['cliente_id']) : 'NULL';
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $nro_oc = $conn->real_escape_string($_POST['nro_oc']);
    $fecha_inicio = $conn->real_escape_string($_POST['fecha_inicio']);
    $fecha_fin = $_POST['fecha_fin'] ? "'".$conn->real_escape_string($_POST['fecha_fin'])."'" : 'NULL';
    $tipo_obra = $conn->real_escape_string($_POST['tipo_obra']);
    $detalle = $conn->real_escape_string($_POST['detalle']);
    $estado = $conn->real_escape_string($_POST['estado']);

    // Validamos y formateamos el ID de usuario usando la variable capturada arriba
    $usuario_id_db = ($usuario_logueado > 0) ? intval($usuario_logueado) : 'NULL';

    if($id){
        // En la edición mantenemos los datos operativos. 
        // Si querés guardar quién la modificó por última vez, podés agregar: usuario_id=$usuario_id_db
        $sql = "UPDATE obras SET
            nombre='$nombre',
            cliente_id=$cliente_id,
            direccion='$direccion',
            nro_oc='$nro_oc',
            fecha_inicio='$fecha_inicio',
            fecha_fin=$fecha_fin,
            tipo_obra='$tipo_obra',
            detalle='$detalle',
            estado='$estado'
        WHERE id=$id";
        
        $conn->query($sql);
        $obra_id = $id;
    } else {
        // En el registro nuevo usamos la variable homologada de forma segura
        $sql = "INSERT INTO obras (nombre, cliente_id, direccion, nro_oc, fecha_inicio, fecha_fin, tipo_obra, detalle, estado, usuario_id)
        VALUES ('$nombre',$cliente_id,'$direccion','$nro_oc','$fecha_inicio',$fecha_fin,'$tipo_obra','$detalle','$estado', $usuario_id_db)";
        
        $conn->query($sql);
        $obra_id = $conn->insert_id;
    }

    // Carpetas físicas dinámicas
    $carpeta_obra = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $obra_id . '/';
    $carpeta_presupuestos = $carpeta_obra . 'presupuestos/';
    $carpeta_repositorio = $carpeta_obra . 'repositorio/';
    
    if(!is_dir($carpeta_obra)) mkdir($carpeta_obra, 0777, true);
    if(!is_dir($carpeta_presupuestos)) mkdir($carpeta_presupuestos, 0777, true);
    if(!is_dir($carpeta_repositorio)) mkdir($carpeta_repositorio, 0777, true);

    // --- PROCESAMIENTO DEL PRESUPUESTO ---
    if(isset($_FILES['presupuesto_archivo']) && $_FILES['presupuesto_archivo']['error'] == UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['presupuesto_archivo']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'presupuesto_' . uniqid() . '.' . $ext;
        $ruta_destino = $carpeta_presupuestos . $nombre_archivo;
        
        if(move_uploaded_file($_FILES['presupuesto_archivo']['tmp_name'], $ruta_destino)) {
            $ruta_db_presupuesto = $obra_id . '/presupuestos/' . $nombre_archivo;
            $conn->query("UPDATE obras SET presupuesto_archivo = '$ruta_db_presupuesto' WHERE id = $obra_id");
        }
    }

    // --- PROCESAMIENTO DEL REPOSITORIO ---
    if(isset($_FILES['archivos_repositorio']['name']) && is_array($_FILES['archivos_repositorio']['name'])) {
        $total_archivos = count($_FILES['archivos_repositorio']['name']);
        
        for($i = 0; $i < $total_archivos; $i++) {
            if($_FILES['archivos_repositorio']['error'][$i] == UPLOAD_ERR_OK) {
                $nombre_original = $conn->real_escape_string($_FILES['archivos_repositorio']['name'][$i]);
                $ext = pathinfo($_FILES['archivos_repositorio']['name'][$i], PATHINFO_EXTENSION);
                
                $nombre_sistema = 'doc_' . uniqid() . '_' . $i . '.' . $ext;
                $ruta_repo = $carpeta_repositorio . $nombre_sistema;
                
                if(move_uploaded_file($_FILES['archivos_repositorio']['tmp_name'][$i], $ruta_repo)) {
                    $ruta_relativa = $obra_id . '/repositorio/' . $nombre_sistema;
                    $conn->query("INSERT INTO obra_archivos (obra_id, archivo, nombre_original) 
                                  VALUES ($obra_id, '$ruta_relativa', '$nombre_original')");
                }
            }
        }
    }

    echo "OK";
}

// ================= TRAER ADJUNTOS DEL REPOSITORIO =================
if($accion == 'listar_archivos') {
    $obra_id = intval($_GET['obra_id'] ?? 0);
    $archivos = [];

    if($obra_id > 0) {
        $res = $conn->query("SELECT id, archivo, nombre_original FROM obra_archivos WHERE obra_id = $obra_id ORDER BY id DESC");
        while($row = $res->fetch_assoc()) {
            $archivos[] = $row;
        }
    }
    echo json_encode(["success" => true, "archivos" => $archivos]);
}

// ================= ELIMINAR UN ARCHIVO INDIVIDUAL =================
if($accion == 'eliminar_archivo') {
    $id = intval($_POST['id'] ?? 0);
    
    $res = $conn->query("SELECT archivo FROM obra_archivos WHERE id = $id");
    if($row = $res->fetch_assoc()) {
        $ruta_fisica = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $row['archivo'];
        if(file_exists($ruta_fisica)) {
            unlink($ruta_fisica);
        }
        $conn->query("DELETE FROM obra_archivos WHERE id = $id");
    }
    echo json_encode(["success" => true]);
}

// ================= ELIMINAR OBRA COMPLETA =================
if($accion == 'eliminar'){
    $id = intval($_POST['id']);

    $carpeta_obra = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/obras/' . $id . '/';
    if(is_dir($carpeta_obra)) {
        eliminarDirectorioCompleto($carpeta_obra);
    }

    $conn->query("DELETE FROM obras WHERE id=$id");

    echo "OK";
}
?>