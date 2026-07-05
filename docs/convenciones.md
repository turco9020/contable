# Convenciones de Desarrollo

## Objetivo

Este documento define las reglas de desarrollo utilizadas en el proyecto Contable.

El objetivo es mantener un código ordenado, consistente y fácil de mantener.

---

# Estructura de módulos

Cada módulo debe tener su propia estructura.

Ejemplo:

modules/

    clientes/
        index.php

    proveedores/
        index.php

    gastos/
        index.php

    caja/
        index.php

La lógica de negocio no debe escribirse directamente en las vistas.

---

# AJAX

Todos los procesos AJAX deben ubicarse dentro de la carpeta:

ajax/

Ejemplos:

- clientes_ajax.php
- proveedores_ajax.php
- gastos_ajax.php

Cada archivo AJAX debe responder únicamente por su módulo.

---

# Base de datos

Toda conexión a la base de datos debe realizarse utilizando:

config/database.php

No deben crearse conexiones nuevas dentro de los módulos.

---

# Includes

Los elementos comunes del sistema deben ubicarse en:

includes/

Ejemplos:

- header.php
- navbar.php
- footer.php
- auth.php

---

# JavaScript

Cada módulo deberá tener su propio archivo JavaScript.

Ejemplo:

assets/js/

    clientes.js

    proveedores.js

    gastos.js

    caja.js

No mezclar la lógica de distintos módulos en un mismo archivo.

---

# CSS

Los estilos generales permanecerán en:

assets/css/

Si un módulo necesita estilos específicos, deberán agregarse en un archivo independiente.

---

# Nombres de archivos

Utilizar nombres en minúsculas.

Correcto:

clientes.php

gastos_ajax.php

usuarios.js

Incorrecto:

Clientes.php

GastosAjax.php

MiArchivo.PHP

---

# Nombres de variables

Utilizar nombres descriptivos.

Correcto:

$cliente

$proveedor

$totalGastos

$idUsuario

Evitar:

$a

$b

$x

$temp

---

# Base de datos

Las tablas utilizarán nombres en minúsculas.

Ejemplo:

clientes

proveedores

gastos

usuarios

Los campos utilizarán guiones bajos.

Ejemplo:

usuario_id

fecha_creacion

centro_costo_id

---

# Formato

- Utilizar indentación consistente (4 espacios o tabulación configurada en VS Code).
- Mantener el código ordenado y comentado cuando sea necesario.
- Evitar duplicar código.

---

# Git

Cada funcionalidad terminada deberá registrarse mediante un commit.

Ejemplos:

Agrega módulo Caja

Corrige edición de Gastos

Mejora exportación Excel

Evitar mensajes como:

cambios

arreglos

prueba

---

# Documentación

Cuando una modificación afecte la arquitectura o el funcionamiento del sistema, actualizar la documentación correspondiente.

Archivos:

README.md

CHANGELOG.md

ROADMAP.md

docs/

---

# Filosofía del proyecto

Priorizar:

- Claridad.
- Simplicidad.
- Reutilización.
- Escalabilidad.
- Seguridad.

Antes de incorporar una nueva funcionalidad, evaluar si puede reutilizar componentes existentes.