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


/* =========================================
   OBTENER ID DEL PEDIDO
   ========================================= */

$pedido_id = intval(
    $_GET["id"] ?? 0
);


if ($pedido_id <= 0) {

    header("Location: pedidos_clientes.php");
    exit();

}


/* =========================================
   OBTENER PEDIDO
   ========================================= */

$sql_pedido = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.fecha,
        ventas.estado,
        ventas.metodo_pago,
        tarjetas.tipo AS tarjeta_tipo,
        tarjetas.ultimos4 AS tarjeta_ultimos4
    FROM ventas

    LEFT JOIN tarjetas
        ON ventas.tarjeta_id = tarjetas.id

    WHERE ventas.id = ?
    AND ventas.usuario_id = ?
";


$stmt_pedido =
    $conexion->prepare(
        $sql_pedido
    );


$stmt_pedido->bind_param(
    "ii",
    $pedido_id,
    $usuario_id
);


$stmt_pedido->execute();


$resultado_pedido =
    $stmt_pedido->get_result();


/* =========================================
   COMPROBAR PEDIDO
   ========================================= */

if (
    $resultado_pedido->num_rows !== 1
) {

    header(
        "Location: pedidos_clientes.php"
    );

    exit();

}


$pedido =
    $resultado_pedido->fetch_assoc();


/* =========================================
   OBTENER PRODUCTOS DEL PEDIDO
   ========================================= */

$sql_detalles = "
    SELECT
        detalle_venta.cantidad,
        detalle_venta.precio,
        detalle_venta.subtotal,
        productos.nombre,
        productos.codigo
    FROM detalle_venta

    INNER JOIN productos
        ON detalle_venta.producto_id = productos.id

    WHERE detalle_venta.venta_id = ?

    ORDER BY detalle_venta.id ASC
";


$stmt_detalles =
    $conexion->prepare(
        $sql_detalles
    );


$stmt_detalles->bind_param(
    "i",
    $pedido_id
);


$stmt_detalles->execute();


