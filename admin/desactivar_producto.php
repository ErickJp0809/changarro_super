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
   OBTENER PRODUCTO
   ========================================= */

$stmt = $conexion->prepare("
    SELECT
        id,
        nombre,
        stock
    FROM productos
    WHERE id = ?
    AND activo = 1
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


$producto = $resultado->fetch_assoc();

$stock_actual = intval(
    $producto["stock"]
);


/* =========================================
   TRANSACCIÓN
   ========================================= */

$conexion->begin_transaction();


try {


    /* =====================================
       REGISTRAR SALIDA
       ===================================== */

    if ($stock_actual > 0) {

        $tipo = "salida";

        $motivo = "Producto desactivado";


        $stmt_movimiento =
            $conexion->prepare("
                INSERT INTO movimientos_inventario
                (
                    producto_id,
                    tipo,
                    cantidad,
                    motivo
                )
                VALUES (?, ?, ?, ?)
            ");


        if (!$stmt_movimiento) {

            throw new Exception(
                "No se pudo preparar el movimiento: "
                . $conexion->error
            );

        }


        $stmt_movimiento->bind_param(
            "isis",
            $producto_id,
            $tipo,
            $stock_actual,
            $motivo
        );


        if (
            !$stmt_movimiento->execute()
        ) {

            throw new Exception(
                "No se pudo registrar la salida: "
                . $stmt_movimiento->error
            );

        }

    }


    /* =====================================
       DESACTIVAR PRODUCTO
       ===================================== */

    $stmt_producto = $conexion->prepare("
        UPDATE productos
        SET
            activo = 0,
            stock = 0
        WHERE id = ?
        AND activo = 1
    ");


    if (!$stmt_producto) {

        throw new Exception(
            "No se pudo preparar la actualización: "
            . $conexion->error
        );

    }


    $stmt_producto->bind_param(
        "i",
        $producto_id
    );


    if (
        !$stmt_producto->execute()
    ) {

        throw new Exception(
            "No se pudo desactivar el producto: "
            . $stmt_producto->error
        );

    }


    /* =====================================
       CONFIRMAR
       ===================================== */

    $conexion->commit();


    header(
        "Location: productos.php?desactivado=ok"
    );

    exit();


} catch (Exception $e) {


    $conexion->rollback();


    header(
        "Location: productos.php?error="
        . urlencode(
            $e->getMessage()
        )
    );

    exit();

}

?>