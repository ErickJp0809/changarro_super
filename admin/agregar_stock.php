<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   VALIDAR PETICIÓN
   ========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: productos.php");
    exit();

}


/* =========================================
   RECIBIR DATOS
   ========================================= */

$producto_id = intval(
    $_POST["producto_id"] ?? 0
);

$cantidad = intval(
    $_POST["cantidad"] ?? 0
);


/* =========================================
   VALIDAR DATOS
   ========================================= */

if ($producto_id <= 0) {

    header(
        "Location: productos.php?error=producto"
    );

    exit();

}


if ($cantidad <= 0) {

    header(
        "Location: productos.php?error=cantidad"
    );

    exit();

}


/* =========================================
   COMPROBAR PRODUCTO
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

    header(
        "Location: productos.php?error=producto"
    );

    exit();

}


$producto = $resultado->fetch_assoc();


/* =========================================
   INICIAR TRANSACCIÓN
   ========================================= */

$conexion->begin_transaction();


try {


    /* =====================================
       ACTUALIZAR STOCK
       ===================================== */

    $sql_stock = "
        UPDATE productos
        SET stock = stock + ?
        WHERE id = ?
        AND activo = 1
    ";

    $stmt_stock =
        $conexion->prepare($sql_stock);


    if (!$stmt_stock) {

        throw new Exception(
            "Error al preparar la actualización del stock: "
            . $conexion->error
        );

    }


    $stmt_stock->bind_param(
        "ii",
        $cantidad,
        $producto_id
    );


    if (!$stmt_stock->execute()) {

        throw new Exception(
            "Error al actualizar el stock: "
            . $stmt_stock->error
        );

    }


    /* =====================================
       REGISTRAR MOVIMIENTO
       ===================================== */

    $tipo = "entrada";

    $motivo =
        "Reposición de mercancía";


    $sql_movimiento = "
        INSERT INTO movimientos_inventario
        (
            producto_id,
            tipo,
            cantidad,
            motivo
        )
        VALUES (?, ?, ?, ?)
    ";


    $stmt_movimiento =
        $conexion->prepare(
            $sql_movimiento
        );


    if (!$stmt_movimiento) {

        throw new Exception(
            "Error al preparar el movimiento: "
            . $conexion->error
        );

    }


    $stmt_movimiento->bind_param(
        "isis",
        $producto_id,
        $tipo,
        $cantidad,
        $motivo
    );


    if (!$stmt_movimiento->execute()) {

        throw new Exception(
            "Error al registrar el movimiento: "
            . $stmt_movimiento->error
        );

    }


    /* =====================================
       CONFIRMAR
       ===================================== */

    $conexion->commit();


    header(
        "Location: productos.php?stock=ok"
    );

    exit();


} catch (Exception $e) {


    /* =====================================
       DESHACER
       ===================================== */

    $conexion->rollback();


    $error = urlencode(
        $e->getMessage()
    );


    header(
        "Location: productos.php?error=" . $error
    );

    exit();

}