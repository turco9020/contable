# Roles y Permisos

## Objetivo

Este documento describe los roles del sistema y los permisos asociados a cada uno.

El objetivo es garantizar que cada usuario acceda únicamente a la información y funcionalidades que le corresponden.

---

# Roles

Actualmente el sistema contempla los siguientes roles:

- FULL
- ADMIN
- INSPECCION
- CUADRILLA
- LECTURA

---

# Descripción de los roles

## FULL

Acceso total al sistema.

Permisos:

- Administración completa.
- Configuración.
- Usuarios.
- Clientes.
- Proveedores.
- Gastos.
- Caja.
- Reportes.
- Auditoría.
- Gestión de permisos.

---

## ADMIN

Administrador operativo.

Permisos:

- Clientes.
- Proveedores.
- Gastos.
- Caja.
- Reportes.
- Configuración operativa.

No puede modificar configuraciones críticas del sistema.

---

## INSPECCION

Usuario destinado a tareas de inspección y seguimiento.

Permisos:

- Consultar información.
- Registrar inspecciones.
- Actualizar estados de trabajos.
- Sin acceso a configuración.
- Sin acceso a administración de usuarios.

---

## CUADRILLA

Usuario operativo.

Permisos:

- Consultar únicamente los trabajos asignados.
- Registrar avances.
- Subir imágenes.
- Actualizar estados autorizados.

No puede visualizar información administrativa.

---

## LECTURA

Usuario de consulta.

Permisos:

- Solo lectura.
- Sin altas.
- Sin modificaciones.
- Sin eliminaciones.

---

# Permisos generales

Los permisos podrán definirse por módulo.

Ejemplo:

- Clientes
- Proveedores
- Gastos
- Caja
- Reportes
- Configuración
- Usuarios

Cada módulo podrá definir:

- Ver
- Crear
- Editar
- Eliminar
- Exportar
- Imprimir

---

# Restricciones

Como regla general:

- Cada usuario visualizará únicamente la información que le corresponda.
- Los registros podrán asociarse al usuario mediante el campo `usuario_id`.
- Las acciones críticas requerirán permisos específicos.

---

# Futuras mejoras

Se prevé implementar un sistema de permisos más granular, permitiendo configurar permisos por:

- Módulo.
- Acción.
- Usuario.
- Rol.

Esto permitirá adaptar el sistema a diferentes tipos de organizaciones.