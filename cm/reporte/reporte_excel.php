<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}
require_once '../../conn/conexionBD.php';
require_once('../../vendor/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Filtro de fecha y empleado
$filtro   = $_GET['filtro']   ?? 'hoy';
$empleado = $_GET['empleado'] ?? 'todos';

switch ($filtro) {
    case 'ayer':
        $date_condition = "DATE(fecha_venta) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        break;
    case 'ultimos7':
        $date_condition = "DATE(fecha_venta) BETWEEN DATE_SUB(CURDATE(), INTERVAL 6 DAY) AND CURDATE()";
        break;
    case 'mes_actual':
        $date_condition = "MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE())";
        break;
    case 'hoy':
    default:
        $date_condition = "DATE(fecha_venta) = CURDATE()";
        break;
}

// Consulta actualizada
$sql = "
    SELECT 
      id, concepto, categoria, tipo_pago, precio, descuento, precio_final, 
      propina, tipo_propina, cambio, ticket, notas, fecha_venta, empleado
    FROM Ventas
    WHERE $date_condition
";
if ($empleado !== 'todos') {
    $sql .= " AND empleado = :empleado";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':empleado' => $empleado]);
} else {
    $stmt = $pdo->query($sql);
}
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Variables para resúmenes
$totalGanEfectivo   = 0;
$totalCambioEfectivo= 0;
$totalGanTarjeta    = 0;
$totalGanDolares    = 0;
$totalTipEfectivo   = 0;
$totalTipTarjeta    = 0;
$totalTipDolares    = 0;

// Pre-cálculo de totales y propinas
foreach ($ventas as $v) {
    $tp      = strtolower($v['tipo_pago']);
    $pf      = (float)$v['precio_final'];
    $cambio  = (float)$v['cambio'];
    $propina = (float)$v['propina'];

    if ($tp === 'efectivo') {
        $totalGanEfectivo    += $pf;
        $totalCambioEfectivo += $cambio;
        $totalTipEfectivo    += $propina;
    } elseif ($tp === 'tarjeta' || $tp === 'c' || $tp === 'd') {
        $totalGanTarjeta += $pf;
        $totalTipTarjeta += $propina;
    } elseif ($tp === 'dolares') {
        $totalGanDolares += $pf;
        $totalTipDolares += $propina;
    }
}
// Ajuste efectivo restando cambio
$totalGanEfectivo -= $totalCambioEfectivo;

// Crear hoja y configurar encabezados
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$headers = [
    'B2'=>'N°','C2'=>'Concepto','D2'=>'Categoría','E2'=>'Tipo de Pago',
    'F2'=>'Precio','G2'=>'Descuento','H2'=>'Precio Final','I2'=>'Propina',
    'J2'=>'Tipo Propina','K2'=>'Ticket','L2'=>'Nota','M2'=>'Fecha de Venta',
    'N2'=>'Empleado'
];
foreach ($headers as $cell => $text) {
    $sheet->setCellValue($cell, $text);
}
$headerRange = 'B2:N2';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FF83AEE1');

// Volcar datos de ventas
$row = 3;
$counter = 1;
foreach ($ventas as $v) {
    $fechaFmt = in_array($filtro, ['ultimos7','mes_actual'])
        ? date("d-m-Y H:i", strtotime($v['fecha_venta']))
        : date("H:i", strtotime($v['fecha_venta']));
    $sheet->setCellValue("B{$row}", $counter++);
    $sheet->setCellValue("C{$row}", $v['concepto']);
    $sheet->setCellValue("D{$row}", $v['categoria']);
    $sheet->setCellValue("E{$row}", $v['tipo_pago']);
    $sheet->setCellValue("F{$row}", $v['precio']);
    $sheet->setCellValue("G{$row}", $v['descuento']);
    $sheet->setCellValue("H{$row}", $v['precio_final']);
    $sheet->setCellValue("I{$row}", $v['propina']);
    $sheet->setCellValue("J{$row}", $v['tipo_propina']);
    $sheet->setCellValue("K{$row}", $v['ticket']);
    $sheet->setCellValue("L{$row}", $v['notas']);
    $sheet->setCellValue("M{$row}", $fechaFmt);
    $sheet->setCellValue("N{$row}", $v['empleado']);
    $row++;
}

// Auto-ajustar ancho
foreach (range('B','N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Estilos para bordes
$lastDataRow = $row - 1;
$borderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'FF1F497D'],
        ],
    ],
];
$sheet->getStyle("B2:N{$lastDataRow}")->applyFromArray($borderStyle);

// --- Tabla de Ganancias ---
$ganRow = $row++;
$sheet->setCellValue("B{$ganRow}", 'GANANCIAS');
$sheet->setCellValue("C{$ganRow}", 'Efectivo');
$sheet->setCellValue("D{$ganRow}", number_format($totalGanEfectivo, 2));
$sheet->setCellValue("E{$ganRow}", 'Tarjeta');
$sheet->setCellValue("F{$ganRow}", number_format($totalGanTarjeta, 2));
$sheet->setCellValue("G{$ganRow}", 'Dólares');
$sheet->setCellValue("H{$ganRow}", number_format($totalGanDolares, 2));
// Estilo Ganancias
$rangeGan = "B{$ganRow}:H{$ganRow}";
$sheet->getStyle($rangeGan)->getFont()->setBold(true);
$sheet->getStyle($rangeGan)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFD9E1F2');
$sheet->getStyle($rangeGan)->applyFromArray($borderStyle);

// --- Tabla de Propinas ---
$tipRow = $row++;
$sheet->setCellValue("B{$tipRow}", 'PROPINAS');
$sheet->setCellValue("C{$tipRow}", 'Efectivo');
$sheet->setCellValue("D{$tipRow}", number_format($totalTipEfectivo, 2));
$sheet->setCellValue("E{$tipRow}", 'Tarjeta');
$sheet->setCellValue("F{$tipRow}", number_format($totalTipTarjeta, 2));
$sheet->setCellValue("G{$tipRow}", 'Dólares');
$sheet->setCellValue("H{$tipRow}", number_format($totalTipDolares, 2));
// Estilo Propinas
$rangeTip = "B{$tipRow}:H{$tipRow}";
$sheet->getStyle($rangeTip)->getFont()->setBold(true);
$sheet->getStyle($rangeTip)->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()->setARGB('FFD9E1F2');
$sheet->getStyle($rangeTip)->applyFromArray($borderStyle);

// Enviar al navegador
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_de_ventas.xlsx"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
