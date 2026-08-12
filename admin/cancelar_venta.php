<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../login.php");
    exit();
}

require_once "../config/conexion.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: ventas.php");
    exit();
}

$venta_id = intval($_GET["id"]);


/* Buscar venta */

$sql_venta = "
    SELECT id, estado
    FROM ventas
    WHERE id = $venta_id
";

$resultado_venta = $conexion->query($sql_venta);

if ($resultado_venta->num_rows === 0) {
    die("La venta no existe.");
}

$venta = $resultado_venta->fetch_assoc();


/* Evitar cancelar dos veces */

if ($venta["estado"] === "Cancelada") {
    header("Location: detalle_venta.php?id=" . $venta_id);
    exit();
}


/* Iniciar transacción */

$conexion->begin_transaction();

try {

    /*
     * Obtener todos los productos
     * que pertenecen a la venta
     */

    $sql_detalles = "
        SELECT
            producto_id,
            cantidad
        FROM detalle_venta
        WHERE venta_id = $venta_id
    ";

    $resultado_detalles =
        $conexion->query($sql_detalles);


    /*
     * Devolver cada producto al inventario
     */

    while ($detalle = $resultado_detalles->fetch_assoc()) {

        $producto_id =
            intval($detalle["producto_id"]);

        $cantidad =
            intval($detalle["cantidad"]);


        /* Aumentar stock */

        $sql_stock = "
            UPDATE productos
            SET stock = stock + $cantidad
            WHERE id = $producto_id
        ";

        if (!$conexion->query($sql_stock)) {
            throw new Exception(
                "Error al devolver el stock."
            );
        }


        /*
         * Registrar entrada en inventario
         */

        $motivo =
            "Cancelación de venta #" . $venta_id;

        $sql_movimiento = "
            INSERT INTO movimientos_inventario
            (
                producto_id,
                tipo,
                cantidad,
                motivo
            )
            VALUES
            (
                $producto_id,
                'Entrada',
                $cantidad,
                '$motivo'
            )
        ";

        if (!$conexion->query($sql_movimiento)) {
            throw new Exception(
                "Error al registrar el movimiento."
            );
        }

    }


    /*
     * Marcar venta como cancelada
     */

    $sql_cancelar = "
        UPDATE ventas
        SET estado = 'Cancelada'
        WHERE id = $venta_id
    ";

    if (!$conexion->query($sql_cancelar)) {
        throw new Exception(
            "Error al cancelar la venta."
        );
    }


    /* Confirmar */

    $conexion->commit();


    header(
        "Location: detalle_venta.php?id=" .
        $venta_id
    );

    exit();


} catch (Exception $e) {

    $conexion->rollback();

    die(
        "No se pudo cancelar la venta: " .
        $e->getMessage()
    );
}