$resultado_detalles =
    $stmt_detalles->get_result();


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
        Pedido #<?php echo $pedido_id; ?>
        | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/clientes.css"
    >


    <style>

        /* =====================================
           CONTENEDOR
           ===================================== */

        .detalle-pedido {

            max-width: 1050px;

            margin: 0 auto;

        }


        /* =====================================
           ENCABEZADO PEDIDO
           ===================================== */

        .detalle-cabecera {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 15px;

            padding: 25px;

            margin-bottom: 20px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

        }


        .detalle-cabecera h2 {

            margin: 0 0 7px;

        }


        .detalle-cabecera p {

            margin: 0;

            color: #777;

            font-size: 14px;

        }


        /* =====================================
           ESTADO
           ===================================== */

        .estado {

            display: inline-flex;

            align-items: center;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .estado-completada {

            background: #e8f7ee;

            color: #16834a;

        }


        .estado-pendiente {

            background: #fff4df;

            color: #c87500;

        }


        .estado-cancelada {

            background: #ffe9e9;

            color: #d52f2f;

        }


        /* =====================================
           INFORMACIÓN
           ===================================== */

        .info-pedido {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 15px;

            margin-bottom: 20px;

        }


        .info-item {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 12px;

            padding: 18px;

        }


        .info-item span {

            display: block;

            color: #777;

            font-size: 12px;

            margin-bottom: 6px;

        }


        .info-item strong {

            font-size: 15px;

        }


        /* =====================================
           PRODUCTOS
           ===================================== */

        .productos-pedido {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 15px;

            padding: 25px;

        }


        .productos-pedido h2 {

            margin-top: 0;

            margin-bottom: 20px;

        }


        .tabla-detalle {

            width: 100%;

            border-collapse: collapse;

        }


        .tabla-detalle th {

            padding: 13px;

            text-align: left;

            background: #f7f7f7;

            color: #555;

            font-size: 13px;

        }


        .tabla-detalle td {

            padding: 15px 13px;

            border-bottom: 1px solid #eee;

            font-size: 14px;

        }


        .tabla-detalle tr:last-child td {

            border-bottom: none;

        }


        /* =====================================
           TOTAL
           ===================================== */

        .total-pedido {

            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 25px;

            padding-top: 20px;

            margin-top: 10px;

            border-top: 1px solid #eee;

        }


        .total-pedido span {

            font-weight: bold;

        }


        .total-pedido strong {

            color: #f7941d;

            font-size: 25px;

        }


        /* =====================================
           BOTÓN
           ===================================== */

        .btn-volver {

            display: inline-block;

            margin-top: 20px;

            padding: 10px 16px;

            border-radius: 8px;

            background: #f7941d;

            color: white;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        .btn-volver:hover {

            background: #e98212;

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (max-width: 700px) {

            .info-pedido {

                grid-template-columns: 1fr;

            }


            .detalle-cabecera {

                flex-direction: column;

                align-items: flex-start;

            }


            .tabla-detalle {

                min-width: 650px;

            }

        }

    </style>

</head>


<body>


<div class="cliente-layout">


    <!-- =========================================
         SIDEBAR
         ========================================= -->

    <aside class="cliente-sidebar">


        <!-- LOGO -->

        <div class="cliente-marca">

            <img
                src="../img/logo_changarro_transparente.png"
                alt="Changarro Súper y Más"
            >

        </div>


        <!-- MENÚ -->

        <nav class="cliente-menu">


            <a href="dashboard_clientes.php">

                <span>⌂</span>

                Inicio

            </a>


            <a href="productos_clientes.php">

                <span>▣</span>

                Productos

            </a>


            <a href="carrito_clientes.php">

                <span>🛒</span>

                Mi carrito

            </a>


            <a
                href="pedidos_clientes.php"
                class="activo"
            >

                <span>▤</span>

                Mis pedidos

            </a>


            <a href="perfil_clientes.php">

                <span>♙</span>

                Mi cuenta

            </a>


        </nav>


        <!-- CERRAR SESIÓN -->

        <div class="cliente-salir">

            <a href="../logout.php">

                Cerrar sesión

            </a>

        </div>


    </aside>


    <!-- =========================================
         CONTENIDO
         ========================================= -->

    <main class="cliente-contenido">


        <!-- ENCABEZADO -->

        <header class="cliente-encabezado">


            <div>

                <h1>
                    Detalle del pedido
                </h1>

                <p>
                    Consulta la información de tu compra.
                </p>

            </div>


            <!-- INICIAL -->

            <div class="cliente-avatar">

                <?php

                echo htmlspecialchars(
                    $inicial_cliente
                );

                ?>

            </div>


        </header>


        <div class="detalle-pedido">


            <!-- =====================================
                 CABECERA DEL PEDIDO
                 ===================================== -->

            <section class="detalle-cabecera">


                <div>

                    <h2>

                        Pedido #

                        <?php

                        echo $pedido_id;

                        ?>

                    </h2>


                    <p>

                        Realizado el

                        <?php

                        echo htmlspecialchars(
                            $pedido["fecha"]
                        );

                        ?>

                    </p>

                </div>


                <?php

                $estado =
                    $pedido["estado"];


                if (
                    $estado === "Cancelada"
                ) {

                    $clase_estado =
                        "estado-cancelada";

                } elseif (
                    $estado === "Pendiente"
                ) {

                    $clase_estado =
                        "estado-pendiente";

                } else {

                    $clase_estado =
                        "estado-completada";

                }

                ?>


                <span
                    class="estado <?php

                        echo $clase_estado;

                    ?>"
                >

                    ●

                    <?php

                    echo htmlspecialchars(
                        $estado
                    );

                    ?>

                </span>


            </section>


            <!-- =====================================
                 INFORMACIÓN DEL PEDIDO
                 ===================================== -->

            <section class="info-pedido">


                <!-- FECHA -->

                <div class="info-item">

                    <span>
                        Fecha
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $pedido["fecha"]
                        );

                        ?>

                    </strong>

                </div>


                <!-- MÉTODO -->

                <div class="info-item">

                    <span>
                        Método de pago
                    </span>

                    <strong>

                        <?php

                        if (
                            !empty(
                                $pedido["tarjeta_ultimos4"]
                            )
                        ) {

                            echo htmlspecialchars(
                                $pedido["tarjeta_tipo"]
                            );

                            echo " •••• ";

                            echo htmlspecialchars(
                                $pedido["tarjeta_ultimos4"]
                            );

                        } else {

                            echo "Tarjeta";

                        }

                        ?>

                    </strong>

                </div>


                <!-- PRODUCTOS -->

                <div class="info-item">

                    <span>
                        Productos
                    </span>

                    <strong>

                        <?php

                        echo $resultado_detalles->num_rows;

                        ?>

                    </strong>

                </div>


            </section>


            <!-- =====================================
                 PRODUCTOS DEL PEDIDO
                 ===================================== -->

            <section class="productos-pedido">


                <h2>
                    Productos comprados
                </h2>


                <div
                    style="
                        overflow-x:auto;
                    "
                >


                    <table
                        class="tabla-detalle"
                    >


                        <thead>

                            <tr>

                                <th>
                                    Producto
                                </th>

                                <th>
                                    Código
                                </th>

                                <th>
                                    Cantidad
                                </th>

                                <th>
                                    Precio
                                </th>

                                <th>
                                    Subtotal
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $detalle =
                            $resultado_detalles->fetch_assoc()
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


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $detalle["codigo"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo intval(
                                        $detalle["cantidad"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    $

                                    <?php

                                    echo number_format(
                                        $detalle["precio"],
                                        2
                                    );

                                    ?>

                                </td>


                                <td>

                                    <strong>

                                        $

                                        <?php

                                        echo number_format(
                                            $detalle["subtotal"],
                                            2
                                        );

                                        ?>

                                    </strong>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


                <!-- =================================
                     TOTAL
                     ================================= -->

                <div class="total-pedido">


                    <span>
                        Total del pedido:
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


                <!-- VOLVER -->

                <a
                    href="pedidos_clientes.php"
                    class="btn-volver"
                >

                    ← Volver a mis pedidos

                </a>


            </section>


        </div>


    </main>


</div>


</body>

</html>