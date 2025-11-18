<?php
/**
 * Recalcula y actualiza precio_usd en ProdSer
 * redondeando siempre hacia arriba al siguiente .0 o .5.
 *
 * @param PDO   $pdo        Conexión PDO a la base de datos
 * @param float $tipoCambio Valor del dólar en MXN
 */
function actualizarPreciosUSD(PDO $pdo, float $tipoCambio): void
{
    $stmt = $pdo->prepare("SELECT id, precio_mxn FROM ProdSer WHERE activo = 1");
    $stmt->execute();
    $upd  = $pdo->prepare("UPDATE ProdSer SET precio_usd = ? WHERE id = ?");
    while ($p = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // redondea hacia arriba al siguiente .0 o .5
        $usd = ceil(($p['precio_mxn'] / $tipoCambio) * 2) / 2;
        $upd->execute([$usd, $p['id']]);
    }
}

