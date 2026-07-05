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

# Autor

Desarrollado por YAIR CORZO.
