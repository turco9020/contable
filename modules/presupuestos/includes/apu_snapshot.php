<?php
// =====================================================================
// APU guardadas dentro de cada versión del presupuesto
// Cada versión guarda en su snapshot_json, bajo "apus", los insumos de cada APU
// usada y los K Mat / K MO con que se calculó su precio. Así el PDF de APU
// coincide siempre con el precio enviado al cliente, aunque después cambie el
// catálogo de APU, los precios de los insumos o la plantilla de K Base.
//
// "apus" = { tarea_id: { codigo, nombre, unidad, observaciones, costo_unitario_total,
//                        k_mat, k_mo, detalles: [{tipo, descripcion, unidad, cantidad, precio_unitario}] } }
// =====================================================================

// K Mat y K MO de la plantilla de K Base elegida. El combo usa "1.0000" para
// "Sin coeficiente": solo un ID entero se busca en las plantillas.
function apuKBase($conn, $coeficiente_k_base) {
    $valor = trim((string)$coeficiente_k_base);
    if ($valor === '' || !ctype_digit($valor)) {
        return ['mat' => 1.0, 'mo' => 1.0];
    }
    $res = $conn->query("SELECT mat_k_resultante, mo_k_resultante FROM presupuesto_coeficientes_plantillas WHERE id = " . intval($valor));
    $row = $res ? $res->fetch_assoc() : null;
    if (!$row) {
        return ['mat' => 1.0, 'mo' => 1.0];
    }
    // Igual que en el formulario: un K en 0 o vacío cuenta como 1
    return [
        'mat' => floatval($row['mat_k_resultante']) ?: 1.0,
        'mo'  => floatval($row['mo_k_resultante']) ?: 1.0,
    ];
}

// APU actuales del catálogo, con sus insumos, para los IDs pedidos
function apuCargarActuales($conn, array $tarea_ids) {
    $ids = array_values(array_unique(array_filter(array_map('intval', $tarea_ids))));
    if (empty($ids)) return [];
    $lista = implode(',', $ids);

    $apus = [];
    $res = $conn->query("SELECT id, codigo, nombre, unidad, observaciones, costo_unitario_total
                         FROM presupuesto_tareas WHERE id IN ($lista)");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $apus[intval($r['id'])] = [
                'codigo' => $r['codigo'] ?? '',
                'nombre' => $r['nombre'] ?? '',
                'unidad' => $r['unidad'] ?? '',
                'observaciones' => $r['observaciones'] ?? '',
                'costo_unitario_total' => floatval($r['costo_unitario_total']),
                'detalles' => [],
            ];
        }
    }

    $res_d = $conn->query("SELECT tarea_id, tipo, descripcion, unidad, cantidad, precio_unitario
                           FROM presupuesto_tarea_detalles
                           WHERE tarea_id IN ($lista)
                           ORDER BY tarea_id ASC, FIELD(UPPER(tipo), 'MATERIAL', 'MO', 'EQUIPO', 'SUBCONTRATO'), id ASC");
    if ($res_d) {
        while ($d = $res_d->fetch_assoc()) {
            $tid = intval($d['tarea_id']);
            if (!isset($apus[$tid])) continue;
            $apus[$tid]['detalles'][] = [
                'tipo' => strtoupper($d['tipo']),
                'descripcion' => $d['descripcion'],
                'unidad' => $d['unidad'],
                'cantidad' => floatval($d['cantidad']),
                'precio_unitario' => floatval($d['precio_unitario']),
            ];
        }
    }
    return $apus;
}

// Precio unitario de la APU con el K Base aplicado, igual que en el formulario:
// materiales x K Mat; mano de obra, equipos y subcontratos x K MO.
// Una APU sin insumos usa su costo unitario total x K Mat.
function apuPrecioConKBase(array $apu, $k_mat, $k_mo) {
    if (empty($apu['detalles'])) {
        return floatval($apu['costo_unitario_total'] ?? 0) * $k_mat;
    }
    $total = 0;
    foreach ($apu['detalles'] as $d) {
        $k = ($d['tipo'] === 'MATERIAL') ? $k_mat : $k_mo;
        $total += floatval($d['cantidad']) * floatval($d['precio_unitario']) * $k;
    }
    return $total;
}

// Arma el "apus" de la versión que se está guardando. Por cada APU usada:
// - si el precio del ítem coincide con la APU actual y el K Base actual, se guarda la actual;
// - si no (el precio quedó fijo de la versión cargada), se reutiliza la APU de esa versión;
// - si esa versión no la tenía (versiones anteriores a este cambio), se guarda la actual.
function apuArmarSnapshot($conn, array $post_data, array $apus_previas) {
    // Primer precio con que aparece cada APU en el presupuesto
    $precio_item = [];
    foreach (($post_data['rubros'] ?? []) as $rubro) {
        foreach (($rubro['items'] ?? []) as $it) {
            $tid = intval($it['tarea_id'] ?? 0);
            if ($tid > 0 && !isset($precio_item[$tid])) {
                $precio_item[$tid] = floatval($it['precio_unitario'] ?? 0);
            }
        }
    }
    if (empty($precio_item)) return new stdClass(); // {} en el JSON

    $k = apuKBase($conn, $post_data['coeficiente_k_base'] ?? '');
    $actuales = apuCargarActuales($conn, array_keys($precio_item));

    $apus = [];
    foreach ($precio_item as $tid => $precio) {
        $actual = $actuales[$tid] ?? null;
        $previa = $apus_previas[$tid] ?? $apus_previas[(string)$tid] ?? null;

        $coincide_actual = $actual && abs(round(apuPrecioConKBase($actual, $k['mat'], $k['mo']), 2) - $precio) < 0.015;

        if ($coincide_actual || !$previa) {
            if (!$actual) continue; // la APU se borró del catálogo y no hay copia
            $apus[$tid] = $actual + ['k_mat' => $k['mat'], 'k_mo' => $k['mo']];
        } else {
            $apus[$tid] = $previa;
        }
    }
    return empty($apus) ? new stdClass() : $apus;
}
