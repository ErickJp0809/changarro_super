<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   VALIDAR ID DEL PEDIDO
   ========================================= */

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: ventas.php");
    exit();

}

$pedido_id = intval($_GET["id"]);


/* =========================================
   OBTENER INFORMACIÓN DEL PEDIDO
   ========================================= */

$sql_pedido = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.metodo_pago,
        ventas.fecha,
        ventas.estado,
        usuarios.nombre AS cliente_nombre,
        usuarios.usuario AS cliente_usuario
    FROM ventas

    LEFT JOIN usuarios
        ON ventas.usuario_id = usuarios.id

    WHERE ventas.id = ?
";


$stmt_pedido =
    $conexion->prepare($sql_pedido);


$stmt_pedido->bind_param(
    "i",
    $pedido_id
);


$stmt_pedido->execute();


$resultado_pedido =
    $stmt_pedido->get_result();


if (
    $resultado_pedido->num_rows !== 1
) {

    die("El pedido no existe.");

}


$pedido =
    $resultado_pedido->fetch_assoc();


/* =========================================
   OBTENER PRODUCTOS DEL PEDIDO
   ========================================= */

$sql_detalle = "
    SELECT
        detalle_venta.cantidad,
        detalle_venta.precio,
        detalle_venta.subtotal,
        productos.nombre
    FROM detalle_venta

    INNER JOIN productos
        ON detalle_venta.producto_id = productos.id

    WHERE detalle_venta.venta_id = ?

    ORDER BY detalle_venta.id ASC
";


$stmt_detalle =
    $conexion->prepare($sql_detalle);


$stmt_detalle->bind_param(
    "i",
    $pedido_id
);


$stmt_detalle->execute();


$resultado_detalle =
    $stmt_detalle->get_result();


/* =========================================
   INICIAL DEL USUARIO ADMIN
   ========================================= */

