<?php

session_start();

require_once "../config/conexion.php";


/* =========================================
   VERIFICAR SESIÓN
   ========================================= */

if (!isset($_SESSION["id"])) {

    header("Location: ../index.php");

    exit();

}


/* =========================================
   VERIFICAR QUE SEA CLIENTE
   ========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Cliente"
) {

    header("Location: ../admin/dashboard.php");

    exit();

}


/* =========================================
   VERIFICAR MÉTODO POST
   ========================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: pedidos_clientes.php");

    exit();

}


$usuario_id =
    intval($_SESSION["id"]);


$pedido_id =
    intval($_POST["pedido_id"] ?? 0);


/* =========================================
   VALIDAR ID DEL PEDIDO
   ========================================= */

if ($pedido_id <= 0) {

    header(
        "Location: pedidos_clientes.php?error="
        . urlencode("Pedido no válido.")
    );

    exit();

}


/* =========================================
   INICIAR TRANSACCIÓN
   ========================================= */

$conexion->begin_transaction();


try {


    /* =====================================
       OBTENER PEDIDO

       FOR UPDATE bloquea el pedido
       mientras se realiza la cancelación.
       ===================================== */

    $sql_pedido = "

        SELECT

            id,
            usuario_id,
            total,
            metodo_pago,
            tarjeta_id,
            estado

        FROM ventas

        WHERE id = ?

        AND usuario_id = ?

        LIMIT 1

        FOR UPDATE

    ";


    $stmt_pedido =
        $conexion->prepare(
            $sql_pedido
        );


    if (!$stmt_pedido) {

        throw new Exception(
            "No se pudo consultar el pedido."
        );

    }


    $stmt_pedido->bind_param(
        "ii",
        $pedido_id,
        $usuario_id
    );


    $stmt_pedido->execute();


    $resultado_pedido =
        $stmt_pedido->get_result();


    /* =====================================
       VERIFICAR QUE EXISTA
       ===================================== */

    if (
        $resultado_pedido->num_rows !== 1
    ) {

        throw new Exception(
            "El pedido no existe o no pertenece a tu cuenta."
        );

    }


    $pedido =
        $resultado_pedido->fetch_assoc();


    /* =====================================
       VERIFICAR ESTADO

       SOLO PENDIENTE PUEDE CANCELARSE
       ===================================== */

    if (
        $pedido["estado"] !== "Pendiente"
    ) {

        throw new Exception(
            "Este pedido ya no puede cancelarse porque su estado es: "
            . $pedido["estado"]
        );

    }


    /* =====================================
       OBTENER PRODUCTOS DEL PEDIDO
       ===================================== */

    $sql_detalles = "

        SELECT

            producto_id,
            cantidad

        FROM detalle_venta

        WHERE venta_id = ?

    ";


    $stmt_detalles =
        $conexion->prepare(
            $sql_detalles
        );


    if (!$stmt_detalles) {

        throw new Exception(
            "No se pudieron consultar los productos del pedido."
        );

    }


    $stmt_detalles->bind_param(
        "i",
        $pedido_id
    );


    $stmt_detalles->execute();


    $resultado_detalles =
        $stmt_detalles->get_result();


    /* =====================================
       VERIFICAR QUE TENGA PRODUCTOS
       ===================================== */

    if (
        $resultado_detalles->num_rows === 0
    ) {

        throw new Exception(
            "El pedido no tiene productos registrados."
        );

    }


    /* =====================================
       PREPARAR DEVOLUCIÓN DE STOCK
       ===================================== */

    $sql_stock = "

        UPDATE productos

        SET stock = stock + ?

        WHERE id = ?

    ";


    $stmt_stock =
        $conexion->prepare(
            $sql_stock
        );


    if (!$stmt_stock) {

        throw new Exception(
            "No se pudo preparar la devolución del stock."
        );

    }


    /* =====================================
       PREPARAR MOVIMIENTO DE INVENTARIO
       ===================================== */

    $sql_movimiento = "

        INSERT INTO movimientos_inventario
        (
            producto_id,
            tipo,
            cantidad,
            motivo,
            usuario_id
        )

        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?
        )

    ";


    $stmt_movimiento =
        $conexion->prepare(
            $sql_movimiento
        );


    if (!$stmt_movimiento) {

        throw new Exception(
            "No se pudo preparar el movimiento de inventario."
        );

    }


    $tipo_movimiento =
        "Entrada";


    $motivo =
        "Devolución por cancelación del pedido #"
        . $pedido_id;


    /* =====================================
       DEVOLVER CADA PRODUCTO
       ===================================== */

    while (
        $detalle =
        $resultado_detalles->fetch_assoc()
    ) {


        $producto_id =
            intval(
                $detalle["producto_id"]
            );


        $cantidad =
            intval(
                $detalle["cantidad"]
            );


        /* =================================
           VALIDAR DETALLE
           ================================= */

        if (
            $producto_id <= 0 ||
            $cantidad <= 0
        ) {

            throw new Exception(
                "Se encontró un producto inválido en el pedido."
            );

        }


        /* =================================
           DEVOLVER STOCK
           ================================= */

        $stmt_stock->bind_param(
            "ii",
            $cantidad,
            $producto_id
        );


        if (
            !$stmt_stock->execute()
        ) {

            throw new Exception(
                "No se pudo devolver el stock del producto."
            );

        }


        /* =================================
           COMPROBAR QUE EXISTE EL PRODUCTO
           ================================= */

        if (
            $stmt_stock->affected_rows !== 1
        ) {

            throw new Exception(
                "Uno de los productos del pedido ya no existe."
            );

        }


        /* =================================
           REGISTRAR ENTRADA
           ================================= */

        $stmt_movimiento->bind_param(
            "isisi",
            $producto_id,
            $tipo_movimiento,
            $cantidad,
            $motivo,
            $usuario_id
        );


        if (
            !$stmt_movimiento->execute()
        ) {

            throw new Exception(
                "No se pudo registrar la devolución de inventario."
            );

        }

    }


    /* =====================================
       CAMBIAR ESTADO

       Pendiente → Cancelada
       ===================================== */

    $sql_cancelar = "

        UPDATE ventas

        SET estado = 'Cancelada'

        WHERE id = ?

        AND usuario_id = ?

        AND estado = 'Pendiente'

    ";


    $stmt_cancelar =
        $conexion->prepare(
            $sql_cancelar
        );


    if (!$stmt_cancelar) {

        throw new Exception(
            "No se pudo preparar la cancelación del pedido."
        );

    }


    $stmt_cancelar->bind_param(
        "ii",
        $pedido_id,
        $usuario_id
    );


    if (
        !$stmt_cancelar->execute()
    ) {

        throw new Exception(
            "No se pudo cambiar el estado del pedido."
        );

    }


    /* =====================================
       VERIFICAR ACTUALIZACIÓN
       ===================================== */

    if (
        $stmt_cancelar->affected_rows !== 1
    ) {

        throw new Exception(
            "El pedido ya no está disponible para cancelación."
        );

    }


    /* =====================================
       CONFIRMAR TODO
       ===================================== */

    $conexion->commit();


    /* =====================================
       REGRESAR A MIS PEDIDOS
       ===================================== */

    header(
        "Location: pedidos_clientes.php?cancelado=1"
    );

    exit();


} catch (Exception $e) {


    /* =====================================
       DESHACER TODA LA OPERACIÓN

       Si falla cualquier cosa:

       - No devuelve stock parcialmente.
       - No registra movimientos parciales.
       - No cambia el pedido.
       ===================================== */

    $conexion->rollback();


    header(
        "Location: pedidos_clientes.php?error="
        . urlencode(
            $e->getMessage()
        )
    );

    exit();

}

?>