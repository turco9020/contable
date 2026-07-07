<div class="sidebar"> 

    <h5 class="text-center mt-3">Contable</h5>
    <hr>

    <a href="/contable/index.php">🏠 Dashboard</a>

    <?php if(esAdmin()): ?>
        <a href="/contable/modules/facturacion/">🧾 Ingresos</a>
    <?php endif; ?>

    <a href="/contable/modules/gastos/">🛒 Gastos</a>

    <a href="/contable/modules/caja/">💰 Cajas</a>

    <!-- ================= OPERACIONES ================= -->
    <div class="menu-section">

        <a href="#" onclick="toggleMenuOperaciones(event)">
            📊 OPERACIONES
        </a>

        <div id="menuOperaciones" class="submenu">

            <a href="/contable/modules/clientes/" class="menu-link">👤 Clientes</a>
            <a href="/contable/modules/proveedores/" class="menu-link">🏭 Proveedores</a>
            <a href="/contable/modules/cheques/" class="menu-link">💸 Cheques</a>

        </div>

    </div>

    <!-- ================= CONFIGURACIONES ================= -->
    <div class="menu-section">

        <a href="#" onclick="toggleMenuConfig(event)" style="font-weight:bold;">
            ⚙️ CONFIGURACIONES
        </a>

        <div id="menuConfig" class="submenu">

            <a href="/contable/modules/config/categorias/" class="menu-link">Categorías</a>
            <a href="/contable/modules/config/subcategorias/" class="menu-link">Subcategorías</a>
            <a href="/contable/modules/config/centros/" class="menu-link">Centros de costo</a>
            <a href="/contable/modules/config/obras/" class="menu-link">Obras</a>
            <a href="/contable/modules/config/medios_pago/" class="menu-link">Medios de Pago</a>
            <a href="/contable/modules/config/tipos_comprobante/" class="menu-link">Tipos de Comprobante</a>
            <a href="/contable/modules/config/cajas/" class="menu-link">Cajas</a>
            <hr>

            <?php if(esAdmin()): ?>
                <a href="/contable/modules/usuarios/" class="menu-link">👥 Usuarios</a>
                <a href="/contable/modules/roles/" class="menu-link">🔐 Roles</a>
            <?php endif; ?>

        </div>

    </div>

    <hr>

    <a href="#" onclick="toggleDark()">🌙 Modo oscuro</a>

    <hr>

    <!-- SALIR -->
    <a href="/contable/auth/logout.php" style="color:#bbb;">
        🚪 Cerrar sesión
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

    let menu = document.getElementById('menuOperaciones');
    let abierto = menu.classList.toggle('show');

    localStorage.setItem('menuOperaciones', abierto ? 'open' : 'closed');
}

// ================= CONFIG =================
function toggleMenuConfig(e){
    e.preventDefault();

    let menu = document.getElementById('menuConfig');
    let abierto = menu.classList.toggle('show');

    localStorage.setItem('menuConfig', abierto ? 'open' : 'closed');
}

// ================= INIT =================
document.addEventListener('DOMContentLoaded', function(){

    let menuOperaciones = document.getElementById('menuOperaciones');
    let menuConfig = document.getElementById('menuConfig');

    // restaurar estado
    if(localStorage.getItem('menuOperaciones') === 'open'){
        menuOperaciones.classList.add('show');
    }

    if(localStorage.getItem('menuConfig') === 'open'){
        menuConfig.classList.add('show');
    }

    let url = window.location.pathname;

    document.querySelectorAll('.menu-link').forEach(link => {

        if(url.includes(link.getAttribute('href'))){

            link.classList.add('active');

            // abrir el menú correcto
            if(link.closest('#menuOperaciones')){
                menuOperaciones.classList.add('show');
            }

            if(link.closest('#menuConfig')){
                menuConfig.classList.add('show');
            }
        }
    });

});
</script>