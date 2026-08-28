<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-dark mb-0">👋 Bienvenid@, <?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></h4>
    <span class="badge bg-secondary px-3 py-2">Perfil: Operador</span>
</div>

<!-- Accesos Directos a Módulos Clave -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 p-3 text-center h-100">
            <div class="my-2">
                <i class="bi bi-receipt text-primary display-6"></i>
            </div>
            <h6 class="fw-bold">Carga de Gastos</h6>
            <p class="text-muted small">Registra nuevos comprobantes y gastos de la jornada.</p>
            <a href="/contable/modules/gastos/" class="btn btn-primary btn-sm px-4 mt-auto">Ingresar Gasto</a>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 p-3 text-center h-100">
            <div class="my-2">
                <i class="bi bi-truck text-info display-6"></i>
            </div>
            <h6 class="fw-bold">Proveedores</h6>
            <p class="text-muted small">Consulta y gestiona la lista de proveedores registrados.</p>
            <a href="/contable/modules/proveedores/" class="btn btn-info text-white btn-sm px-4 mt-auto">Ver Proveedores</a>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0 p-3 text-center h-100">
            <div class="my-2">
                <i class="bi bi-folder-plus text-warning display-6"></i>
            </div>
            <h6 class="fw-bold">Gestión de Archivos</h6>
            <p class="text-muted small">Sube documentación o comprobantes adjuntos a tareas.</p>
            <a href="/contable/modules/tareas/" class="btn btn-warning btn-sm px-4 mt-auto">Ir a Tareas</a>
        </div>
    </div>
</div>