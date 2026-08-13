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
   VERIFICAR CLIENTE
   ========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Cliente"
) {

    header("Location: ../admin/dashboard.php");
    exit();

}


$usuario_id = intval($_SESSION["id"]);

$mensaje = "";
$tipo_mensaje = "";


/* =========================================
   VERIFICAR CARRITO
   ========================================= */

if (
    !isset($_SESSION["carrito"]) ||
    empty($_SESSION["carrito"])
) {

    header("Location: carrito_clientes.php");
    exit();

}


/* =========================================
   AGREGAR NUEVA TARJETA
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "agregar_tarjeta"
) {


    $numero_tarjeta =
        preg_replace(
            "/\D/",
            "",
            $_POST["numero_tarjeta"] ?? ""
        );


    $titular =
        trim(
            $_POST["titular"] ?? ""
        );


    $mes =
        trim(
            $_POST["mes_expiracion"] ?? ""
        );


    $anio =
        trim(
            $_POST["anio_expiracion"] ?? ""
        );


    /* =====================================
       VALIDAR NÚMERO
       ===================================== */

    if (
        strlen($numero_tarjeta) < 13 ||
        strlen($numero_tarjeta) > 19
    ) {

        $mensaje =
            "El número de tarjeta no es válido.";

        $tipo_mensaje = "error";

    }


    /* =====================================
       VALIDAR TITULAR
       ===================================== */

    elseif ($titular === "") {

        $mensaje =
            "Ingresa el nombre del titular.";

        $tipo_mensaje = "error";

    }


    /* =====================================
       VALIDAR MES
       ===================================== */

    elseif (
        !ctype_digit($mes) ||
        intval($mes) < 1 ||
        intval($mes) > 12
    ) {

        $mensaje =
            "El mes de vencimiento no es válido.";

        $tipo_mensaje = "error";

    }


    /* =====================================
       VALIDAR AÑO
       ===================================== */

    elseif (
        !ctype_digit($anio) ||
        strlen($anio) !== 4
    ) {

        $mensaje =
            "El año de vencimiento no es válido.";

        $tipo_mensaje = "error";

    }


    else {


        /* =================================
           OBTENER ÚLTIMOS 4
           ================================= */

        $ultimos4 =
            substr(
                $numero_tarjeta,
                -4
            );


        /* =================================
           DETECTAR TIPO DE TARJETA
           ================================= */

        $primer_digito =
            substr(
                $numero_tarjeta,
                0,
                1
            );


        if ($primer_digito === "4") {

            $tipo = "Visa";

        } elseif ($primer_digito === "5") {

            $tipo = "Mastercard";

        } else {

            $tipo = "Tarjeta";

        }


        /* =================================
           GUARDAR TARJETA
           ================================= */

        $sql = "
            INSERT INTO tarjetas
            (
                usuario_id,
                tipo,
                ultimos4,
                titular,
                mes_expiracion,
                anio_expiracion
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";


        $stmt =
            $conexion->prepare($sql);


        if (!$stmt) {

            $mensaje =
                "No se pudo preparar el registro de la tarjeta.";

            $tipo_mensaje = "error";

        } else {


            $stmt->bind_param(
                "isssss",
                $usuario_id,
                $tipo,
                $ultimos4,
                $titular,
                $mes,
                $anio
            );


            if ($stmt->execute()) {

                /*
                 * No guardamos:
                 * - número completo
                 * - CVV
                 */

                header(
                    "Location: confirmar_pedido_clientes.php?tarjeta_agregada=1"
                );

                exit();

            } else {

                $mensaje =
                    "No se pudo guardar la tarjeta.";

                $tipo_mensaje = "error";

            }

        }

    }

}


/* =========================================
   OBTENER TARJETAS DEL CLIENTE
   ========================================= */

