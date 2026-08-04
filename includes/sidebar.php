<!-- Cargamos los iconos con la URL robusta de cdnjs -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

<style>
/* ================= PERSONALIZACIÓN ESTÉTICA ================= */
.sidebar {
    background-color: #1e2229 !important; /* Gris oscuro mate */
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif !important;
    z-index: 1040;
    transition: transform 0.3s ease !important; /* Animación fluida al colapsar en móvil */
}

/* Título superior */
.sidebar h5 {
    color: #ffffff !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    font-size: 1.1rem !important;
}

/* Líneas divisorias sutiles */
.sidebar hr {
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    opacity: 1 !important;
}

/* Enlaces principales del menú */
.sidebar a {
    color: #b8c1cc !important; /* Gris elegante para el texto */
    font-size: 0.925rem !important;
    font-weight: 500 !important;
    padding: 0.7rem 1rem !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
    display: flex !important;
    align-items: center !important;
    text-decoration: none !important;
}

/* ÍCONOS TOTALMENTE HOMOGÉNEOS: Todos en el mismo tono gris */
.sidebar a i {
    color: #b8c1cc !important; /* Forzamos el mismo tono gris de operaciones/configuraciones */
    margin-right: 12px !important;
    font-size: 1.1rem !important;
    min-width: 20px !important;
    display: inline-block !important;
    vertical-align: middle !important;
}

/* Hover (Pasar el mouse) */
.sidebar a:hover {
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.05) !important;
}
.sidebar a:hover i {
    color: #ffffff !important; /* El ícono también se ilumina en blanco al pasar el mouse */
}

/* Ítem activo (Pantalla actual) */
.sidebar a.active {
    color: #ffffff !important;
    background-color: #2f3640 !important;
    font-weight: 600 !important;
    border-left: 4px solid #3498db !important;
    padding-left: calc(1rem - 4px) !important;
}
.sidebar a.active i {
    color: #ffffff !important;
}

/* Encabezados de secciones colapsables */
.menu-section > a {
    font-size: 0.8rem !important;
    letter-spacing: 0.8px !important;
    color: #8a97a6 !important;
    text-transform: uppercase !important;
    margin-top: 0.5rem !important;
}

/* Submenús internos */
.submenu a {
    font-size: 0.875rem !important;
    padding-left: 2.5rem !important;
    color: #a0aab5 !important;
}

/* ================= COMPORTAMIENTO RESPONSIVO EXCLUSIVO PARA MÓVILES ================= */
/* Botón Hamburguesa (Oculto en PC por defecto) */
.sidebar-toggler {
    display: none;
    position: fixed;
    top: 12px;
    left: 12px;
    z-index: 1050;
    background-color: #1e2229;
    color: #fff;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    cursor: pointer;
}

/* Capa oscura de fondo para cerrar el menú al hacer clic fuera */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0,0,0,0.4);
    z-index: 1030;
}

/* Cuando la pantalla mide menos de 992px aplicamos el colapso */
@media (max-width: 991.98px) {
    .sidebar {
        transform: translateX(-100%); /* Saca el sidebar de la pantalla hacia la izquierda */
    }
    .sidebar.mobile-open {
        transform: translateX(0); /* Lo vuelve a introducir cuando se abre */
    }
    .sidebar-toggler {
        display: block; /* Hacemos visible el botón de tres líneas */
    }
    .sidebar-overlay.mobile-open {
        display: block; /* Muestra el fondo oscuro */
    }
}
</style>

<!-- Elementos estructurales para móviles -->
<button class="sidebar-toggler" id="mobileSidebarBtn">
    <i class="bi bi-list fs-4"></i>
</button>
<div class="sidebar-overlay" id="mobileSidebarOverlay"></div>

