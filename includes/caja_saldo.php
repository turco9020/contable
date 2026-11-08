<?php
// =====================================================================
// Control de saldo negativo de las cajas
// Las cajas con permite_negativo = 0 no aceptan cambios que las dejen por
// debajo de $ 0. Uso, dentro de una transacción:
//   $antes = cajaSaldos($conn, [$caja_id, ...]);   // antes del cambio
//   ... INSERT / UPDATE / DELETE en movimientos_caja ...
//   $error = cajaControlarNegativo($conn, $antes);  // después del cambio
//   if ($error) -> rollback y mostrar $error
// Solo se rechaza un cambio que BAJA el saldo y lo deja negativo: si una caja
// ya estaba en negativo, un ingreso que no alcanza a cubrirla se acepta igual.
// =====================================================================

// Saldo actual de cada caja (la misma cuenta que las tarjetas de Caja)
function cajaSaldos($conn, array $caja_ids) {
    $ids = array_values(array_unique(array_filter(array_map('intval', $caja_ids))));
    $saldos = [];
    foreach ($ids as $id) $saldos[$id] = 0.0;
    if (empty($ids)) return $saldos;

    // Dentro de una transacción bloquea las cajas hasta el commit, así dos movimientos
    // simultáneos sobre la misma caja no pasan el control a la vez
    $conn->query("SELECT id FROM cajas WHERE id IN (" . implode(',', $ids) . ") FOR UPDATE");

    $res = $conn->query("
        SELECT caja_id,
               SUM(CASE WHEN tipo IN ('INGRESO', 'TRANSFERENCIA') THEN importe ELSE -importe END) AS saldo
        FROM movimientos_caja
        WHERE caja_id IN (" . implode(',', $ids) . ")
        GROUP BY caja_id
    ");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $saldos[(int)$r['caja_id']] = (float)$r['saldo'];
        }
    }
    return $saldos;
}

// Devuelve el mensaje de error si alguna caja que no permite negativo quedó
// por debajo de $ 0 con el cambio, o null si está todo bien.
function cajaControlarNegativo($conn, array $saldos_antes) {
    if (empty($saldos_antes)) return null;
    $despues = cajaSaldos($conn, array_keys($saldos_antes));

    $res = $conn->query("SELECT id, nombre, permite_negativo FROM cajas WHERE id IN (" . implode(',', array_map('intval', array_keys($saldos_antes))) . ")");
    if (!$res) return null; // sin la columna (antes del ALTER) no se controla
    while ($c = $res->fetch_assoc()) {
        if ((int)($c['permite_negativo'] ?? 1) === 1) continue;
        $id = (int)$c['id'];
        $antes = $saldos_antes[$id] ?? 0;
        $ahora = $despues[$id] ?? 0;
        if ($ahora < -0.005 && $ahora < $antes - 0.005) {
            return "La caja " . $c['nombre'] . " no permite saldo negativo: con este movimiento quedaría en "
                 . cajaFormatoMoneda($ahora) . " (saldo actual " . cajaFormatoMoneda($antes) . ").";
        }
    }
    return null;
}

function cajaFormatoMoneda($n) {
    return '$ ' . number_format((float)$n, 2, ',', '.');
}
