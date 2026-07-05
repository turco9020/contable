# Arquitectura del Sistema

## Objetivo

El sistema Contable fue desarrollado con una arquitectura modular para facilitar el mantenimiento, la escalabilidad y la incorporación de nuevos módulos.

Actualmente está construido utilizando PHP puro, MySQL, Bootstrap y JavaScript, evitando dependencias innecesarias.

---

# Tecnologías

- PHP 8.x
- MySQL
- Bootstrap 5
- jQuery
- DataTables
- Select2
- SweetAlert2
- Git
- GitHub

---

# Estructura de carpetas

```text
contable/
│
├── ajax/
│   ├── clientes_ajax.php
│   ├── proveedores_ajax.php
│   ├── gastos_ajax.php
│   └── ...
│
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
│
├── config/
│   └── database.php
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   ├── navbar.php
│   └── auth.php
│
├── modules/
│   ├── clientes/
│   ├── proveedores/
│   ├── gastos/
│   ├── caja/
│   └── ...
│
├── uploads/
│
├── docs/
│
├── README.md
├── CHANGELOG.md
├── ROADMAP.md
└── index.php
```

---

# Arquitectura

Cada módulo se divide en tres capas principales:

- Interfaz (PHP + Bootstrap)
- Lógica AJAX
- Base de datos (MySQL)

La comunicación entre la interfaz y la base de datos se realiza mediante peticiones AJAX.

---

# Objetivos

- Código modular.
- Reutilización.
- Fácil mantenimiento.
- Escalabilidad.
- Bajo acoplamiento entre módulos.

---

# Flujo general

Usuario

↓

Pantalla PHP

↓

JavaScript

↓

AJAX

↓

Base de Datos

↓

Respuesta JSON

↓

Actualización de la interfaz