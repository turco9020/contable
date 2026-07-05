# Instalación del Sistema

## Requisitos

Antes de comenzar, verificar que el equipo tenga instalado:

- XAMPP
- PHP 8.x o superior
- MySQL
- Apache
- Git
- Visual Studio Code (recomendado)

---

# Clonar el repositorio

Abrir una terminal y ejecutar:

```bash
git clone https://github.com/turco9020/contable.git
```

O descargar el proyecto desde GitHub como archivo ZIP.

---

# Ubicación del proyecto

Copiar la carpeta dentro de:

```text
C:\xampp\htdocs\
```

La estructura deberá quedar:

```text
C:\xampp\htdocs\contable
```

---

# Base de datos

1. Iniciar Apache y MySQL desde XAMPP.
2. Abrir phpMyAdmin.
3. Crear una nueva base de datos.
4. Importar el archivo SQL del proyecto.

---

# Configuración

Editar el archivo:

```text
config/database.php
```

Configurar los datos de conexión según el entorno:

- Host
- Base de datos
- Usuario
- Contraseña

---

# Ejecutar el sistema

Abrir el navegador:

```text
http://localhost/contable
```

---

# Actualizar el proyecto

Para obtener los últimos cambios del repositorio:

```bash
git checkout develop
git pull
```

---

# Flujo de trabajo recomendado

Todo el desarrollo se realiza sobre la rama:

```text
develop
```

La rama:

```text
main
```

se reserva para versiones estables.

---

# Guardar cambios

Cuando una funcionalidad esté terminada:

```bash
git add .
git commit -m "Descripción del cambio"
git push
```

---

# Buenas prácticas

- Realizar commits frecuentes.
- Utilizar mensajes descriptivos.
- Mantener la documentación actualizada.
- Probar cada funcionalidad antes de realizar un commit.
- Trabajar siempre sobre la rama `develop`.

---

# Solución de problemas

## Error de conexión a la base de datos

Verificar:

- Apache iniciado.
- MySQL iniciado.
- Configuración correcta en `config/database.php`.

---

## Error 404

Verificar que el proyecto se encuentre dentro de:

```text
C:\xampp\htdocs\contable
```

---

## Git no reconoce comandos

Verificar que Git esté instalado:

```bash
git --version
```

---

# Soporte

Ante cualquier inconveniente, revisar la documentación del proyecto o consultar el repositorio en GitHub.