$sql_tarjetas = "
    SELECT
        id,
        tipo,
        ultimos4,
        titular,
        mes_expiracion,
        anio_expiracion
    FROM tarjetas
    WHERE usuario_id = ?
    AND activa = 1
    ORDER BY id DESC
";


$stmt_tarjetas =
    $conexion->prepare($sql_tarjetas);


$stmt_tarjetas->bind_param(
    "i",
    $usuario_id
);


$stmt_tarjetas->execute();


$resultado_tarjetas =
    $stmt_tarjetas->get_result();


$tarjetas = [];


while (
    $tarjeta =
    $resultado_tarjetas->fetch_assoc()
) {

    $tarjetas[] = $tarjeta;

}


/* =========================================
   TARJETA AGREGADA
   ========================================= */

$tarjeta_agregada =
    isset($_GET["tarjeta_agregada"]);


/* =========================================
   PROCESAR PEDIDO
   ========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["accion"]) &&
    $_POST["accion"] === "confirmar_pedido"
) {


    $tarjeta_id =
        intval(
            $_POST["tarjeta_id"] ?? 0
        );


    /* =====================================
       VALIDAR TARJETA
       ===================================== */

    if ($tarjeta_id <= 0) {

        $mensaje =
            "Selecciona una tarjeta para continuar.";

        $tipo_mensaje = "error";

    } else {


        /* =================================
           COMPROBAR QUE LA TARJETA PERTENECE
           AL CLIENTE
           ================================= */

        $sql_tarjeta = "
            SELECT
                id
            FROM tarjetas
            WHERE id = ?
            AND usuario_id = ?
            AND activa = 1
        ";


        $stmt_tarjeta =
            $conexion->prepare(
                $sql_tarjeta
            );


        $stmt_tarjeta->bind_param(
            "ii",
            $tarjeta_id,
            $usuario_id
        );


        $stmt_tarjeta->execute();


        $resultado_tarjeta =
            $stmt_tarjeta->get_result();


        if (
            $resultado_tarjeta->num_rows !== 1
        ) {

            $mensaje =
                "La tarjeta seleccionada no es válida.";

            $tipo_mensaje = "error";

        } else {


            /* =============================
               TRANSACCIÓN
               ============================= */

            $conexion->begin_transaction();


            try {


                $total = 0;

                $detalles = [];


                /* =============================
                   VALIDAR PRODUCTOS Y STOCK
                   ============================= */

                foreach (
                    $_SESSION["carrito"]
                    as $item
                ) {


                    $producto_id =
                        intval(
                            $item["id"]
                        );


                    $cantidad =
                        intval(
                            $item["cantidad"]
                        );


                    if (
                        $producto_id <= 0 ||
                        $cantidad <= 0
                    ) {

                        throw new Exception(
                            "Hay un producto inválido en el carrito."
                        );

                    }


                    /* =========================
                       BUSCAR PRODUCTO
                       ========================= */

                    $sql_producto = "
                        SELECT
                            id,
                            nombre,
                            precio_venta,
                            stock,
                            activo
                        FROM productos
                        WHERE id = ?
                        FOR UPDATE
                    ";


                    $stmt_producto =
                        $conexion->prepare(
                            $sql_producto
                        );


                    $stmt_producto->bind_param(
                        "i",
                        $producto_id
                    );


                    $stmt_producto->execute();


                    $resultado_producto =
                        $stmt_producto->get_result();


                    if (
                        $resultado_producto->num_rows !== 1
                    ) {

                        throw new Exception(
                            "Uno de los productos ya no existe."
                        );

                    }


                    $producto =
                        $resultado_producto->fetch_assoc();


                    /* =========================
                       COMPROBAR ACTIVO
                       ========================= */

                    if (
                        intval(
                            $producto["activo"]
                        ) !== 1
                    ) {

                        throw new Exception(
                            "El producto " .
                            $producto["nombre"] .
                            " ya no está disponible."
                        );

                    }


                    /* =========================
                       COMPROBAR STOCK
                       ========================= */

                    if (
                        $cantidad >
                        intval(
                            $producto["stock"]
                        )
                    ) {

                        throw new Exception(
                            "No hay suficiente stock de " .
                            $producto["nombre"] .
                            ". Disponible: " .
                            $producto["stock"]
                        );

                    }


                    $precio =
                        floatval(
                            $producto["precio_venta"]
                        );


                    $subtotal =
                        $precio *
                        $cantidad;


                    $total +=
                        $subtotal;


                    $detalles[] = [

                        "id" =>
                            $producto_id,

                        "nombre" =>
                            $producto["nombre"],

                        "cantidad" =>
                            $cantidad,

                        "precio" =>
                            $precio,

                        "subtotal" =>
                            $subtotal,

                        "stock" =>
                            intval(
                                $producto["stock"]
                            )

                    ];

                }


                $total =
                    round(
                        $total,
                        2
                    );


                /* =============================
                   CREAR VENTA
                   ============================= */

                $metodo_pago =
                    "Tarjeta";


                $sql_venta = "
                    INSERT INTO ventas
                    (
                        total,
                        metodo_pago,
                        usuario_id,
                        tarjeta_id
                    )
                    VALUES (?, ?, ?, ?)
                ";


                $stmt_venta =
                    $conexion->prepare(
                        $sql_venta
                    );


                $stmt_venta->bind_param(
                    "dsii",
                    $total,
                    $metodo_pago,
                    $usuario_id,
                    $tarjeta_id
                );


                if (
                    !$stmt_venta->execute()
                ) {

                    throw new Exception(
                        "No se pudo registrar el pedido."
                    );

                }


                $venta_id =
                    $conexion->insert_id;


                /* =============================
                   GUARDAR DETALLES
                   ============================= */

                foreach (
                    $detalles
                    as $detalle
                ) {


                    $producto_id =
                        $detalle["id"];


                    $cantidad =
                        $detalle["cantidad"];


                    $precio =
                        $detalle["precio"];


                    $subtotal =
                        $detalle["subtotal"];


                    $nuevo_stock =
                        $detalle["stock"] -
                        $cantidad;


                    /* =========================
                       DETALLE DE VENTA
                       ========================= */

                    $sql_detalle = "
                        INSERT INTO detalle_venta
                        (
                            venta_id,
                            producto_id,
                            cantidad,
                            precio,
                            subtotal
                        )
                        VALUES (?, ?, ?, ?, ?)
                    ";


                    $stmt_detalle =
                        $conexion->prepare(
                            $sql_detalle
                        );


                    $stmt_detalle->bind_param(
                        "iiidd",
                        $venta_id,
                        $producto_id,
                        $cantidad,
                        $precio,
                        $subtotal
                    );


                    if (
                        !$stmt_detalle->execute()
                    ) {

                        throw new Exception(
                            "No se pudo guardar el detalle del pedido."
                        );

                    }


                    /* =========================
                       ACTUALIZAR STOCK
                       ========================= */

                    $sql_stock = "
                        UPDATE productos
                        SET stock = ?
                        WHERE id = ?
                    ";


                    $stmt_stock =
                        $conexion->prepare(
                            $sql_stock
                        );


                    $stmt_stock->bind_param(
                        "ii",
                        $nuevo_stock,
                        $producto_id
                    );


                    if (
                        !$stmt_stock->execute()
                    ) {

                        throw new Exception(
                            "No se pudo actualizar el stock."
                        );

                    }


                    /* =========================
                       REGISTRAR MOVIMIENTO
                       ========================= */

                    $motivo =
                        "Venta en línea #" .
                        $venta_id;


                    $tipo_movimiento =
                        "Salida";


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


                    $stmt_movimiento =
                        $conexion->prepare(
                            $sql_movimiento
                        );


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
                            "No se pudo registrar el movimiento de inventario."
                        );

                    }

                }


                /* =============================
                   CONFIRMAR TRANSACCIÓN
                   ============================= */

                $conexion->commit();


                /* =============================
                   VACIAR CARRITO
                   ============================= */

                $_SESSION["carrito"] = [];


                /* =============================
                   REDIRIGIR
                   ============================= */

                header(
                    "Location: pedidos_clientes.php?pedido=exito"
                );

                exit();


            } catch (
                Exception $e
            ) {


                $conexion->rollback();


                $mensaje =
                    $e->getMessage();


                $tipo_mensaje =
                    "error";

            }

        }

    }

}


