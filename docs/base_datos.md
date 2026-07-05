# Base de Datos

## Objetivo

Este documento describe la estructura general de la base de datos del sistema Contable.

La base de datos fue diseñada para ser modular y permitir el crecimiento del sistema sin modificar la estructura principal.

---

# Motor

- MySQL

---

# Tablas principales

## usuarios

Almacena los usuarios del sistema.

Campos principales:

- id
- nombre
- usuario
- password
- rol
- activo

---

## clientes

Información de clientes.

Campos principales:

- id
- nombre
- cuit
- condicion_fiscal
- direccion
- localidad
- provincia
- cp
- whatsapp
- telefono
- contacto
- observaciones
- usuario_id

---

## proveedores

Información de proveedores.

Campos principales:

- id
- nombre
- cuit
- condicion_fiscal
- direccion
- localidad
- provincia
- cp
- whatsapp
- telefono
- contacto
- producto_servicio
- observaciones
- usuario_id

---

## gastos

Registro de gastos.

Campos principales:

- id
- fecha
- proveedor_id
- centro_costo_id
- categoria_id
- subcategoria_id
- tipo_comprobante
- numero_comprobante
- detalle
- neto
- iva
- percepciones
- total
- medio_pago
- usuario_id

---

## caja

Movimientos de caja.

Campos principales:

- id
- fecha
- tipo
- concepto
- ingreso
- egreso
- saldo
- usuario_id

---

## centros_costos

Centros de costos utilizados por el módulo Gastos.

---

## categorias

Categorías de gastos.

---

## subcategorias

Subcategorías asociadas a una categoría.

---

# Relaciones

clientes
↓

ventas (futuro)

proveedores
↓

gastos

categorias
↓

subcategorias

gastos
↓

caja (opcional)

usuarios
↓

todos los módulos

---

# Convenciones

Todas las tablas poseen un campo:

- id

Cuando corresponde también incluyen:

- usuario_id
- fecha_creacion
- fecha_actualizacion

---

# Futuras tablas

- bancos
- cuentas_corrientes
- presupuestos
- auditoria
- movimientos_bancarios
- configuracion