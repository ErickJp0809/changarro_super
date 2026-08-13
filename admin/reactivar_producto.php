<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   VALIDAR ID
   ========================================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: productos.php");
    exit();

}

$producto_id = intval($_GET["id"]);


/* =========================================
   COMPROBAR PRODUCTO
   ========================================= */

$stmt = $conexion->prepare("
    SELECT
        id,
        nombre
    FROM productos
    WHERE id = ?
    AND activo = 0
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $producto_id
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    header("Location: productos.php");
    exit();

}


/* =========================================
   REACTIVAR
   ========================================= */

$stmt_reactivar = $conexion->prepare("
    UPDATE productos
    SET
        activo = 1,
        stock = 0
    WHERE id = ?
    AND activo = 0
");


$stmt_reactivar->bind_param(
    "i",
    $producto_id
);


if (
    $stmt_reactivar->execute()
) {

    header(
        "Location: productos.php?reactivado=ok"
    );

    exit();

}


/* =========================================
   ERROR
   ========================================= */

header(
    "Location: productos.php?error="
    . urlencode(
        "No se pudo reactivar el producto."
    )
);

exit();

?>