<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: inventario.php");
    exit();
}

$producto_id = intval($_POST["producto_id"]);
$tipo = $_POST["tipo"];
$cantidad = intval($_POST["cantidad"]);
$motivo = trim($_POST["motivo"]);

/* Validaciones */

if ($producto_id <= 0 || $cantidad <= 0) {
    header("Location: inventario.php");
    exit();
}

if ($tipo !== "entrada" && $tipo !== "salida") {
    header("Location: inventario.php");
    exit();
}


/* Buscar producto */

$sql = "SELECT stock
        FROM productos
        WHERE id = ? AND activo = 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $producto_id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: inventario.php");
    exit();
}

$producto = $resultado->fetch_assoc();

$stock_actual = intval($producto["stock"]);


/* Calcular nuevo stock */

if ($tipo === "entrada") {

    $nuevo_stock = $stock_actual + $cantidad;

} else {

    /* Evitar stock negativo */

    if ($cantidad > $stock_actual) {
        echo "Error: no hay suficiente stock disponible.";
        exit();
    }

    $nuevo_stock = $stock_actual - $cantidad;
}


/* Actualizar producto */

$sql_update = "UPDATE productos
               SET stock = ?
               WHERE id = ?";

$stmt_update = $conexion->prepare($sql_update);

$stmt_update->bind_param(
    "ii",
    $nuevo_stock,
    $producto_id
);

$stmt_update->execute();


/* Registrar movimiento */

$sql_movimiento = "INSERT INTO movimientos_inventario
                   (producto_id, tipo, cantidad, motivo)
                   VALUES (?, ?, ?, ?)";

$stmt_movimiento = $conexion->prepare($sql_movimiento);

$stmt_movimiento->bind_param(
    "isis",
    $producto_id,
    $tipo,
    $cantidad,
    $motivo
);

$stmt_movimiento->execute();


/* Regresar al inventario */

header("Location: inventario.php");
exit();