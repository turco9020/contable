<?php
session_start();
include '../config/database.php';
include '../includes/documentacion.php';

ob_clean();

/* LISTAR */
if($_GET['accion'] == 'listar'){

    header('Content-Type: application/json');

    $result = $conn->query("SELECT c.*, " . doc_sql_cantidad('cliente', 'c') . " FROM clientes c");

    $data = [];

    while($row = $result->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
    exit;
}

/* GUARDAR */
if($_GET['accion'] == 'guardar'){

    header('Content-Type: application/json');

    // Documentación: se valida el tipo de cada archivo antes de guardar nada
    $rechazados = doc_rechazados();
    if ($rechazados) {
        echo json_encode([
            'ok'    => false,
            'error' => 'Tipo de archivo no permitido: ' . implode(', ', $rechazados) . '. Se aceptan PDF, imágenes (JPG/PNG), Word y Excel.'
        ]);
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $usuario = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 'NULL';

    $nombre        = $conn->real_escape_string($_POST['nombre'] ?? '');
    $cuit          = $conn->real_escape_string($_POST['cuit'] ?? '');
    $condicion     = $conn->real_escape_string($_POST['condicion_fiscal'] ?? '');
    $direccion     = $conn->real_escape_string($_POST['direccion'] ?? '');
    $localidad     = $conn->real_escape_string($_POST['localidad'] ?? '');
    $provincia     = $conn->real_escape_string($_POST['provincia'] ?? '');
    $cp            = $conn->real_escape_string($_POST['cp'] ?? '');
    $whatsapp      = $conn->real_escape_string($_POST['whatsapp'] ?? '');
    $telefono      = $conn->real_escape_string($_POST['telefono'] ?? '');
    $contacto      = $conn->real_escape_string($_POST['contacto'] ?? '');
    $email         = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $red_social    = $conn->real_escape_string(trim($_POST['red_social'] ?? ''));
    $sitio_web     = $conn->real_escape_string(trim($_POST['sitio_web'] ?? ''));
    $observaciones = $conn->real_escape_string($_POST['observaciones'] ?? '');

    if($id){
        $sql = "UPDATE clientes SET
        nombre='$nombre',
        cuit='$cuit',
        condicion_fiscal='$condicion',
        direccion='$direccion',
        localidad='$localidad',
        provincia='$provincia',
        cp='$cp',
        whatsapp='$whatsapp',
        telefono='$telefono',
        contacto='$contacto',
        email='$email',
        red_social='$red_social',
        sitio_web='$sitio_web',
        observaciones='$observaciones'
        WHERE id=$id";
    } else {
        $sql = "INSERT INTO clientes
        (nombre,cuit,condicion_fiscal,direccion,localidad,provincia,cp,whatsapp,telefono,contacto,email,red_social,sitio_web,observaciones,usuario_id)
        VALUES
        ('$nombre','$cuit','$condicion','$direccion','$localidad','$provincia','$cp','$whatsapp','$telefono','$contacto','$email','$red_social','$sitio_web','$observaciones',$usuario)";
    }

    $ok = $conn->query($sql);
    $cliente_id = $id ? $id : (int)$conn->insert_id;

    // Archivos de documentación (se suben una vez que el cliente existe)
    $no_subidos = $ok ? doc_guardar_subidos($conn, 'cliente', $cliente_id, $usuario === 'NULL' ? null : $usuario) : [];

    // Se devuelve el id para que el alta rápida pueda seleccionar el cliente nuevo
    echo json_encode([
        'ok'         => (bool)$ok,
        'id'         => $cliente_id,
        'nuevo'      => !$id,
        'nombre'     => $_POST['nombre'] ?? '',
        'cuit'       => $_POST['cuit'] ?? '',
        'no_subidos' => $no_subidos
    ]);
    exit;
}

/* DOCUMENTACIÓN: LISTAR ARCHIVOS DE UN CLIENTE */
if($_GET['accion'] == 'listar_archivos'){
    header('Content-Type: application/json');
    $cliente_id = (int)($_GET['id'] ?? 0);
    echo json_encode(['ok' => true, 'archivos' => doc_listar($conn, 'cliente', $cliente_id)]);
    exit;
}

/* DOCUMENTACIÓN: ELIMINAR UN ARCHIVO */
if($_GET['accion'] == 'eliminar_archivo'){
    header('Content-Type: application/json');
    $archivo_id = (int)($_POST['id'] ?? 0);
    echo json_encode(['ok' => doc_eliminar_uno($conn, 'cliente', $archivo_id)]);
    exit;
}

/* ELIMINAR */
if($_GET['accion'] == 'eliminar'){
    $id = (int)($_POST['id'] ?? 0);
    $rutas = doc_rutas_registro($conn, 'cliente', $id);
    $conn->query("DELETE FROM clientes WHERE id=$id");
    if ($conn->affected_rows > 0) {
        doc_borrar_del_disco('cliente', $id, $rutas);
    }
    echo "OK";
    exit;
}
