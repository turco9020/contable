<?php
session_start();
require_once '../../config/database.php';

$db_conn = $conn ?? $conexion ?? $db ?? null;

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
        $categoria = $_GET['categoria'] ?? '';
        $data = [];
        if ($db_conn && !empty($categoria)) {
            $stmt = $db_conn->prepare("SELECT * FROM presupuesto_equipos WHERE activo = 1 AND categoria = ? ORDER BY id DESC");
            $stmt->bind_param("s", $categoria);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res) {
                $data = $res->fetch_all(MYSQLI_ASSOC);
            }
        }
        echo json_encode(['data' => $data]);
        exit;
    }

    if ($action === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id                  = intval($_POST['id'] ?? 0);
        $categoria           = trim($_POST['categoria'] ?? '');
        $nombre              = trim($_POST['nombre']);
        $unidad_medida       = trim($_POST['unidad_medida']);
        $precio              = floatval($_POST['precio']);
        $proveedor           = trim($_POST['proveedor'] ?? '');
        $observaciones       = trim($_POST['observaciones'] ?? '');
        $fecha_actualizacion = !empty($_POST['fecha_actualizacion']) ? $_POST['fecha_actualizacion'] : date('Y-m-d');

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_equipos SET 
                categoria=?, nombre=?, unidad_medida=?, precio=?, proveedor=?, observaciones=?, fecha_actualizacion=?, usuario_nombre=? 
                WHERE id=?");
            $stmt->bind_param("sssdssssi", $categoria, $nombre, $unidad_medida, $precio, $proveedor, $observaciones, $fecha_actualizacion, $usuario_actual, $id);
        } else {
            $stmt = $db_conn->prepare("INSERT INTO presupuesto_equipos 
                (categoria, nombre, unidad_medida, precio, proveedor, observaciones, fecha_actualizacion, usuario_nombre) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdssss", $categoria, $nombre, $unidad_medida, $precio, $proveedor, $observaciones, $fecha_actualizacion, $usuario_actual);
        }

        $ok = $stmt ? $stmt->execute() : false;
        echo json_encode(['success' => $ok]);
        exit;
    }

    if ($action === 'edicion_rapida' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $id        = intval($_POST['id'] ?? 0);
        $precio    = floatval($_POST['precio']);
        $fecha_act = !empty($_POST['fecha_actualizacion']) ? $_POST['fecha_actualizacion'] : date('Y-m-d');

        if ($id > 0) {
            $stmt = $db_conn->prepare("UPDATE presupuesto_equipos SET 
                precio=?, fecha_actualizacion=?, usuario_nombre=? 
                WHERE id=?");
            $stmt->bind_param("dssi", $precio, $fecha_act, $usuario_actual, $id);
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
            $stmt = $db_conn->prepare("UPDATE presupuesto_equipos SET activo = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

echo "<script>localStorage.setItem('menuOperaciones', 'closed'); localStorage.setItem('menuConfig', 'closed');</script>";

include '../../includes/header.php';
include '../../includes/sidebar.php';
?>

<div class="content">

    <!-- CABECERA DEL MÓDULO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-truck text-secondary me-2"></i> Gestión de Equipos y Alquileres
        </h4>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-warning text-dark fw-semibold d-flex align-items-center" id="btnModoEdicion" onclick="toggleModoEdicion()">
                <i class="bi bi-pencil-square me-1"></i> Edición Rápida
            </button>
            <button class="btn btn-success align-items-center" id="btnGuardarRapido" onclick="guardarEdicionRapida()" style="display:none;">
                <i class="bi bi-check-lg me-1"></i> Guardar Cambios
            </button>
            <button class="btn btn-dark d-flex align-items-center" onclick="abrirModal('NUEVO')">
                <i class="bi bi-plus-circle me-2"></i> Nuevo Registro
            </button>
        </div>
    </div>

    <!-- Pestañas de Navegación de Equipos y Alquileres -->
    <ul class="nav nav-tabs mb-3" id="tabEquipos" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-semibold text-dark" id="tab-herramientas" data-bs-toggle="tab" data-categoria="MAQUINAS Y HERRAMIENTAS" type="button" role="tab">
                <i class="bi bi-tools me-1 text-secondary"></i> MÁQUINAS Y HERRAMIENTAS
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="tab-equipos" data-bs-toggle="tab" data-categoria="EQUIPOS" type="button" role="tab">
                <i class="bi bi-truck me-1 text-secondary"></i> EQUIPOS
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-semibold text-dark" id="tab-obra" data-bs-toggle="tab" data-categoria="EQUIPAMIENTO DE OBRA" type="button" role="tab">
                <i class="bi bi-building me-1 text-secondary"></i> EQUIPAMIENTO DE OBRA
            </button>
        </li>
    </ul>

    <!-- TABLA UNIFICADA PARA LAS PESTAÑAS -->
    <div class="card shadow-sm border-0 p-3">
        <div class="table-responsive">
            <table id="tablaEquipos" class="table table-bordered table-striped w-100 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center text-nowrap" style="width: 5%;">ID</th>
                        <th class="text-nowrap">Nombre</th>
                        <th class="text-center text-nowrap" style="width: 10%;">Unidad</th>
                        <th class="text-end text-nowrap" style="width: 15%;">Precio(s/IVA)</th>
                        <th class="text-nowrap" style="width: 13%;">Proveedor</th>
                        <th class="text-nowrap" style="width: 17%;">Observaciones</th>
                        <th class="text-center text-nowrap" style="width: 12%;">Fecha act.</th>
                        <th class="text-center text-nowrap" style="width: 10%;">Usuario</th>
                        <th class="text-center text-nowrap" style="width: 8%;">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

</div>

<!-- MODAL UNIFICADO (NUEVO / EDITAR) -->
<div class="modal fade" id="modalEquipo" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalEquipoLabel"><i class="bi bi-truck me-2"></i>Equipo / Alquiler</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formEquipo">
                <div class="modal-body bg-light">
                    <input type="hidden" name="id" id="eq_id" value="">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Pestaña / Categoría *</label>
                            <select name="categoria" id="categoria" class="form-select" required>
                                <option value="MAQUINAS Y HERRAMIENTAS">MAQUINAS Y HERRAMIENTAS</option>
                                <option value="EQUIPOS">EQUIPOS</option>
                                <option value="EQUIPAMIENTO DE OBRA">EQUIPAMIENTO DE OBRA</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nombre *</label>
                            <input name="nombre" id="nombre" class="form-control" required placeholder="Ej: Grupo Electrógeno / Andamio Tubular">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Unidad Computable *</label>
                            <select name="unidad_medida" id="unidad_medida" class="form-select" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="DIA">DIA</option>
                                <option value="HR">HR</option>
                                <option value="MES">MES</option>
                                <option value="OTRO">OTRO</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Precio (s/ IVA) *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="precio" id="precio" class="form-control text-end" required placeholder="0.00">
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

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Observaciones</label>
                            <input name="observaciones" id="observaciones" class="form-control" placeholder="Detalles, estado o especificaciones adicionales...">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Fecha de Actualización</label>
                            <input type="date" name="fecha_actualizacion" id="fecha_actualizacion" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-dark" id="btnGuardarEquipo">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let modalEquipoBS;
let tabla;
let modoEdicionActivo = false;
let buscadoresTom = {};
let categoriaActiva = 'MAQUINAS Y HERRAMIENTAS';

function renderMoneda(val) {
    if (!val || val == 0) return '$ 0,00';
    let num = parseFloat(val);
    if (isNaN(num)) return '$ 0,00';

    return '$ ' + num.toLocaleString('es-AR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

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
    modalEquipoBS = new bootstrap.Modal(document.getElementById('modalEquipo'));

    tabla = $('#tablaEquipos').DataTable({
        ajax: {
            url: 'equipos.php?action=listar',
            data: function(d) {
                d.categoria = categoriaActiva;
            },
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
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: 'print',
                text: ' Imprimir',
                className: 'btn btn-secondary btn-sm',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6, 7] }
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
                data: 'nombre',
                render: d => `<span class="text-dark" style="font-size: 0.85rem;">${d}</span>`
            },
            { 
                data: 'unidad_medida',
                className: 'text-center',
                render: d => `<span class="badge bg-light text-dark border fw-normal">${d || ''}</span>`
            },
            { 
                data: 'precio',
                className: 'text-end',
                render: function(d, type, row) {
                    if (modoEdicionActivo) {
                        let val = parseFloat(d || 0).toFixed(2);
                        return `<div class="input-group input-group-sm">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control text-end input-rapido-precio" data-id="${row.id}" value="${val}">
                        </div>`;
                    }
                    return `<span class="fw-bold">${renderMoneda(d)}</span>`;
                }
            }, 
            { 
                data: 'proveedor',
                render: d => d ? d : '<span class="text-muted">-</span>'
            },
            { 
                data: 'observaciones',
                render: d => d ? `<span class="text-muted small">${d}</span>` : '<span class="text-muted">-</span>'
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
                            <button class="btn btn-sm btn-outline-primary" title="Editar" onclick="editar(${d.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="eliminar(${d.id})">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ]
    });

    // CAMBIO DE PESTAÑAS (TABS)
    $('#tabEquipos button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        categoriaActiva = $(e.target).data('categoria');
        tabla.ajax.reload();
    });

    // GUARDADO VIA AJAX
    $('#formEquipo').submit(function(e){
        e.preventDefault();
        $.ajax({
            url: 'equipos.php?action=guardar',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    tabla.ajax.reload(null, false);
                    modalEquipoBS.hide();
                } else {
                    alert('Error al guardar el equipo.');
                }
            }
        });
    });
});

