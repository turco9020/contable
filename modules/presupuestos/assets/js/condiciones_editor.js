// =====================================================================
// EDITOR DE CONDICIONES GENERALES (Quill)
// Se usa en Presupuestos > Condiciones Generales y en la pestaña
// Condiciones del modal del presupuesto. Requiere quill@2 cargado antes.
// El servidor vuelve a limpiar el HTML al guardar (includes/condiciones_html.php).
// =====================================================================

function crearEditorCondiciones(selector, placeholder) {
    return new Quill(selector, {
        theme: 'snow',
        placeholder: placeholder || 'Escriba las condiciones generales...',
        formats: ['bold', 'italic', 'underline', 'list'],
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['clean']
            ]
        }
    });
}

// ¿El contenido guardado ya tiene formato o es texto plano de antes del editor?
function condicionesEsHtml(texto) {
    return /<\s*\/?\s*(p|br|div|span|strong|b|em|i|u|ul|ol|li)\b/i.test(texto || '');
}

// Carga contenido en el editor (HTML o texto plano viejo)
function setCondicionesEditor(quill, contenido) {
    contenido = contenido || '';
    if (condicionesEsHtml(contenido)) {
        quill.setContents(quill.clipboard.convert({ html: contenido }), 'silent');
    } else {
        quill.setText(contenido, 'silent');
    }
    quill.history.clear(); // que "deshacer" no vuelva al contenido anterior
}

// Devuelve el HTML del editor ('' si está vacío)
function getCondicionesEditor(quill) {
    if (quill.getText().trim() === '') return '';
    return quill.getSemanticHTML()
        .replace(/&nbsp;/g, ' ')
        .replace(/<p><\/p>/g, '<p><br></p>'); // líneas en blanco visibles (un <p> vacío no ocupa lugar)
}

function condicionesEditorVacio(quill) {
    return quill.getText().trim() === '';
}
