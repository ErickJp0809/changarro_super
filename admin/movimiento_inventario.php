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


/* =========================
   DATOS DEL FORMULARIO
   ========================= */

$producto_id = intval($_POST["producto_id"] ?? 0);
$tipo = $_POST["tipo"] ?? "";
$cantidad = intval($_POST["cantidad"] ?? 0);
$motivo = trim($_POST["motivo"] ?? "");

$usuario_id = intval($_SESSION["id"]);


/* =========================
   VALIDACIONES
   ========================= */

if ($producto_id <= 0 || $cantidad <= 0) {
    die("Error: producto o cantidad inválidos.");
}

if ($tipo !== "entrada" && $tipo !== "salida") {
    die("Error: tipo de movimiento inválido.");
}


/* =========================
   BUSCAR PRODUCTO
   ========================= */

$sql = "SELECT stock
        FROM productos
        WHERE id = ? AND activo = 1";

$stmt = $conexion->prepare($sql);

if (!$stmt) {
    die("Error preparando consulta de producto: " . $conexion->error);
}

$stmt->bind_param("i", $producto_id);

if (!$stmt->execute()) {
    die("Error consultando producto: " . $stmt->error);
}

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Error: el producto no existe o está inactivo.");
}

$producto = $resultado->fetch_assoc();

$stock_actual = intval($producto["stock"]);


/* =========================
   CALCULAR NUEVO STOCK
   ========================= */

if ($tipo === "entrada") {

    $nuevo_stock = $stock_actual + $cantidad;

} else {

    if ($cantidad > $stock_actual) {
        die("Error: no hay suficiente stock disponible.");
    }

    $nuevo_stock = $stock_actual - $cantidad;
}


/* =========================
   ACTUALIZAR PRODUCTO
   ========================= */

$sql_update = "UPDATE productos
               SET stock = ?
               WHERE id = ?";

$stmt_update = $conexion->prepare($sql_update);

if (!$stmt_update) {
    die("Error preparando actualización: " . $conexion->error);
}

$stmt_update->bind_param(
    "ii",
    $nuevo_stock,
    $producto_id
);

if (!$stmt_update->execute()) {
    die("Error actualizando stock: " . $stmt_update->error);
}


/* =========================
   REGISTRAR MOVIMIENTO
   ========================= */

$sql_movimiento = "
    INSERT INTO movimientos_inventario
    (
        producto_id,
        tipo,
        cantidad,
        motivo,
        usuario_id
    )
    VALUES (?, ?, ?, ?, ?)
";

$stmt_movimiento = $conexion->prepare($sql_movimiento);

if (!$stmt_movimiento) {
    die(
        "Error preparando movimiento: " .
        $conexion->error
    );
}

$stmt_movimiento->bind_param(
    "isisi",
    $producto_id,
    $tipo,
    $cantidad,
    $motivo,
    $usuario_id
);

if (!$stmt_movimiento->execute()) {
    die(
        "Error registrando movimiento: " .
        $stmt_movimiento->error
    );
}


/* =========================
   REGRESAR A INVENTARIO
   ========================= */

header("Location: inventario.php");
exit();

?>