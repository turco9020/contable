<?php
// =====================================================================
// CONDICIONES GENERALES CON FORMATO (HTML)
// El editor permite negrita, cursiva, subrayado y listas. Antes de guardar
// se limpia el HTML para dejar solo esas etiquetas, sin atributos.
// Los textos viejos en texto plano se convierten a párrafos.
// =====================================================================

if (!function_exists('condicionesTextoAHtml')) {
    // Texto plano -> párrafos HTML (línea en blanco = párrafo nuevo, salto simple = <br>)
    function condicionesTextoAHtml(string $texto): string {
        $texto = trim(str_replace(["\r\n", "\r"], "\n", $texto));
        if ($texto === '') return '';
        $html = '';
        foreach (preg_split("/\n{2,}/", $texto) as $parrafo) {
            $html .= '<p>' . str_replace("\n", '<br>', htmlspecialchars($parrafo, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        return $html;
    }
}

if (!function_exists('condicionesEsHtml')) {
    // Es HTML si tiene alguna etiqueta de bloque o de formato (un "<ok>" suelto en el texto no cuenta)
    function condicionesEsHtml(string $texto): bool {
        return (bool)preg_match('/<\s*\/?\s*(p|br|div|span|strong|b|em|i|u|ul|ol|li|h[1-6]|script|style|a|img)\b/i', $texto);
    }
}

if (!function_exists('condicionesLimpiarNodo')) {
    function condicionesLimpiarNodo(DOMNode $nodo): void {
        $permitidas = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li'];
        $eliminar   = ['script', 'style', 'iframe', 'object', 'embed', 'template', 'head', 'title', 'meta', 'link'];

        foreach (iterator_to_array($nodo->childNodes) as $hijo) {
            if ($hijo instanceof DOMElement) {
                $tag = strtolower($hijo->tagName);

                // Quill guarda las viñetas como <ol><li data-list="bullet">: se pasan a <ul>
                if ($tag === 'ol') {
                    $primer_li = $hijo->getElementsByTagName('li')->item(0);
                    if ($primer_li && $primer_li->getAttribute('data-list') === 'bullet') {
                        $ul = $hijo->ownerDocument->createElement('ul');
                        while ($hijo->firstChild) {
                            $ul->appendChild($hijo->firstChild);
                        }
                        $nodo->replaceChild($ul, $hijo);
                        $hijo = $ul;
                        $tag = 'ul';
                    }
                }

                if (in_array($tag, $eliminar, true)) {
                    $nodo->removeChild($hijo);
                    continue;
                }
                condicionesLimpiarNodo($hijo);
                if (in_array($tag, $permitidas, true)) {
                    // Sin atributos: evita estilos, eventos (onclick...) y enlaces
                    while ($hijo->attributes->length > 0) {
                        $hijo->removeAttribute($hijo->attributes->item(0)->nodeName);
                    }
                } else {
                    // Etiqueta no permitida: se conserva solo su contenido
                    while ($hijo->firstChild) {
                        $nodo->insertBefore($hijo->firstChild, $hijo);
                    }
                    $nodo->removeChild($hijo);
                }
            } elseif ($hijo->nodeType !== XML_TEXT_NODE) {
                // Comentarios, CDATA, instrucciones de procesamiento, etc.
                $nodo->removeChild($hijo);
            }
        }
    }
}

if (!function_exists('limpiarCondicionesHtml')) {
    function limpiarCondicionesHtml(?string $html): string {
        $html = trim((string)$html);
        if ($html === '') return '';

        // Texto plano (bases o presupuestos guardados antes del editor)
        if (!condicionesEsHtml($html)) {
            return condicionesTextoAHtml($html);
        }

        $doc = new DOMDocument();
        $errores_previos = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($errores_previos);

        $raiz = $doc->getElementsByTagName('div')->item(0);
        if (!$raiz) return '';
        condicionesLimpiarNodo($raiz);

        $salida = '';
        foreach ($raiz->childNodes as $hijo) {
            $salida .= $doc->saveHTML($hijo);
        }

        // Si quedó sin texto (ej: solo <p><br></p>), se guarda vacío
        return trim(strip_tags($salida)) === '' ? '' : trim($salida);
    }
}

if (!function_exists('condicionesResumen')) {
    // Texto sin formato para listados (ej: la tabla de bases)
    function condicionesResumen(?string $html, int $largo = 160): string {
        $texto = str_ireplace(['</p>', '<br>', '<br/>', '<br />', '</li>'], ' ', (string)$html);
        $texto = html_entity_decode(strip_tags($texto), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $texto = trim(preg_replace('/\s+/u', ' ', $texto));
        return mb_strlen($texto) > $largo ? mb_substr($texto, 0, $largo) . '…' : $texto;
    }
}