/* =========================================
   CALCULAR TOTAL DEL CARRITO
   ========================================= */

$total = 0;

$cantidad_productos = 0;


foreach (
    $_SESSION["carrito"]
    as $item
) {


    $cantidad =
        intval(
            $item["cantidad"]
        );


    $precio =
        floatval(
            $item["precio"]
        );


    $total +=
        $precio *
        $cantidad;


    $cantidad_productos +=
        $cantidad;

}


/* =========================================
   INICIAL DEL CLIENTE
   ========================================= */

$inicial_cliente =
    strtoupper(
        substr(
            $_SESSION["nombre"],
            0,
            1
        )
    );


/* =========================================
   ABRIR MODAL AUTOMÁTICAMENTE
   ========================================= */

$abrir_modal =
    count($tarjetas) === 0;

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Confirmar pedido | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/clientes.css"
    >


    <style>

        /* =====================================
           CONTENEDOR
           ===================================== */

        .confirmar-contenedor {

            max-width: 1100px;

            margin: 0 auto;

        }


        .confirmar-grid {

            display: grid;

            grid-template-columns:
                1fr
                360px;

            gap: 25px;

            align-items: start;

        }


        /* =====================================
           TARJETAS
           ===================================== */

        .tarjetas-panel {

            background: white;

            border: 1px solid #e8e8e8;

            border-radius: 16px;

            padding: 25px;

        }


        .tarjetas-panel h2 {

            margin: 0 0 7px;

            font-size: 21px;

        }


        .tarjetas-panel > p {

            margin: 0 0 22px;

            color: #777;

            font-size: 14px;

        }


        .tarjeta-opcion {

            position: relative;

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 17px;

            margin-bottom: 12px;

            border: 1px solid #ddd;

            border-radius: 12px;

            cursor: pointer;

            transition: .2s;

        }


        .tarjeta-opcion:hover {

            border-color: #f7941d;

        }


        .tarjeta-opcion.seleccionada {

            border: 2px solid #f7941d;

            background: #fffaf4;

        }


        .tarjeta-radio {

            position: absolute;

            opacity: 0;

        }


        .tarjeta-icono {

            width: 48px;

            height: 32px;

            border-radius: 7px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #f7941d;

            color: white;

            font-size: 18px;

        }


        .tarjeta-info {

            flex: 1;

        }


        .tarjeta-info strong {

            display: block;

            margin-bottom: 4px;

        }


        .tarjeta-numero {

            color: #555;

            font-size: 14px;

        }


        .tarjeta-titular {

            color: #888;

            font-size: 12px;

            margin-top: 4px;

        }


        .tarjeta-vencimiento {

            color: #888;

            font-size: 12px;

            margin-top: 2px;

        }


        .tarjeta-check {

            width: 20px;

            height: 20px;

            border: 2px solid #ccc;

            border-radius: 50%;

        }


        .tarjeta-opcion.seleccionada
        .tarjeta-check {

            border: 6px solid #f7941d;

        }


        /* =====================================
           AGREGAR TARJETA
           ===================================== */

        .btn-agregar-tarjeta {

            width: 100%;

            height: 45px;

            margin-top: 10px;

            border: 1px dashed #f7941d;

            border-radius: 9px;

            background: #fffaf4;

            color: #d9780e;

            font-weight: bold;

            cursor: pointer;

        }


        .btn-agregar-tarjeta:hover {

            background: #fff3e5;

        }


        /* =====================================
           RESUMEN
           ===================================== */

        .resumen-pedido {

            background: white;

            border: 1px solid #e8e8e8;

            border-radius: 16px;

            padding: 25px;

            position: sticky;

            top: 25px;

        }


        .resumen-pedido h2 {

            margin-top: 0;

            margin-bottom: 20px;

            font-size: 20px;

        }


        .resumen-linea {

            display: flex;

            justify-content: space-between;

            padding: 12px 0;

            border-bottom: 1px solid #eee;

            font-size: 14px;

        }


        .resumen-linea span {

            color: #777;

        }


        .resumen-total {

            display: flex;

            justify-content: space-between;

            padding-top: 18px;

            font-weight: bold;

        }


        .resumen-total strong {

            color: #f7941d;

            font-size: 24px;

        }


        .btn-confirmar {

            width: 100%;

            height: 48px;

            margin-top: 20px;

            border: none;

            border-radius: 9px;

            background: #f7941d;

            color: white;

            font-weight: bold;

            cursor: pointer;

        }


        .btn-confirmar:hover {

            background: #e98212;

        }


        .volver-carrito {

            display: block;

            margin-top: 15px;

            text-align: center;

            color: #249db5;

            text-decoration: none;

            font-size: 13px;

        }


        /* =====================================
           MENSAJES
           ===================================== */

        .mensaje {

            padding: 13px 16px;

            margin-bottom: 18px;

            border-radius: 9px;

            font-size: 14px;

        }


        .mensaje-error {

            background: #fff0f0;

            color: #c62828;

            border: 1px solid #ffcaca;

        }


        .mensaje-exito {

            background: #edf9f1;

            color: #18864b;

            border: 1px solid #bce8ce;

        }


        /* =====================================
           MODAL
           ===================================== */

        .modal {

            display: none;

            position: fixed;

            inset: 0;

            z-index: 9999;

            align-items: center;

            justify-content: center;

            background:
                rgba(0, 0, 0, .55);

            padding: 20px;

        }


        .modal.mostrar {

            display: flex;

        }


        .modal-contenido {

            position: relative;

            width: 100%;

            max-width: 470px;

            max-height: 90vh;

            overflow-y: auto;

            padding: 28px;

            background: white;

            border-radius: 16px;

        }


        .modal-cerrar {

            position: absolute;

            top: 10px;

            right: 15px;

            width: 35px;

            height: 35px;

            border: none;

            background: transparent;

            font-size: 25px;

            cursor: pointer;

            color: #777;

        }


        .modal-contenido h2 {

            margin-top: 0;

            margin-bottom: 7px;

        }


        .modal-contenido > p {

            color: #777;

            font-size: 14px;

            margin-bottom: 22px;

        }


        .campo {

            margin-bottom: 15px;

        }


        .campo label {

            display: block;

            margin-bottom: 7px;

            font-size: 13px;

            font-weight: bold;

        }


        .campo input {

            width: 100%;

            height: 43px;

            box-sizing: border-box;

            padding: 0 12px;

            border: 1px solid #ddd;

            border-radius: 8px;

            outline: none;

            font-size: 14px;

        }


        .campo input:focus {

            border-color: #f7941d;

            box-shadow:
                0 0 0 3px
                rgba(247, 148, 29, .12);

        }


        .fila-campos {

            display: grid;

            grid-template-columns:
                1fr
                1fr;

            gap: 12px;

        }


        .btn-guardar-tarjeta {

            width: 100%;

            height: 45px;

            margin-top: 5px;

            border: none;

            border-radius: 9px;

            background: #f7941d;

            color: white;

            font-weight: bold;

            cursor: pointer;

        }


        .seguridad {

            margin-top: 14px;

            color: #888;

            font-size: 11px;

            line-height: 1.5;

            text-align: center;

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (max-width: 850px) {

            .confirmar-grid {

                grid-template-columns: 1fr;

            }


            .resumen-pedido {

                position: static;

            }

        }


        @media (max-width: 500px) {

            .fila-campos {

                grid-template-columns: 1fr;

            }

        }

    </style>

</head>


<body>


<div class="cliente-layout">


    <!-- =====================================
         SIDEBAR
         ===================================== -->

    <aside class="cliente-sidebar">


        <div class="cliente-marca">

            <img
                src="../img/logo_changarro_transparente.png"
                alt="Changarro Súper y Más"
            >

        </div>


        <nav class="cliente-menu">


            <a href="dashboard_clientes.php">

                <span>⌂</span>

                Inicio

            </a>


            <a href="productos_clientes.php">

                <span>▣</span>

                Productos

            </a>


            <a
                href="carrito_clientes.php"
                class="activo"
            >

                <span>🛒</span>

                Mi carrito

            </a>


            <a href="pedidos_clientes.php">

                <span>▤</span>

                Mis pedidos

            </a>


            <a href="perfil_clientes.php">

                <span>♙</span>

                Mi cuenta

            </a>


        </nav>


        <div class="cliente-salir">

            <a href="../logout.php">

                Cerrar sesión

            </a>

        </div>


    </aside>


    <!-- =====================================
         CONTENIDO
         ===================================== -->

    <main class="cliente-contenido">


        <header class="cliente-encabezado">


            <div>

                <h1>
                    Confirmar pedido
                </h1>

                <p>
                    Selecciona la tarjeta con la que deseas realizar tu compra.
                </p>

            </div>


            <div class="cliente-avatar">

                <?php

                echo htmlspecialchars(
                    $inicial_cliente
                );

                ?>

            </div>


        </header>


        <div class="confirmar-contenedor">


            <?php if ($mensaje !== ""): ?>

                <div
                    class="mensaje <?php

                        echo (
                            $tipo_mensaje === "error"
                        )
                        ? "mensaje-error"
                        : "mensaje-exito";

                    ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $mensaje
                    );

                    ?>

                </div>

            <?php endif; ?>


            <div class="confirmar-grid">


                <!-- =================================
                     TARJETAS
                     ================================= -->

                <section class="tarjetas-panel">


                    <h2>
                        Método de pago
                    </h2>


                    <p>
                        Selecciona una tarjeta registrada
                        para realizar tu pedido.
                    </p>


                    <?php if (
                        count($tarjetas) > 0
                    ): ?>


                        <form
                            method="POST"
                            id="formPedido"
                        >


                            <input
                                type="hidden"
                                name="accion"
                                value="confirmar_pedido"
                            >


                            <?php foreach (
                                $tarjetas
                                as $indice => $tarjeta
                            ): ?>


                                <label
                                    class="tarjeta-opcion <?php

                                        echo (
                                            $indice === 0 &&
                                            !$tarjeta_agregada
                                        )
                                        ? "seleccionada"
                                        : "";

                                    ?>"
                                >


                                    <input
                                        type="radio"
                                        class="tarjeta-radio"
                                        name="tarjeta_id"
                                        value="<?php

                                            echo intval(
                                                $tarjeta["id"]
                                            );

                                        ?>"
                                        <?php

                                        echo (
                                            $indice === 0 &&
                                            !$tarjeta_agregada
                                        )
                                        ? "checked"
                                        : "";

                                        ?>
                                    >


                                    <div class="tarjeta-icono">

                                        💳

                                    </div>


                                    <div class="tarjeta-info">


                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $tarjeta["tipo"]
                                            );

                                            ?>

                                        </strong>


                                        <div
                                            class="tarjeta-numero"
                                        >

                                            •••• •••• ••••

                                            <?php

                                            echo htmlspecialchars(
                                                $tarjeta["ultimos4"]
                                            );

                                            ?>

                                        </div>


                                        <div
                                            class="tarjeta-titular"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $tarjeta["titular"]
                                            );

                                            ?>

                                        </div>


                                        <div
                                            class="tarjeta-vencimiento"
                                        >

                                            Vence:

                                            <?php

                                            echo htmlspecialchars(
                                                $tarjeta["mes_expiracion"]
                                            );

                                            ?>

                                            /

                                            <?php

                                            echo htmlspecialchars(
                                                $tarjeta["anio_expiracion"]
                                            );

                                            ?>

                                        </div>


                                    </div>


                                    <div
                                        class="tarjeta-check"
                                    ></div>


                                </label>


                            <?php endforeach; ?>


                            <button
                                type="button"
                                class="btn-agregar-tarjeta"
                                onclick="abrirModal()"
                            >

                                + Agregar nueva tarjeta

                            </button>


                        </form>


                    <?php else: ?>


                        <div
                            style="
                                text-align:center;
                                padding:25px 10px;
                                color:#777;
                            "
                        >

                            <div
                                style="
                                    font-size:42px;
                                    margin-bottom:10px;
                                "
                            >
                                💳
                            </div>


                            <h3
                                style="
                                    color:#222;
                                    margin-bottom:8px;
                                "
                            >

                                No tienes tarjetas registradas

                            </h3>


                            <p
                                style="
                                    margin-bottom:20px;
                                "
                            >

                                Agrega una tarjeta para
                                continuar con tu pedido.

                            </p>


                            <button
                                type="button"
                                class="btn-agregar-tarjeta"
                                onclick="abrirModal()"
                            >

                                + Agregar tarjeta

                            </button>

                        </div>


                    <?php endif; ?>


                </section>


                <!-- =================================
                     RESUMEN
                     ================================= -->

                <aside class="resumen-pedido">


                    <h2>
                        Resumen de compra
                    </h2>


                    <div class="resumen-linea">

                        <span>
                            Productos
                        </span>

                        <strong>

                            <?php

                            echo $cantidad_productos;

                            ?>

                        </strong>

                    </div>


                    <div class="resumen-linea">

                        <span>
                            Subtotal
                        </span>

                        <strong>

                            $

                            <?php

                            echo number_format(
                                $total,
                                2
                            );

                            ?>

                        </strong>

                    </div>


                    <div class="resumen-total">

                        <span>
                            Total
                        </span>

                        <strong>

                            $

                            <?php

                            echo number_format(
                                $total,
                                2
                            );

                            ?>

                        </strong>

                    </div>


                    <?php if (
                        count($tarjetas) > 0
                    ): ?>


                        <button
                            type="submit"
                            form="formPedido"
                            class="btn-confirmar"
                        >

                            Confirmar pedido

                        </button>


                    <?php endif; ?>


                    <a
                        href="carrito_clientes.php"
                        class="volver-carrito"
                    >

                        ← Volver al carrito

                    </a>


                </aside>


            </div>


        </div>


    </main>