$inicial =
    strtoupper(
        substr(
            $_SESSION["nombre"],
            0,
            1
        )
    );

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

        Pedido #

        <?php

        echo $pedido["id"];

        ?>

        | Changarro Súper y Más

    </title>


    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >


    <style>

        /* =====================================
           CONTENEDOR
           ===================================== */

        .pedido-contenedor {

            max-width: 850px;

            margin: 0 auto;

        }


        /* =====================================
           ENCABEZADO DEL PEDIDO
           ===================================== */

        .pedido-header {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 25px;

            margin-bottom: 18px;

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

        }


        .pedido-header h2 {

            margin: 0 0 7px 0;

            font-size: 23px;

        }


        .pedido-header p {

            margin: 4px 0;

            color: #777;

            font-size: 13px;

        }


        /* =====================================
           ESTADOS
           ===================================== */

        .estado {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 700;

        }


        .estado-pagada {

            background: #e8f8ef;

            color: #16834a;

        }


        .estado-pendiente {

            background: #fff5dc;

            color: #b87500;

        }


        .estado-cancelada {

            background: #ffe8e8;

            color: #c62828;

        }


        /* =====================================
           INFORMACIÓN DEL CLIENTE
           ===================================== */

        .cliente-card {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 22px;

            margin-bottom: 18px;

        }


        .cliente-card h3 {

            margin: 0 0 15px 0;

            font-size: 17px;

        }


        .cliente-datos {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

        }


        .dato {

            background: #f8f8f8;

            border-radius: 9px;

            padding: 13px;

        }


        .dato span {

            display: block;

            color: #888;

            font-size: 11px;

            margin-bottom: 4px;

        }


        .dato strong {

            font-size: 13px;

        }


        /* =====================================
           PRODUCTOS
           ===================================== */

        .productos-card {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 22px;

        }


        .productos-card h3 {

            margin: 0 0 18px 0;

            font-size: 17px;

        }


        .tabla-pedido {

            width: 100%;

            border-collapse: collapse;

        }


        .tabla-pedido th {

            padding: 11px 8px;

            text-align: left;

            border-bottom: 1px solid #ddd;

            color: #777;

            font-size: 12px;

        }


        .tabla-pedido td {

            padding: 14px 8px;

            border-bottom: 1px solid #eee;

            font-size: 13px;

        }


        .tabla-pedido .derecha {

            text-align: right;

        }


        .tabla-pedido .centro {

            text-align: center;

        }


        /* =====================================
           TOTAL
           ===================================== */

        .pedido-total {

            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 25px;

            padding-top: 20px;

        }


        .pedido-total span {

            color: #777;

            font-size: 14px;

        }


        .pedido-total strong {

            font-size: 25px;

            color: #f7941d;

        }


        /* =====================================
           PAGO
           ===================================== */

        .pago-card {

            margin-top: 18px;

            background: #f8fafb;

            border: 1px solid #e7eaec;

            border-radius: 10px;

            padding: 15px;

            display: flex;

            justify-content: space-between;

            align-items: center;

        }


        .pago-card span {

            color: #777;

            font-size: 12px;

        }


        .pago-card strong {

            font-size: 13px;

        }


        /* =====================================
           ACCIONES
           ===================================== */

        .acciones-pedido {

            display: flex;

            justify-content: center;

            flex-wrap: wrap;

            gap: 10px;

            margin-top: 20px;

        }


        .btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 11px 18px;

            border-radius: 8px;

            text-decoration: none;

            border: none;

            cursor: pointer;

            font-size: 13px;

            font-weight: 600;

        }


        .btn-imprimir {

            background: #222;

            color: white;

        }


        .btn-regresar {

            background: #eeeeee;

            color: #222;

        }


        .btn-cancelar {

            background: #ffe8e8;

            color: #c62828;

        }


        .btn-cancelar:hover {

            background: #ffd7d7;

        }


        .pedido-cancelado {

            display: inline-flex;

            align-items: center;

            padding: 11px 18px;

            border-radius: 8px;

            background: #ffe8e8;

            color: #c62828;

            font-weight: 700;

            font-size: 13px;

        }


        /* =====================================
           IMPRESIÓN
           ===================================== */

        @media print {

            body {

                background: white;

            }


            .sidebar,
            .encabezado,
            .acciones-pedido {

                display: none !important;

            }


            .contenido {

                margin: 0;

                padding: 0;

            }


            .pedido-contenedor {

                max-width: 100%;

            }


            .pedido-header,
            .cliente-card,
            .productos-card {

                border: none;

                box-shadow: none;

            }

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (max-width: 650px) {

            .pedido-header {

                flex-direction: column;

            }


            .cliente-datos {

                grid-template-columns: 1fr;

            }


            .pedido-total {

                justify-content: space-between;

            }

        }

    </style>

</head>


<body>


<div class="admin-layout">


    <!-- =====================================
         SIDEBAR
         ===================================== -->

    <?php include "../includes/sidebar.php"; ?>


    <!-- =====================================
         CONTENIDO
         ===================================== -->

    <main class="contenido">


        <!-- =================================
             ENCABEZADO
             ================================= -->

        <header class="encabezado">


            <div>

                <h1>

                    Detalle del pedido

                </h1>


                <p>

                    Consulta la información
                    de la compra realizada en línea.

                </p>

            </div>


            <!-- PERFIL -->

            <div class="perfil">


                <div class="avatar">

                    <?php

                    echo htmlspecialchars(
                        $inicial
                    );

                    ?>

                </div>


                <div>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["nombre"]
                        );

                        ?>

                    </strong>


                    <span>

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["rol"]
                        );

                        ?>

                    </span>

                </div>


            </div>


        </header>


        <!-- =================================
             PEDIDO
             ================================= -->

        <div class="pedido-contenedor">


            <!-- =================================
                 INFORMACIÓN GENERAL
                 ================================= -->

            <section class="pedido-header">


                <div>

                    <h2>

                        Pedido #

                        <?php

                        echo intval(
                            $pedido["id"]
                        );

                        ?>

                    </h2>


                    <p>

                        Fecha:

                        <strong>

                            <?php

                            echo date(
                                "d/m/Y H:i",
                                strtotime(
                                    $pedido["fecha"]
                                )
                            );

                            ?>

                        </strong>

                    </p>


                    <p>

                        Compra realizada
                        desde la tienda en línea.

                    </p>

                </div>


                <div>


                    <?php

                    if (
                        $pedido["estado"]
                        === "Cancelada"
                    ) {

                        $clase_estado =
                            "estado-cancelada";

                        $texto_estado =
                            "Cancelada";

                    } elseif (
                        $pedido["estado"]
                        === "Pendiente"
                    ) {

                        $clase_estado =
                            "estado-pendiente";

                        $texto_estado =
                            "Pendiente";

                    } else {

                        $clase_estado =
                            "estado-pagada";

                        $texto_estado =
                            "Pagada";

                    }

                    ?>


                    <span
                        class="estado <?php echo $clase_estado; ?>"
                    >

                        ●

                        <?php

                        echo $texto_estado;

                        ?>

                    </span>


                </div>


            </section>


            <!-- =================================
                 CLIENTE
                 ================================= -->

            <section class="cliente-card">


                <h3>

                    Información del cliente

                </h3>


                <div class="cliente-datos">


                    <div class="dato">


                        <span>

                            Nombre

                        </span>


                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $pedido[
                                    "cliente_nombre"
                                ]
                                ??
                                "No disponible"
                            );

                            ?>

                        </strong>


                    </div>


                    <div class="dato">


                        <span>

                            Usuario

                        </span>


                        <strong>

                            @

                            <?php

                            echo htmlspecialchars(
                                $pedido[
                                    "cliente_usuario"
                                ]
                                ??
                                "No disponible"
                            );

                            ?>

                        </strong>


                    </div>


                </div>


            </section>


            <!-- =================================
                 PRODUCTOS
                 ================================= -->

            <section class="productos-card">


                <h3>

                    Productos del pedido

                </h3>


                <div
                    style="
                        overflow-x:auto;
                    "
                >


                    <table
                        class="tabla-pedido"
                    >


                        <thead>

                            <tr>

                                <th>

                                    Producto

                                </th>


                                <th class="centro">

                                    Cantidad

                                </th>


                                <th class="derecha">

                                    Precio

                                </th>


                                <th class="derecha">

                                    Subtotal

                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (
                            $resultado_detalle &&
                            $resultado_detalle->num_rows > 0
                        ): ?>


                            <?php while (
                                $detalle =
                                $resultado_detalle
                                ->fetch_assoc()
                            ): ?>


                                <tr>


                                    <td>

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $detalle["nombre"]
                                            );

                                            ?>

                                        </strong>

                                    </td>


                                    <td
                                        class="centro"
                                    >

                                        <?php

                                        echo intval(
                                            $detalle["cantidad"]
                                        );

                                        ?>

                                    </td>


                                    <td
                                        class="derecha"
                                    >

                                        $

                                        <?php

                                        echo number_format(
                                            $detalle["precio"],
                                            2
                                        );

                                        ?>

                                    </td>


                                    <td
                                        class="derecha"
                                    >

                                        $

                                        <?php

                                        echo number_format(
                                            $detalle["subtotal"],
                                            2
                                        );

                                        ?>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="4"
                                    style="
                                        text-align:center;
                                        color:#888;
                                        padding:30px;
                                    "
                                >

                                    Este pedido no tiene
                                    productos registrados.

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>


                </div>


                <!-- TOTAL -->

                <div class="pedido-total">


                    <span>

                        Total del pedido

                    </span>


                    <strong>

                        $

                        <?php

                        echo number_format(
                            $pedido["total"],
                            2
                        );

                        ?>

                    </strong>


                </div>


                <!-- MÉTODO DE PAGO -->

                <div class="pago-card">


                    <span>

                        Método de pago

                    </span>


                    <strong>

                        💳 Tarjeta

                    </strong>


                </div>


            </section>


            <!-- =================================
                 ACCIONES
                 ================================= -->

            <div class="acciones-pedido">


                <button
                    type="button"
                    class="btn btn-imprimir"
                    onclick="window.print()"
                >

                    🖨 Imprimir pedido

                </button>


                <?php if (
                    $pedido["estado"] !==
                    "Cancelada"
                ): ?>


                    <a
                        href="cancelar_venta.php?id=<?php echo intval($pedido["id"]); ?>"
                        class="btn btn-cancelar"
                        onclick="
                            return confirm(
                                '¿Seguro que deseas cancelar este pedido? El stock será devuelto al inventario.'
                            );
                        "
                    >

                        ✕ Cancelar pedido

                    </a>


                <?php else: ?>


                    <span
                        class="pedido-cancelado"
                    >

                        Pedido cancelado

                    </span>


                <?php endif; ?>


                <a
                    href="ventas.php"
                    class="btn btn-regresar"
                >

                    ← Volver a pedidos

                </a>


            </div>


        </div>


    </main>


</div>


</body>

</html>