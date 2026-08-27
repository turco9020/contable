<!-- Cargamos los iconos con la URL robusta de cdnjs -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="/contable/assets/img/favicon.ico">

<style>
/* ================= PERSONALIZACIÓN ESTÉTICA ================= */
.sidebar {
    background-color: #1e2229 !important;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif !important;
    z-index: 1040;
    transition: transform 0.3s ease !important;

    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    bottom: 0 !important;
    width: 260px !important;
    height: 100vh !important;
    overflow-y: auto !important;
    display: flex !important;
    flex-direction: column !important;
    padding: 0 !important;             
    margin: 0 !important;              
    padding-bottom: 2rem !important;
}

/* Personalización de la barra de desplazamiento */
.sidebar::-webkit-scrollbar {
    width: 6px;
}
.sidebar::-webkit-scrollbar-track {
    background: #1e2229;
}
.sidebar::-webkit-scrollbar-thumb {
    background: #2f3640;
    border-radius: 3px;
}
.sidebar::-webkit-scrollbar-thumb:hover {
    background: #4a5568;
}

/* Logo - Ajustado para eliminar espacios sobrantes */
.sidebar-logo-container {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding: 10px 0 !important; /* Espaciado interno controlado y simétrico */
    margin: 0 !important;
    flex-shrink: 0 !important;
}

.sidebar-logo {
    width: 190px !important;     /* Reducido para mejor proporción en sidebar de 260px */
    height: auto !important;    /* Mantiene la proporción original sin estirar */
    max-height: 110px !important; /* Límite estricto de altura */
    object-fit: contain !important;
    display: block !important;
    margin: 0 !important;
    padding: 0 !important;
    border: 0 !important;
}

/* HR - PEGADO AL LOGO */
.sidebar hr {
    border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
    opacity: 1 !important;
    margin: 0 !important;
    padding: 0 !important;
    flex-shrink: 0 !important;
}

/* Enlaces principales del menú */
.sidebar a {
    color: #b8c1cc !important;
    font-size: 0.925rem !important;
    font-weight: 500 !important;
    padding: 0.7rem 1rem !important;
    border-radius: 6px !important;
    transition: all 0.2s ease !important;
    display: flex !important;
    align-items: center !important;
    text-decoration: none !important;
}

.sidebar a i {
    color: #b8c1cc !important;
    margin-right: 12px !important;
    font-size: 1.1rem !important;
    min-width: 20px !important;
    display: inline-block !important;
    vertical-align: middle !important;
}

.sidebar a:hover {
    color: #ffffff !important;
    background-color: rgba(255, 255, 255, 0.05) !important;
}
.sidebar a:hover i {
    color: #ffffff !important;
}

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

.menu-section > a {
    font-size: 0.8rem !important;
    letter-spacing: 0.8px !important;
    color: #8a97a6 !important;
    text-transform: uppercase !important;
    margin-top: 0.5rem !important;
}

.submenu a {
    font-size: 0.875rem !important;
    padding-left: 2.5rem !important;
    color: #a0aab5 !important;
}

/* ================= COMPORTAMIENTO RESPONSIVO ================= */
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

@media (min-width: 992px) {
    .content {
        margin-left: 260px !important;
        width: calc(100% - 260px) !important;
        transition: margin-left 0.3s ease !important;
    }
}

@media (max-width: 991.98px) {
    .content {
        margin-left: 0 !important;
        width: 100% !important;
    }
    .sidebar {
        transform: translateX(-100%);
    }
    .sidebar.mobile-open {
        transform: translateX(0);
    }
    .sidebar-toggler {
        display: block;
    }
    .sidebar-overlay.mobile-open {
        display: block;
    }
}
</style>

<!-- Elementos estructurales para móviles -->
<button class="sidebar-toggler" id="mobileSidebarBtn">
    <i class="bi bi-list fs-4"></i>
</button>
<div class="sidebar-overlay" id="mobileSidebarOverlay"></div>

<div class="sidebar" id="sidebarMenu"> 

    <!-- Logo ajustado -->
    <div class="sidebar-logo-container">
        <img src="/contable/assets/img/logo.png" alt="RG Contable" class="sidebar-logo">
    </div>
    <hr>

    <a href="/contable/index.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
    <a href="/contable/modules/tareas/"><i class="bi bi-kanban"></i> Tareas</a>

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

    <?php if (esAdmin()): ?>
        <a href="/contable/modules/reportes/"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a>
    <?php endif; ?>

    <?php 
    $rol_actual = $_SESSION['rol'] ?? '';
    if (
        strcasecmp($rol_actual, 'admin') === 0 || 
        strcasecmp($rol_actual, 'contador') === 0 
    ): ?>
        <a href="/contable/modules/vencimientos/"><i class="bi bi-calendar-date"></i> Vencimientos</a>
    <?php endif; ?>

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
    <a href="/contable/auth/logout.php" style="color:#bbb;">
        <i class="bi bi-box-arrow-right text-danger"></i> Cerrar sesión
    </a>

</div>

<script>
function toggleDark(){
    fetch('/contable/ajax/toggle_dark.php')
    .then(()=>location.reload());
}

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

document.addEventListener('DOMContentLoaded', function(){
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