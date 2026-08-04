<?php
session_start();
include '../config/database.php';

ob_clean();

/* LISTAR */
if($_GET['accion'] == 'listar'){
    header('Content-Type: application/json');

    $r = $conn->query("SELECT * FROM proveedores");

    $data = [];
    while($row = $r->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(["data"=>$data]);
    exit;
}

/* GUARDAR */
if($_GET['accion'] == 'guardar'){

    $id = $_POST['id'] ?? '';
    $usuario = $_SESSION['id'] ?? 'NULL';

    $nombre    = $conn->real_escape_string($_POST['nombre'] ?? '');
    $cuit      = $conn->real_escape_string($_POST['cuit'] ?? '');
    $condicion = $conn->real_escape_string($_POST['condicion_fiscal'] ?? '');
    $direccion = $conn->real_escape_string($_POST['direccion'] ?? '');
    $localidad = $conn->real_escape_string($_POST['localidad'] ?? '');
    $provincia = $conn->real_escape_string($_POST['provincia'] ?? '');
    $cp        = $conn->real_escape_string($_POST['cp'] ?? '');
    $whatsapp  = $conn->real_escape_string($_POST['whatsapp'] ?? '');
    $telefono  = $conn->real_escape_string($_POST['telefono'] ?? '');
    $contacto  = $conn->real_escape_string($_POST['contacto'] ?? '');
    $cbu       = $conn->real_escape_string($_POST['cbu'] ?? '');
    $alias     = $conn->real_escape_string($_POST['alias'] ?? '');
    $producto  = $conn->real_escape_string($_POST['producto_servicio'] ?? '');
    $obs       = $conn->real_escape_string($_POST['observaciones'] ?? '');

    // Manejo del archivo individual
    $sql_archivo = "";
    if (!empty($_FILES['archivo']['name'])) {
        $dir_destino = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/proveedores/';
        if (!file_exists($dir_destino)) {
            mkdir($dir_destino, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['archivo']['name'], PATHINFO_EXTENSION));
        $nombre_archivo = 'prov_' . time() . '_' . rand(100, 999) . '.' . $extension;
        
        if (move_uploaded_file($_FILES['archivo']['tmp_name'], $dir_destino . $nombre_archivo)) {
            $sql_archivo = ", archivo='$nombre_archivo'";
        }
    }

    if($id){
        $sql = "UPDATE proveedores SET 
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
        cbu='$cbu',
        alias='$alias',
        producto_servicio='$producto',
        observaciones='$obs'
        $sql_archivo
        WHERE id=$id";
    } else {
        // En insert, asignamos $nombre_archivo si existe o NULL
        $archivo_val = !empty($nombre_archivo) ? "'$nombre_archivo'" : "NULL";
        
        $sql = "INSERT INTO proveedores 
        (nombre,cuit,condicion_fiscal,direccion,localidad,provincia,cp,whatsapp,telefono,contacto,cbu,alias,producto_servicio,observaciones,archivo,usuario_id)
        VALUES 
        ('$nombre','$cuit','$condicion','$direccion','$localidad','$provincia','$cp','$whatsapp','$telefono','$contacto','$cbu','$alias','$producto','$obs',$archivo_val,$usuario)";
    }

    $conn->query($sql);

    echo "OK";
    exit;
}

/* ELIMINAR PROVEEDOR Y SU ARCHIVO */
if($_GET['accion'] == 'eliminar'){
    $id = (int)$_POST['id'];

    $res = $conn->query("SELECT archivo FROM proveedores WHERE id = $id");
    if($row = $res->fetch_assoc()){
        if(!empty($row['archivo'])){
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/proveedores/' . $row['archivo'];
            if(file_exists($file_path)) unlink($file_path);
        }
    }

    $conn->query("DELETE FROM proveedores WHERE id=$id");
    echo "OK";
    exit;
}

/* ELIMINAR ARCHIVO ADJUNTO */
if($_GET['accion'] == 'eliminar_archivo'){
    $id = (int)$_POST['id'];

    $res = $conn->query("SELECT archivo FROM proveedores WHERE id = $id");
    if($row = $res->fetch_assoc()){
        if(!empty($row['archivo'])){
            $file_path = $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/proveedores/' . $row['archivo'];
            if(file_exists($file_path)) unlink($file_path);
            
            $conn->query("UPDATE proveedores SET archivo=NULL WHERE id=$id");
        }
    }

    echo "OK";
    exit;
}