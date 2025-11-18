<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once '../../conn/conexionBD.php';
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : 'hoy';
if ($filtro !== 'hoy' && $filtro !== 'ayer') {
    $filtro = 'hoy';
}
$empleado = isset($_GET['empleado']) ? $_GET['empleado'] : 'todos';
switch ($filtro) {
    case 'ayer':
        $date_condition = "DATE(fecha_venta) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        break;
    default:
        $date_condition = "DATE(fecha_venta) = CURDATE()";
        break;
}
$sql_ventas = "SELECT id, concepto, categoria, tipo_pago, precio, fecha_venta, empleado FROM Ventas WHERE $date_condition";
if ($empleado !== 'todos') {
    $sql_ventas .= " AND empleado = :empleado";
    $stmt = $pdo->prepare($sql_ventas);
    $stmt->execute([':empleado' => $empleado]);
} else {
    $stmt = $pdo->query($sql_ventas);
}
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$monthMap = [
    '01' => 'ene', '02' => 'feb', '03' => 'mar', '04' => 'abr',
    '05' => 'may', '06' => 'jun', '07' => 'jul', '08' => 'ago',
    '09' => 'sep', '10' => 'oct', '11' => 'nov', '12' => 'dic'
];
if ($filtro === 'ayer') {
    $ayer = new DateTime('-1 day');
    $fechaText = "Fecha: " . $ayer->format('d') . '-' . $monthMap[$ayer->format('m')];
} else {
    $hoy = new DateTime();
    $fechaText = "Fecha: " . $hoy->format('d') . '-' . $monthMap[$hoy->format('m')];
}
$empleadoText = "Empleado: " . ($empleado !== 'todos' ? $empleado : "Todos");
$fontTable = 3;
$padding = 10;
$columns = [];
$columns[] = ['header' => 'No.', 'width' => imagefontwidth($fontTable) * strlen('No.')];
$columns[] = ['header' => 'Concepto', 'width' => imagefontwidth($fontTable) * strlen('Concepto')];
$columns[] = ['header' => 'Categoria', 'width' => imagefontwidth($fontTable) * strlen('Categoria')];
$columns[] = ['header' => 'Fecha de Venta', 'width' => imagefontwidth($fontTable) * strlen('Fecha de Venta')];
if ($empleado === 'todos') {
    $columns[] = ['header' => 'Empleado', 'width' => imagefontwidth($fontTable) * strlen('Empleado')];
}
$seq = 1;
foreach ($ventas as $row) {
    $cell0 = $seq;
    $cell1 = $row['concepto'];
    $cell2 = $row['categoria'];
    $cell3 = date("H:i", strtotime($row['fecha_venta']));
    $rowData = [$cell0, $cell1, $cell2, $cell3];
    if ($empleado === 'todos') {
        $rowData[] = $row['empleado'];
    }
    foreach ($rowData as $i => $cellText) {
        $cellText = (string)$cellText;
        $textWidth = imagefontwidth($fontTable) * strlen($cellText);
        if ($textWidth > $columns[$i]['width']) {
            $columns[$i]['width'] = $textWidth;
        }
    }
    $seq++;
}
foreach ($columns as &$col) {
    $col['width'] += $padding;
}
unset($col);
$tableWidth = 0;
foreach ($columns as $col) {
    $tableWidth += $col['width'];
}
$cellHeight = 20;
$tableHeaderHeight = $cellHeight;
$numRows = count($ventas);
$tableDataHeight = $cellHeight * $numRows;
$tableAreaHeight = $tableHeaderHeight + $tableDataHeight;
$titleAreaHeight = 30;
$subTitleAreaHeight = 20 * 2;
$headerAreaHeight = $titleAreaHeight + $subTitleAreaHeight + 10;
$contentHeight = $headerAreaHeight + $tableAreaHeight + 20;
$contentWidth = $tableWidth + 40;
$side = max(800, $contentWidth, $contentHeight);
$image = imagecreatetruecolor($side, $side);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$gray = imagecolorallocate($image, 200, 200, 200);
imagefilledrectangle($image, 0, 0, $side, $side, $white);
$totalContentHeight = $headerAreaHeight + $tableAreaHeight;
$startY = ($side - $totalContentHeight) / 2;
$startX = ($side - $tableWidth) / 2;
$title = "Cortes";
$fontTitle = 5;
$titleWidth = imagefontwidth($fontTitle) * strlen($title);
$titleX = ($side - $titleWidth) / 2;
$titleY = $startY;
imagestring($image, $fontTitle, $titleX, $titleY, utf8_decode($title), $black);
$fontSub = 3;
$fechaWidth = imagefontwidth($fontSub) * strlen($fechaText);
$empleadoWidth = imagefontwidth($fontSub) * strlen($empleadoText);
$subX = ($side - max($fechaWidth, $empleadoWidth)) / 2;
$fechaY = $titleY + imagefontheight($fontTitle) + 5;
imagestring($image, $fontSub, $subX, $fechaY, utf8_decode($fechaText), $black);
$empleadoY = $fechaY + imagefontheight($fontSub) + 5;
imagestring($image, $fontSub, $subX, $empleadoY, utf8_decode($empleadoText), $black);
$tableStartY = $startY + $headerAreaHeight;
imagefilledrectangle($image, $startX, $tableStartY, $startX + $tableWidth, $tableStartY + $cellHeight, $gray);
$currentX = $startX;
foreach ($columns as $col) {
    $headerText = $col['header'];
    $textWidth = imagefontwidth($fontTable) * strlen($headerText);
    $cellXCenter = $currentX + ($col['width'] - $textWidth) / 2;
    $cellYCenter = $tableStartY + ($cellHeight - imagefontheight($fontTable)) / 2;
    imagestring($image, $fontTable, $cellXCenter, $cellYCenter, utf8_decode($headerText), $black);
    $currentX += $col['width'];
}
imageline($image, $startX, $tableStartY + $cellHeight, $startX + $tableWidth, $tableStartY + $cellHeight, $black);
$currentY = $tableStartY + $cellHeight;
$seq = 1;
foreach ($ventas as $row) {
    $currentX = $startX;
    $fechaFormatted = date("H:i", strtotime($row['fecha_venta']));
    $rowData = [$seq, $row['concepto'], $row['categoria'], $fechaFormatted];
    if ($empleado === 'todos') {
        $rowData[] = $row['empleado'];
    }
    foreach ($rowData as $i => $cellData) {
        $cellData = (string)$cellData;
        $textWidth = imagefontwidth($fontTable) * strlen($cellData);
        $cellXCenter = $currentX + ($columns[$i]['width'] - $textWidth) / 2;
        $cellYCenter = $currentY + ($cellHeight - imagefontheight($fontTable)) / 2;
        imagestring($image, $fontTable, $cellXCenter, $cellYCenter, utf8_decode($cellData), $black);
        imageline($image, $currentX + $columns[$i]['width'], $currentY, $currentX + $columns[$i]['width'], $currentY + $cellHeight, $black);
        $currentX += $columns[$i]['width'];
    }
    imageline($image, $startX, $currentY + $cellHeight, $startX + $tableWidth, $currentY + $cellHeight, $black);
    $currentY += $cellHeight;
    $seq++;
}
imageline($image, $startX, $tableStartY, $startX, $tableStartY + $tableAreaHeight, $black);
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="ticket.png"');
imagepng($image);
imagedestroy($image);
exit();
?>
