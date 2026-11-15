<?php
/* ==========================================================================
   DATOS DE LA EMPRESA (Configuración → Empresa)
   --------------------------------------------------------------------------
   Lectura centralizada de la tabla config_empresa (un solo registro, id = 1)
   y de los logos configurables. Si la tabla todavía no existe o un logo no
   se cargó, se usan los valores de siempre, para que nada se rompa.

   Logos: uploads/empresa/{archivo}
     - logo_sistema: sidebar y login        (por defecto assets/img/logo.png)
     - logo_pdf:     encabezado de los PDF  (por defecto assets/img/logo-pdf.jpg)
   ========================================================================== */

const EMPRESA_CAMPOS = [
    'razon_social', 'nombre_fantasia', 'cuit', 'condicion_iva', 'ingresos_brutos',
    'inicio_actividades', 'direccion', 'localidad', 'provincia', 'cp',
    'telefono', 'whatsapp', 'email', 'sitio_web',
];
// Extensiones por logo. El de PDF va solo en JPG: Dompdf necesita la extensión GD
// de PHP para insertar PNG, y el servidor puede no tenerla.
const EMPRESA_LOGO_EXT = [
    'logo_sistema' => ['png', 'jpg', 'jpeg'],
    'logo_pdf'     => ['jpg', 'jpeg'],
];

function empresaDatos($conn = null) {
    static $cache = null;
    if ($cache !== null) return $cache;

    $datos = array_fill_keys(array_merge(EMPRESA_CAMPOS, ['logo_sistema', 'logo_pdf']), '');
    $datos['razon_social'] = 'RECURSOS GLOBALES';
    $datos['nombre_fantasia'] = 'Recursos Globales';

    if ($conn === null) $conn = $GLOBALS['conn'] ?? null;
    if ($conn) {
        try {
            $res = $conn->query("SELECT * FROM config_empresa WHERE id = 1");
            if ($res && ($row = $res->fetch_assoc())) {
                foreach ($datos as $k => $v) {
                    if (array_key_exists($k, $row) && $row[$k] !== null) $datos[$k] = $row[$k];
                }
            }
        } catch (Throwable $e) {
            // Tabla inexistente (antes de correr el SQL): se siguen usando los valores de siempre
        }
    }
    return $cache = $datos;
}

// Nombre corto para mostrar (fantasía o, si no hay, razón social)
function empresaNombre() {
    $d = empresaDatos();
    return $d['nombre_fantasia'] !== '' ? $d['nombre_fantasia'] : $d['razon_social'];
}

function empresaDirUploads() {
    return $_SERVER['DOCUMENT_ROOT'] . '/contable/uploads/empresa/';
}

// Archivo de logo válido (solo nombre simple, sin rutas) o '' si no existe
function empresaArchivoLogo($tipo) {
    $archivo = basename((string)(empresaDatos()[$tipo] ?? ''));
    return ($archivo !== '' && is_file(empresaDirUploads() . $archivo)) ? $archivo : '';
}

// URL del logo para la web (sidebar / login)
function empresaLogoUrl() {
    $archivo = empresaArchivoLogo('logo_sistema');
    return $archivo !== ''
        ? '/contable/uploads/empresa/' . rawurlencode($archivo) . '?v=' . filemtime(empresaDirUploads() . $archivo)
        : '/contable/assets/img/logo.png';
}

// Logo para los PDF como data URI (Dompdf no carga URLs remotas)
function empresaLogoPdfBase64() {
    $archivo = empresaArchivoLogo('logo_pdf');
    $ruta = $archivo !== ''
        ? empresaDirUploads() . $archivo
        : $_SERVER['DOCUMENT_ROOT'] . '/contable/assets/img/logo-pdf.jpg';

    // Un PNG sin la extensión GD rompería el PDF: se usa el logo de siempre
    $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
    if ($ext === 'png' && !extension_loaded('gd')) {
        $ruta = $_SERVER['DOCUMENT_ROOT'] . '/contable/assets/img/logo-pdf.jpg';
        $ext = 'jpg';
    }
    if (!is_file($ruta)) return '';

    $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
    return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($ruta));
}

// Línea de contacto para el pie de los PDF (solo los datos cargados)
function empresaLineaContacto() {
    $d = empresaDatos();
    $partes = [];
    if ($d['razon_social'] !== '') $partes[] = $d['razon_social'];
    if ($d['cuit'] !== '') $partes[] = 'CUIT ' . $d['cuit'];

    $dir = trim(implode(', ', array_filter([$d['direccion'], $d['localidad'], $d['provincia']], 'strlen')));
    if ($d['cp'] !== '' && $dir !== '') $dir .= ' (' . $d['cp'] . ')';
    if ($dir !== '') $partes[] = $dir;

    if ($d['telefono'] !== '') $partes[] = 'Tel. ' . $d['telefono'];
    if ($d['whatsapp'] !== '') $partes[] = 'WhatsApp ' . $d['whatsapp'];
    if ($d['email'] !== '') $partes[] = $d['email'];
    if ($d['sitio_web'] !== '') $partes[] = $d['sitio_web'];

    return implode('  ·  ', $partes);
}