function toggleModoEdicion() {
    modoEdicionActivo = !modoEdicionActivo;

    if (modoEdicionActivo) {
        $('#btnModoEdicion').html('<i class="bi bi-x-circle me-1"></i> Cancelar Edición').removeClass('btn-outline-warning').addClass('btn-outline-secondary');
        $('#btnGuardarRapido').css('display', 'inline-flex');
    } else {
        $('#btnModoEdicion').html('<i class="bi bi-pencil-square me-1"></i> Edición Rápida').removeClass('btn-outline-secondary').addClass('btn-outline-warning');
        $('#btnGuardarRapido').hide();
    }

    tabla.rows().invalidate().draw(false);
}

function guardarEdicionRapida() {
    let promesas = [];

    $('.input-rapido-precio').each(function(){
        let id = $(this).data('id');
        let precio = $(this).val();
        let fecha = $(`.input-rapido-fecha[data-id="${id}"]`).val();

        let req = $.post('equipos.php?action=edicion_rapida', {
            id: id,
            precio: precio,
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
    $('#formEquipo')[0].reset();
    $('#eq_id').val('');
    $('#categoria').val(categoriaActiva);
    $('#observaciones').val('');
    $('#fecha_actualizacion').val('<?= date('Y-m-d') ?>');

    if(modo === 'NUEVO') {
        $('#modalEquipoLabel').html('<i class="bi bi-plus-circle me-2"></i> Registrar Nuevo Equipo');
        modalEquipoBS.show();
        
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

    $('#formEquipo')[0].reset();
    $('#modalEquipoLabel').html('<i class="bi bi-pencil me-2"></i> Editar Equipo');
    
    $('#eq_id').val(d.id);
    $('#categoria').val(d.categoria);
    $('#nombre').val(d.nombre);
    $('#unidad_medida').val(d.unidad_medida);
    $('#precio').val(d.precio);
    $('#observaciones').val(d.observaciones || '');
    $('#fecha_actualizacion').val(d.fecha_actualizacion || '<?= date('Y-m-d') ?>');

    modalEquipoBS.show();

    setTimeout(() => {
        aplicarBuscadores();
        if (buscadoresTom['#proveedor']) {
            buscadoresTom['#proveedor'].setValue(d.proveedor || '');
        }
    }, 150);
}

window.eliminar = function(id) {
    Swal.fire({
        title: '¿Eliminar registro?',
        text: 'El registro se desactivará del sistema.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash me-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('equipos.php?action=eliminar', { id: id }, function(res) {
                if (res.success) {
                    Swal.fire({
                        title: '¡Eliminado!',
                        text: 'El registro ha sido eliminado correctamente.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    tabla.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                }
            }, 'json');
        }
    });
}
</script>

<?php include '../../includes/footer.php'; ?>