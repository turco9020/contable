<?php
session_start();
require_once '../../config/database.php';

// Detectar variable de conexión MySQLi
$db_conn = $conn ?? $conexion ?? $db ?? null;

// Obtener el ID de usuario desde la sesión (igual que en Facturación)
$user_id = $_SESSION['id'] ?? 0;
$usuario_actual = 'Sistema';

if ($db_conn && $user_id > 0) {
    $stmt_u = $db_conn->prepare("SELECT usuario FROM usuarios WHERE id = ?");
    if ($stmt_u) {
        $stmt_u->bind_param("i", $user_id);
        $stmt_u->execute();
        $res_u = $stmt_u->get_result();
        if ($row_u = $res_u->fetch_assoc()) {
            $usuario_actual = $row_u['usuario']; // O $row_u['nombre'] según tu columna en 'usuarios'
        }
    }
}

// Cargar lista de proveedores para el desplegable del modal
$lista_proveedores = [];
if ($db_conn) {
    $res_prov = $db_conn->query("SELECT id, nombre FROM proveedores ORDER BY nombre ASC");
    if ($res_prov) {
        while ($row_p = $res_prov->fetch_assoc()) {
            $lista_proveedores[] = $row_p['nombre'];
        }
    }
}

// PROCESAMIENTO AJAX
if (isset($_GET['action'])) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    if ($action === 'listar') {
        $data = [];
        if ($db_conn) {
            $res = $db_conn->query("SELECT * FROM presupuesto_materiales WHERE activo = 1 ORDER BY id DESC");
            if ($res) {
                $data = $res->fetch_all(MYSQLI_ASSOC);
            }
        }
        echo json_encode(['data' => $data]);
        exit;
    }

    if ($action === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id                  = intval($_POST['id'] ?? 0);
        $nombre              = trim($_POST['nombre']);
        $peso_cantidad       = trim($_POST['peso_cantidad'] ?? '');
        $unidad_medida       = trim($_POST['unidad_medida']);
        $precio_bulto        = floatval($_POST['precio_bulto']);
        
        // Cálculo del precio unitario en PHP por seguridad backend
        $num_peso = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '.', $peso_cantidad)));
        $precio_unitario = ($num_peso > 0) ? ($precio_bulto / $num_peso) : $precio_bulto;

        $proveedor           = trim($_POST['proveedor'] ?? '');
        $fecha_actualizacion = !empty($_POST['fecha_actualizacion']) ? $_POST['fecha_actualizacion'] : date('Y-m-d');

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_materiales SET 
                nombre=?, peso_cantidad=?, unidad_medida=?, precio_bulto=?, precio_unitario=?, proveedor=?, fecha_actualizacion=?, usuario_nombre=? 
                WHERE id=?");
            $stmt->bind_param("sssddsssi", $nombre, $peso_cantidad, $unidad_medida, $precio_bulto, $precio_unitario, $proveedor, $fecha_actualizacion, $usuario_actual, $id);
        } else {
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_materiales 
                (nombre, peso_cantidad, unidad_medida, precio_bulto, precio_unitario, proveedor, fecha_actualizacion, usuario_nombre) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssddsss", $nombre, $peso_cantidad, $unidad_medida, $precio_bulto, $precio_unitario, $proveedor, $fecha_actualizacion, $usuario_actual);
        }

        $ok = $stmt ? $stmt->execute() : false;
        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($action === 'edicion_rapida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id        = intval($_POST['id'] ?? 0);
        $precio_b  = floatval($_POST['precio_bulto']);
        $fecha_act = !empty($_POST['fecha_actualizacion']) ? $_POST['fecha_actualizacion'] : date('Y-m-d');

        if ($id > 0) {
            // Traer peso_cantidad para recálculo
            $res = $db_conn->query("SELECT peso_cantidad FROM presupuesto_materiales WHERE id = $id");
            $row = $res ? $res->fetch_assoc() : null;
            $peso_cant = $row['peso_cantidad'] ?? '1';
            
            $num_peso = floatval(preg_replace('/[^0-9.]/', '', str_replace(',', '.', $peso_cant)));
            $precio_u = ($num_peso > 0) ? ($precio_b / $num_peso) : $precio_b;

            $stmt = $db_conn->prepare("UPDATE presupuesto_materiales SET 
                precio_bulto=?, precio_unitario=?, fecha_actualizacion=?, usuario_nombre=? 
                WHERE id=?");
            $stmt->bind_param("ddssi", $precio_b, $precio_u, $fecha_act, $usuario_actual, $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if ($action === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_materiales SET activo = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

// Cierre preventivo de submenús
echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-box-seam text-secondary me-2"></i> Gestión de Materiales
        </h4>
        <div class="d-flex gap-2">
            <!-- Formulario/Input Oculto para seleccionar Excel -->
            <input type="file" id="inputArchivoExcel" accept=".xlsx" style="display: none;" onchange="procesarImportacionExcel(this)">
            
            <!-- En la cabecera de materiales.php -->
            <button class="btn btn-outline-success d-flex align-items-center fw-semibold" data-bs-toggle="modal" data-bs-target="#modalImportarMateriales">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Importar CSV
            </button>

            <button class="btn btn-outline-warning text-dark fw-semibold d-flex align-items-center" id="btnModoEdicion" onclick="toggleModoEdicion()">
                <i class="bi bi-pencil-square me-1"></i> Edición Rápida
            </button>
            <button class="btn btn-success align-items-center" id="btnGuardarRapido" onclick="guardarEdicionRapida()" style="display:none;">
                <i class="bi bi-check-lg me-1"></i> Guardar Cambios
            </button>
            <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal('NUEVO')">
                <i class="bi bi-plus-circle me-2"></i> Nuevo Material
            </button>
        </div>
    </div>

    <!-- TABLA DE MATERIALES -->
    <div class="card shadow-sm border-0 p-3">
        <div class="table-responsive">
            <table id="tablaMateriales" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center" style="width: 5%;">ID</th>
                        <th>Material</th>
                        <th class="text-end" style="width: 18%;">Precio Presentación (s/IVA)</th>
                        <th class="text-center" style="width: 10%;">Unidad</th>
                        <th class="text-end" style="width: 15%;">Precio unit. (s/IVA)</th>
                        <th style="width: 15%;">Proveedor</th>
                        <th class="text-center" style="width: 14%;">Fecha actualización</th>
                        <th class="text-center" style="width: 10%;">Usuario</th>
                        <th class="text-center" style="width: 8%;">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- MODAL UNIFICADO (NUEVO / EDITAR) -->
<div class="modal fade" id="modalMaterial" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalMaterialLabel"><i class="bi bi-box-seam me-2"></i>Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formMaterial">
                <div class="modal-body bg-light">
                    <input type="hidden" name="id" id="mat_id" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre del Material *</label>
                            <input name="nombre" id="nombre" class="form-control" required placeholder="Ej: Cal hidratada / Ladrillo común">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Peso/Cantidad *</label>
                            <input name="peso_cantidad" id="peso_cantidad" class="form-control" required placeholder="Ej: EL TAMAÑO DE PRESENTACION 25 / 1 / 1000 ">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Unidad Computable *</label>
                            <select name="unidad_medida" id="unidad_medida" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="KG">KG (Kilogramo)</option>
                                <option value="U">U (Unidad)</option>
                                <option value="M³">M³ (Metro Cúbico)</option>
                                <option value="M²">M² (Metro Cuadrado)</option>
                                <option value="M">M (Metro)</option>
                                <option value="ML">ML (Metro Lineal)</option>
                                <option value="L">L (Litros)</option>
                                <option value="GL">GL (Global)</option>
                                <option value="TN">TN (Tonelada)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Precio Presentación (s/ IVA) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="precio_bulto" id="precio_bulto" class="form-control text-end" required placeholder="0.00">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Proveedor</label>
                            <select name="proveedor" id="proveedor" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <?php foreach ($lista_proveedores as $prov): ?>
                                    <option value="<?= htmlspecialchars($prov) ?>"><?= htmlspecialchars($prov) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Fecha de Actualización</label>
                            <input type="date" name="fecha_actualizacion" id="fecha_actualizacion" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" id="btnGuardarMaterial">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalMaterialBS;
let tabla;
let modoEdicionActivo = false;
let buscadoresTom = {};

        function aplicarBuscadores() {
            let el = document.querySelector('#proveedor');
            if (!el) return;

            if (buscadoresTom['#proveedor']) {
                buscadoresTom['#proveedor'].destroy();
            }

            buscadoresTom['#proveedor'] = new TomSelect(el, {
                create: false,
                sortField: { field: "text", order: "asc" },
                placeholder: "-- Seleccionar o Buscar --",
                allowEmptyOption: true
            });
        }

document.addEventListener("DOMContentLoaded", function(){
    modalMaterialBS = new bootstrap.Modal(document.getElementById('modalMaterial'));

    tabla = $('#tablaMateriales').DataTable({
        ajax: {
            url: 'materiales.php?action=listar',
            dataSrc: 'data'
        },
        order: [[0, 'asc']],
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf>rtip',
        buttons: [
            {
                extend: 'excelHtml5',
                text: ' Excel',
                className: 'btn btn-success btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6] }
            },
            { 
                extend: 'colvis', 
                text: 'Columnas', 
                className: 'btn btn-sm btn-secondary' 
            }
        ],
        columns: [
            { 
                data: 'id',
                className: 'text-center text-secondary',
                render: d => `${d}`
            },
            { 
                // Renderizado: Nombre Material + peso_cantidad + unidad_medida
                data: null,
                render: function(d) {
                    let peso = d.peso_cantidad ? ` x ${d.peso_cantidad}` : '';
                    let un = d.unidad_medida ? `  ${d.unidad_medida}` : '';
                    return `<span class="fw-semibold text-dark" style="font-size: 0.85rem;">${d.nombre}</span><small class="text-muted">${peso}${un}</small>`;
                }
            },
            { 
                data: 'precio_bulto',
                className: 'text-end',
                render: function(d, type, row) {
                    let val = parseFloat(d || 0).toFixed(2);
                    if (modoEdicionActivo) {
                        return `<div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control text-end input-rapido-bulto" data-id="${row.id}" value="${val}">
                        </div>`;
                    }
                    return `<span class="fw-semibold">$ ${val}</span>`;
                }
            },
            { 
                data: 'unidad_medida',
                className: 'text-center',
                // Estilo ajustado: Sin negrita ni bordes prominentes
                render: d => `<span class="badge bg-light text-dark border fw-normal">${d || ''}</span>`
            },
            { 
                data: 'precio_unitario',
                className: 'text-end',
                render: d => `$ ${parseFloat(d || 0).toFixed(2)}`
            },
            { 
                data: 'proveedor',
                render: d => d ? d : '<span class="text-muted">-</span>'
            },
            { 
                data: 'fecha_actualizacion',
                className: 'text-center',
                render: function(d, type, row) {
                    let f = d ? d : '<?= date('Y-m-d') ?>';
                    if (modoEdicionActivo) {
                        return `<input type="date" class="form-control form-control-sm text-center input-rapido-fecha" data-id="${row.id}" value="${f}">`;
                    }
                    return f;
                }
            },
            { 
                data: 'usuario_nombre',
                className: 'text-center',
                render: d => d ? `<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> ${d}</span>` : '<span class="badge bg-light text-dark border fw-normal"><i class="bi bi-person"></i> Sistema</span>'
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: function(d){
                    return `
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary" title="Editar Completo" onclick="editar(${d.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar Material" onclick="eliminar(${d.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    $('#formMaterial').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: 'materiales.php?action=guardar',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    tabla.ajax.reload(null, false);
                    modalMaterialBS.hide();
                } else {
                    alert('Error al guardar el material.');
                }
            }
        });
    });
});

// CONTROL DE EDICIÓN RÁPIDA (ACTIVAR / DESACTIVAR)
function toggleModoEdicion() {
    modoEdicionActivo = !modoEdicionActivo;

    if (modoEdicionActivo) {
        $('#btnModoEdicion').html('<i class="bi bi-x-circle me-1"></i> Cancelar Edición').removeClass('btn-outline-warning').addClass('btn-outline-secondary');
        $('#btnGuardarRapido').css('display', 'inline-flex');
    } else {
        $('#btnModoEdicion').html('<i class="bi bi-pencil-square me-1"></i> Edición Rápida').removeClass('btn-outline-secondary').addClass('btn-outline-warning');
        $('#btnGuardarRapido').hide();
    }

    // Redibujar la tabla para alternar entre Inputs y Texto
    tabla.rows().invalidate().draw(false);
}

function guardarEdicionRapida() {
    let promesas = [];

    $('.input-rapido-bulto').each(function(){
        let id = $(this).data('id');
        let bulto = $(this).val();
        let fecha = $(`.input-rapido-fecha[data-id="${id}"]`).val();

        let req = $.post('materiales.php?action=edicion_rapida', {
            id: id,
            precio_bulto: bulto,
            fecha_actualizacion: fecha
        });
        promesas.push(req);
    });

    $.when.apply($, promesas).done(function(){
        toggleModoEdicion();
        tabla.ajax.reload(null, false);
    });
}

window.abrirModal = function(modo) {
    $('#formMaterial')[0].reset();
    $('#mat_id').val('');
    $('#fecha_actualizacion').val('<?= date('Y-m-d') ?>');

    if(modo === 'NUEVO') {
        $('#modalMaterialLabel').html('<i class="bi bi-plus-circle me-2"></i> Registrar Nuevo Material');
        modalMaterialBS.show();
        
        setTimeout(() => {
            aplicarBuscadores();
            if (buscadoresTom['#proveedor']) {
                buscadoresTom['#proveedor'].setValue('');
            }
        }, 150);
    }
}

window.editar = function(id){
    let d = tabla.rows().data().toArray().find(x => x.id == id);
    if(!d) return;

    $('#formMaterial')[0].reset();
    $('#modalMaterialLabel').html('<i class="bi bi-pencil me-2"></i> Editar Material');
    
    $('#mat_id').val(d.id);
    $('#nombre').val(d.nombre);
    $('#peso_cantidad').val(d.peso_cantidad);
    $('#unidad_medida').val(d.unidad_medida);
    $('#precio_bulto').val(d.precio_bulto);
    $('#fecha_actualizacion').val(d.fecha_actualizacion || '<?= date('Y-m-d') ?>');

    modalMaterialBS.show();

    setTimeout(() => {
        aplicarBuscadores();
        if (buscadoresTom['#proveedor']) {
            buscadoresTom['#proveedor'].setValue(d.proveedor || '');
        }
    }, 150);
}

window.eliminar = function(id) {
    Swal.fire({
        title: '¿Eliminar material?',
        text: 'El registro se desactivará del sistema.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('materiales.php?action=eliminar', { id: id }, function(res) {
                if (res.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El material ha sido eliminado correctamente.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    tabla.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', 'No se pudo eliminar el material.', 'error');
                }
            }, 'json');
        }
    });
}

function procesarImportacionExcel(input) {
    if (!input.files || !input.files[0]) return;

    let archivo = input.files[0];
    let formData = new FormData();
    formData.append('archivo_excel', archivo);

    // Mensaje de confirmación previo
    if (!confirm(`¿Deseas importar el archivo "${archivo.name}"?`)) {
        input.value = ''; // Limpiar selección si cancela
        return;
    }

    $.ajax({
        url: 'procesar_excel.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            // Opcional: Deshabilitar el botón mientras procesa
            console.log("Subiendo e importando archivo...");
        },
        success: function(res) {
            if (res.success) {
                alert(res.message);
                tabla.ajax.reload(null, false); // Recargar DataTables sin perder la paginación
            } else {
                alert('Error al importar: ' + (res.message || 'Error desconocido'));
            }
        },
        error: function(xhr, status, error) {
            alert('Ocurrió un error en el servidor al procesar el archivo Excel.');
            console.error(error);
        },
        complete: function() {
            input.value = ''; // Limpiar el input para permitir volver a subir el mismo archivo si fuese necesario
        }
    });
}
</script>
<?php include $_SERVER['DOCUMENT_ROOT'].'/contable/modules/presupuestos/includes/modal_importar_materiales.php'; ?>
<?php include '../../includes/footer.php'; ?>
<script src="/contable/modules/presupuestos/assets/js/materiales_importar.js"></script>