</div>


<!-- =========================================
     MODAL NUEVA TARJETA
     ========================================= -->

<div
    id="modalTarjeta"
    class="modal <?php

        echo $abrir_modal
        ? "mostrar"
        : "";

    ?>"
>


    <div class="modal-contenido">


        <button
            type="button"
            class="modal-cerrar"
            onclick="cerrarModal()"
        >

            ×

        </button>


        <h2>
            Agregar tarjeta
        </h2>


        <p>

            Registra tu tarjeta para utilizarla
            en tus próximas compras.

        </p>


        <form
            method="POST"
        >


            <input
                type="hidden"
                name="accion"
                value="agregar_tarjeta"
            >


            <!-- NÚMERO -->

            <div class="campo">


                <label
                    for="numero_tarjeta"
                >

                    Número de tarjeta

                </label>


                <input
                    type="text"
                    id="numero_tarjeta"
                    name="numero_tarjeta"
                    maxlength="19"
                    inputmode="numeric"
                    autocomplete="cc-number"
                    placeholder="1234 5678 9012 3456"
                    required
                >


            </div>


            <!-- TITULAR -->

            <div class="campo">


                <label
                    for="titular"
                >

                    Nombre del titular

                </label>


                <input
                    type="text"
                    id="titular"
                    name="titular"
                    maxlength="100"
                    autocomplete="cc-name"
                    placeholder="Nombre como aparece en la tarjeta"
                    required
                >


            </div>


            <!-- VENCIMIENTO -->

            <div class="fila-campos">


                <div class="campo">


                    <label
                        for="mes_expiracion"
                    >

                        Mes

                    </label>


                    <input
                        type="text"
                        id="mes_expiracion"
                        name="mes_expiracion"
                        maxlength="2"
                        inputmode="numeric"
                        placeholder="MM"
                        required
                    >


                </div>


                <div class="campo">


                    <label
                        for="anio_expiracion"
                    >

                        Año

                    </label>


                    <input
                        type="text"
                        id="anio_expiracion"
                        name="anio_expiracion"
                        maxlength="4"
                        inputmode="numeric"
                        placeholder="AAAA"
                        required
                    >


                </div>


            </div>


            <!-- CVV -->

            <div class="campo">


                <label
                    for="cvv"
                >

                    CVV

                </label>


                <input
                    type="password"
                    id="cvv"
                    name="cvv"
                    maxlength="4"
                    inputmode="numeric"
                    autocomplete="cc-csc"
                    placeholder="•••"
                    required
                >


            </div>


            <button
                type="submit"
                class="btn-guardar-tarjeta"
            >

                Guardar tarjeta

            </button>


            <div class="seguridad">

                🔒 Por seguridad, el número completo de la
                tarjeta y el CVV no se almacenan.

            </div>


        </form>


    </div>