<!-- Tu HTML original intacto con los accesos por roles corregidos -->
<div class="sidebar" id="sidebarMenu"> 

    <h5 class="text-center mt-3">RG CONTABLE</h5>
    <hr>

    <a href="/contable/index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>


    <!-- ACCESO A FACTURACIÓN COMPARTIDO -->
    <?php 
    $rol_actual = $_SESSION['rol'] ?? '';
    if (
        strcasecmp($rol_actual, 'admin') === 0 || 
        strcasecmp($rol_actual, 'contador') === 0 || 
        strcasecmp($rol_actual, 'arquitecto') === 0
    ): ?>
        <a href="/contable/modules/facturacion/"><i class="bi bi-receipt"></i> Facturación</a>
    <?php endif; ?>

    <a href="/contable/modules/gastos/"><i class="bi bi-cart3"></i> Gastos</a>

    <a href="/contable/modules/caja/"><i class="bi bi-cash-coin"></i> Cajas</a>

    <!-- ================= OPERACIONES ================= -->
    <div class="menu-section">

        <a href="#" onclick="toggleMenuOperaciones(event)">
            <i class="bi bi-bar-chart-steps"></i> Operaciones
        </a>

        <div id="menuOperaciones" class="submenu">

        <a href="/contable/modules/proveedores/" class="menu-link"><i class="bi bi-building-gear"></i> Proveedores</a>    

    <?php 
    $rol_actual = $_SESSION['rol'] ?? '';
    if (
        strcasecmp($rol_actual, 'admin') === 0 || 
        strcasecmp($rol_actual, 'contador') === 0 || 
        strcasecmp($rol_actual, 'arquitecto') === 0
    ): ?>
        <a href="/contable/modules/clientes/" class="menu-link"><i class="bi bi-person-badge"></i> Clientes</a>
        <a href="/contable/modules/cheques/" class="menu-link"><i class="bi bi-postage-heart"></i> Cheques</a>
        <?php endif; ?>
        </div>

    </div>

    <!-- ================= CONFIGURACIONES ================= -->
    <div class="menu-section">

        <a href="#" onclick="toggleMenuConfig(event)">
            <i class="bi bi-sliders"></i> Configuraciones
        </a>

        <div id="menuConfig" class="submenu">

    <?php $rol_actual = $_SESSION['rol'] ?? '';
         if (
        strcasecmp($rol_actual, 'admin') === 0 || 
        strcasecmp($rol_actual, 'contador') === 0 || 
        strcasecmp($rol_actual, 'arquitecto') === 0
            ): ?>
            <a href="/contable/modules/config/obras/" class="menu-link">Obras</a>
            <?php endif; ?>
            
            <?php if(esAdmin()): ?>
            <a href="/contable/modules/config/categorias/" class="menu-link">Categorías</a>
            <a href="/contable/modules/config/subcategorias/" class="menu-link">Subcategorías</a>
            <a href="/contable/modules/config/centros/" class="menu-link">Centros de costo</a>
            <a href="/contable/modules/config/medios_pago/" class="menu-link">Medios de Pago</a>
            <a href="/contable/modules/config/tipos_comprobante/" class="menu-link">Tipos de Comprobante</a>
            <a href="/contable/modules/config/retenciones/" class="menu-link">Tipos de Retenciones</a>
            <a href="/contable/modules/config/cajas/" class="menu-link">Cajas</a>
                <hr>
                <a href="/contable/modules/usuarios/" class="menu-link"><i class="bi bi-people"></i> Usuarios</a>
                <a href="/contable/modules/roles/" class="menu-link"><i class="bi bi-shield-lock"></i> Roles</a>
            <?php endif; ?>

        </div>

    </div>

    <hr style="margin-top: auto;">

    <a href="#" onclick="toggleDark()"><i class="bi bi-moon-stars"></i> Modo oscuro</a>

    <hr>

    <!-- SALIR -->
    <a href="/contable/auth/logout.php" style="color:#bbb;">
        <i class="bi bi-box-arrow-right text-danger"></i> Cerrar sesión
    </a>

</div>

<script>
function toggleDark(){
    fetch('/contable/ajax/toggle_dark.php')
    .then(()=>location.reload());
}
</script>

<script>
// ================= OPERACIONES =================
function toggleMenuOperaciones(e){
    e.preventDefault();
    let menuOperaciones = document.getElementById('menuOperaciones');
    let menuConfig = document.getElementById('menuConfig');
    
    if (!menuOperaciones.classList.contains('show')) {
        menuOperaciones.classList.add('show');
        menuConfig.classList.remove('show');
        localStorage.setItem('menuOperaciones', 'open');
        localStorage.setItem('menuConfig', 'closed');
    } else {
        menuOperaciones.classList.remove('show');
        localStorage.setItem('menuOperaciones', 'closed');
    }
}

// ================= CONFIG =================
function toggleMenuConfig(e){
    e.preventDefault();
    let menuOperaciones = document.getElementById('menuOperaciones');
    let menuConfig = document.getElementById('menuConfig');
    
    if (!menuConfig.classList.contains('show')) {
        menuConfig.classList.add('show');
        menuOperaciones.classList.remove('show');
        localStorage.setItem('menuConfig', 'open');
        localStorage.setItem('menuOperaciones', 'closed');
    } else {
        menuConfig.classList.remove('show');
        localStorage.setItem('menuConfig', 'closed');
    }
}

// ================= INIT =================
document.addEventListener('DOMContentLoaded', function(){
    // Interactividad responsiva del menú móvil
    const sidebar = document.getElementById('sidebarMenu');
    const mobileBtn = document.getElementById('mobileSidebarBtn');
    const overlay = document.getElementById('mobileSidebarOverlay');

    function toggleMobileSidebar() {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('mobile-open');
    }

    if(mobileBtn && overlay) {
        mobileBtn.addEventListener('click', toggleMobileSidebar);
        overlay.addEventListener('click', toggleMobileSidebar);
    }

    // Restaurar estado de submenús
    let menuOperaciones = document.getElementById('menuOperaciones');
    let menuConfig = document.getElementById('menuConfig');

    if(localStorage.getItem('menuOperaciones') === 'open'){
        menuOperaciones.classList.add('show');
    }
    if(localStorage.getItem('menuConfig') === 'open'){
        menuConfig.classList.add('show');
    }

    let url = window.location.pathname;
    document.querySelectorAll('.menu-link, .sidebar > a').forEach(link => {
        let href = link.getAttribute('href');
        if(href && href !== '/contable/index.php' && url.includes(href)){
            link.classList.add('active');
            if(link.closest('#menuOperaciones')){
                menuOperaciones.classList.add('show');
            }
            if(link.closest('#menuConfig')){
                menuConfig.classList.add('show');
            }
        } else if (href === '/contable/index.php' && url === href) {
            link.classList.add('active');
        }
    });
});
</script>