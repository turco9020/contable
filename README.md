# Sistema Contable

Sistema de gestión contable desarrollado en **PHP**, **MySQL**, **Bootstrap** y **DataTables**.

Actualmente el sistema permite administrar distintos módulos contables con una interfaz web moderna y una arquitectura modular, preparada para seguir creciendo.

---

# Tecnologías

* PHP 8.x
* MySQL
* Bootstrap 5
* jQuery
* DataTables
* Select2
* SweetAlert2
* Git
* GitHub

---

# Estructura del proyecto

```text
contable/
│
├── ajax/
├── assets/
├── config/
├── includes/
├── modules/
├── uploads/
├── index.php
└── README.md
```

---

# Módulos

Actualmente el sistema contempla los siguientes módulos:

* Clientes
* Proveedores
* Gastos
* Caja
* Reportes
* Configuración
* Usuarios

---

# Funcionalidades

* Login de usuarios
* Gestión de clientes
* Gestión de proveedores
* Administración de gastos
* Control de caja
* Reportes
* Exportación a Excel
* Impresión
* Buscadores con DataTables
* Select2 para búsquedas rápidas

---

# Requisitos

* PHP 8.x
* MySQL
* Apache (XAMPP recomendado)

---

# Instalación

1. Clonar el repositorio.

```bash
git clone https://github.com/turco9020/contable.git
```

2. Copiar la carpeta dentro de:

```text
C:\xampp\htdocs\
```

3. Crear la base de datos en MySQL.

4. Importar el archivo SQL correspondiente.

5. Configurar la conexión en:

```text
config/database.php
```

6. Iniciar Apache y MySQL desde XAMPP.

7. Abrir el navegador:

```text
http://localhost/contable
```

---

# Flujo de desarrollo

El proyecto utiliza Git y GitHub.

* `main` → versión estable.
* `develop` → desarrollo.

Todo el desarrollo se realiza sobre la rama **develop**.

---

# Estado del proyecto

En desarrollo activo.

Las nuevas funcionalidades se incorporan progresivamente y se validan antes de integrarse a la rama principal.

---

# Logica de Usuarios y Roles

Con la lógica que implementamos en los cuatro archivos (movimientos_caja, gastos, facturacion y cheques), la información queda visible estrictamente por usuario individual, no por grupo de rol.

Te lo detallo de forma simple para que veas el comportamiento exacto de cada perfil:

Los Administradores (admin) y Contadores (contador): Tienen acceso total. Ven absolutamente todo lo que cargan ellos mismos y lo que carga cualquier otro usuario del sistema.

Los Operadores / Usuarios comunes (user o cualquier otro): Tienen acceso restringido a su propio ID. Esto significa que un operador NO puede ver lo que cargó otro operador. Cada uno trabaja en su propio "silo" de información.

¿Cómo funciona a nivel de código?
Cuando un operador entra a listar los gastos o las facturas, el backend ejecuta esta validación que agregamos:

PHP
if (strcasecmp($rol, 'admin') !== 0 && strcasecmp($rol, 'contador') !== 0) {
    $where .= " AND usuario_id = $usuario";
}
Al obligar al sistema a filtrar por usuario_id = $_SESSION['id'], la base de datos automáticamente descarta los registros de los demás compañeros de trabajo, aunque tengan su mismo rol.

TODOS LOS ACCESOS A LOS MENUES LO RESTRINGIMOS DESDE SIDEBAR

# Autor

Desarrollado por YAIR CORZO.