</div>


<script>


/* =========================================
   MODAL
   ========================================= */

function abrirModal() {

    document
        .getElementById("modalTarjeta")
        .classList
        .add("mostrar");

}


function cerrarModal() {

    document
        .getElementById("modalTarjeta")
        .classList
        .remove("mostrar");

}


/* =========================================
   CERRAR AL HACER CLIC FUERA
   ========================================= */

document
    .getElementById("modalTarjeta")
    .addEventListener(
        "click",
        function(evento) {

            if (
                evento.target === this
            ) {

                cerrarModal();

            }

        }
    );


/* =========================================
   SELECCIÓN DE TARJETA
   ========================================= */

document
    .querySelectorAll(".tarjeta-opcion")
    .forEach(
        function(tarjeta) {

            tarjeta.addEventListener(
                "click",
                function() {


                    document
                        .querySelectorAll(
                            ".tarjeta-opcion"
                        )
                        .forEach(
                            function(elemento) {

                                elemento
                                    .classList
                                    .remove(
                                        "seleccionada"
                                    );

                            }
                        );


                    this
                        .classList
                        .add(
                            "seleccionada"
                        );


                    const radio =
                        this.querySelector(
                            ".tarjeta-radio"
                        );


                    radio.checked = true;

                }
            );

        }
    );


/* =========================================
   FORMATEAR NÚMERO DE TARJETA
   ========================================= */

document
    .getElementById("numero_tarjeta")
    ?.addEventListener(
        "input",
        function() {

            let valor =
                this.value
                .replace(/\D/g, "")
                .substring(0, 19);


            let grupos =
                valor.match(/.{1,4}/g);


            this.value =
                grupos
                ? grupos.join(" ")
                : "";

        }
    );


/* =========================================
   SOLO NÚMEROS
   ========================================= */

[
    "mes_expiracion",
    "anio_expiracion",
    "cvv"
]
.forEach(
    function(id) {

        document
            .getElementById(id)
            ?.addEventListener(
                "input",
                function() {

                    this.value =
                        this.value.replace(
                            /\D/g,
                            ""
                        );

                }
            );

    }
);


</script>


</body>

</html>