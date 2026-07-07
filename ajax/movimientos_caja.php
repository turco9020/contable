<?php

include $_SERVER['DOCUMENT_ROOT'].'/contable/config/database.php';

$accion = $_GET['accion'] ?? '';

// =======================
// SALDOS
// =======================

if($accion == 'saldos'){

    $sql = "
        SELECT
            c.id,
            c.nombre,

            IFNULL(
                SUM(
                    CASE
                        WHEN m.tipo='INGRESO'
                        THEN m.importe

                        WHEN m.tipo='TRANSFERENCIA'
                        THEN m.importe

                        ELSE -m.importe
                    END
                )
            ,0) saldo

        FROM cajas c

        LEFT JOIN movimientos_caja m
            ON m.caja_id = c.id

        WHERE c.activa = 1

        GROUP BY c.id

        ORDER BY c.nombre
    ";

    $res = $conn->query($sql);

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode($data);
    exit;
}


// =======================
// LISTAR
// =======================

if($accion == 'listar'){

    // Modificamos la consulta para traer el archivo original desde la tabla gastos
    $sql = "
        SELECT
            m.*,
            c.nombre caja,
            g.archivo AS gasto_archivo
        FROM movimientos_caja m
        LEFT JOIN cajas c 
            ON c.id = m.caja_id
        LEFT JOIN gastos g 
            ON g.id = m.referencia_id AND m.origen = 'GASTO'
        ORDER BY
            m.fecha DESC,
            m.id DESC
    ";

    $res = $conn->query($sql);

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode([
        'data' => $data
    ]);

    exit;
}


// =======================
// GUARDAR
// =======================

if($accion == 'guardar'){

    $id = $_POST['id'] ?? '';

    $fecha = $_POST['fecha'];
    $caja_id = $_POST['caja_id'];

    $tipo = $_POST['tipo'];
    $concepto = $_POST['concepto'];

    $comprobante = $_POST['comprobante'] ?? '';

    $importe = $_POST['importe'];

    $observaciones = $_POST['observaciones'] ?? '';

    $origen = $_POST['origen'] ?? 'MANUAL';

    $referencia_id = !empty($_POST['referencia_id'])
        ? $_POST['referencia_id']
        : "NULL";

    $archivo_nombre = null;

    // =======================
    // ARCHIVO
    // =======================

    if(
        isset($_FILES['archivo'])
        && $_FILES['archivo']['error'] == 0
    ){

        $ext = strtolower(
            pathinfo(
                $_FILES['archivo']['name'],
                PATHINFO_EXTENSION
            )
        );

        $permitidos = [
            'pdf',
            'jpg',
            'jpeg',
            'png'
        ];

        if(in_array($ext, $permitidos)){

            $nombre =
                time().'_'.
                rand(1000,9999).
                '.'.$ext;

            $ruta =
                $_SERVER['DOCUMENT_ROOT'].
                '/contable/uploads/caja/'.
                $nombre;

            // REEMPLAZAR ARCHIVO

            if($id){

                $res = $conn->query("
                    SELECT archivo
                    FROM movimientos_caja
                    WHERE id = $id
                ");

                $old = $res->fetch_assoc();

                if(
                    $old
                    && !empty($old['archivo'])
                ){

                    $rutaVieja =
                        $_SERVER['DOCUMENT_ROOT'].
                        '/contable/uploads/caja/'.
                        $old['archivo'];

                    if(file_exists($rutaVieja)){
                        unlink($rutaVieja);
                    }
                }
            }

            if(
                move_uploaded_file(
                    $_FILES['archivo']['tmp_name'],
                    $ruta
                )
            ){
                $archivo_nombre = $nombre;
            }
        }
    }

    // =======================
    // UPDATE
    // =======================

    if($id){

        $sql = "
            UPDATE movimientos_caja SET

                fecha = '$fecha',
                caja_id = '$caja_id',
                tipo = '$tipo',
                concepto = '$concepto',
                comprobante = '$comprobante',
                importe = '$importe',
                observaciones = '$observaciones',

                origen = '$origen',
                referencia_id = $referencia_id,

                updated_at = NOW()
        ";

        if($archivo_nombre){
            $sql .= ",
                archivo = '$archivo_nombre'
            ";
        }

        $sql .= "
            WHERE id = $id
        ";

    }else{

        // =======================
        // INSERT
        // =======================

        $sql = "
            INSERT INTO movimientos_caja(

                fecha,
                caja_id,
                tipo,
                concepto,
                comprobante,
                archivo,
                importe,
                observaciones,

                origen,
                referencia_id

            ) VALUES (

                '$fecha',
                '$caja_id',
                '$tipo',
                '$concepto',
                '$comprobante',
                ".($archivo_nombre ? "'$archivo_nombre'" : "NULL").",
                '$importe',
                '$observaciones',

                '$origen',
                $referencia_id

            )
        ";
    }

    $ok = $conn->query($sql);

    if(!$ok){
        echo "ERROR: ".$conn->error;
    }else{
        echo "OK";
    }

    exit;
}


// =======================
// ELIMINAR
// =======================

if($accion == 'eliminar'){

    $id = $_POST['id'];

    $res = $conn->query("
        SELECT archivo
        FROM movimientos_caja
        WHERE id = $id
    ");

    $row = $res->fetch_assoc();

    if(
        $row
        && !empty($row['archivo'])
    ){

        $ruta =
            $_SERVER['DOCUMENT_ROOT'].
            '/contable/uploads/caja/'.
            $row['archivo'];

        if(file_exists($ruta)){
            unlink($ruta);
        }
    }

    $conn->query("
        DELETE FROM movimientos_caja
        WHERE id = $id
    ");

    echo "OK";
    exit;
}