<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";


/* =========================================
   VERIFICAR ADMINISTRADOR
   ========================================= */

if (
    !isset($_SESSION["rol"]) ||
    $_SESSION["rol"] !== "Administrador"
) {
    header("Location: ../index.php");
    exit();
}


/* =========================================
   OBTENER ID DEL PEDIDO
   ========================================= */

$pedido_id = intval($_GET["id"] ?? 0);

if ($pedido_id <= 0) {
    header("Location: pedidos.php");
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
        usuarios.nombre AS cliente,
        tarjetas.tipo AS tarjeta_tipo,
        tarjetas.ultimos4 AS tarjeta_ultimos4
    FROM ventas

    INNER JOIN usuarios
        ON ventas.usuario_id = usuarios.id

    LEFT JOIN tarjetas
        ON ventas.tarjeta_id = tarjetas.id

    WHERE ventas.id = ?
";


$stmt_pedido = $conexion->prepare($sql_pedido);

if (!$stmt_pedido) {
    die("Error en la consulta del pedido: " . $conexion->error);
}

$stmt_pedido->bind_param(
    "i",
    $pedido_id
);

$stmt_pedido->execute();

$resultado_pedido = $stmt_pedido->get_result();


/* =========================================
   COMPROBAR PEDIDO
   ========================================= */

if ($resultado_pedido->num_rows !== 1) {
    header("Location: pedidos.php");
    exit();
}

$pedido = $resultado_pedido->fetch_assoc();


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


$stmt_detalles = $conexion->prepare($sql_detalles);

if (!$stmt_detalles) {
    die("Error en la consulta de productos: " . $conexion->error);
}

$stmt_detalles->bind_param(
    "i",
    $pedido_id
);

$stmt_detalles->execute();

$resultado_detalles = $stmt_detalles->get_result();


/* =========================================
   INICIAL DEL ADMINISTRADOR
   ========================================= */

$inicial_admin = strtoupper(
    substr(
        $_SESSION["nombre"],
        0,
        1
    )
);


/* =========================================
   CLASE DEL ESTADO
   ========================================= */

switch ($pedido["estado"]) {

    case "Pendiente":

        $clase_estado = "estado-pendiente";

        break;

    case "En preparación":

        $clase_estado = "estado-preparacion";

        break;

    case "En camino":

        $clase_estado = "estado-camino";

        break;

    case "Completada":

        $clase_estado = "estado-completada";

        break;

    case "Cancelada":

        $clase_estado = "estado-cancelada";

        break;

    default:

        $clase_estado = "estado-pendiente";

        break;
}

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
        href="../css/dashboard.css"
    >


    <style>

        .detalle-pedido-admin {

            max-width: 1100px;

        }


        /* =========================================
           CABECERA
           ========================================= */

        .cabecera-pedido-admin {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 15px;

            padding: 25px;

            margin-bottom: 20px;

        }


        .cabecera-pedido-admin h2 {

            margin: 0 0 7px;

        }


        .cabecera-pedido-admin p {

            margin: 0;

            color: #777;

            font-size: 14px;

        }


        /* =========================================
           ESTADOS
           ========================================= */

        .estado {

            display: inline-flex;

            align-items: center;

            gap: 5px;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

        }


        .estado-pendiente {

            background: #fff4df;

            color: #c87500;

        }


        .estado-preparacion {

            background: #eaf4ff;

            color: #2475b8;

        }


        .estado-camino {

            background: #f0eaff;

            color: #7048b8;

        }


        .estado-completada {

            background: #e8f7ee;

            color: #16834a;

        }


        .estado-cancelada {

            background: #ffe9e9;

            color: #d52f2f;

        }


        /* =========================================
           INFORMACIÓN
           ========================================= */

        .informacion-pedido {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 15px;

            margin-bottom: 20px;

        }


        .informacion-item {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 12px;

            padding: 18px;

        }


        .informacion-item span {

            display: block;

            color: #777;

            font-size: 12px;

            margin-bottom: 6px;

        }


        .informacion-item strong {

            font-size: 15px;

        }


        /* =========================================
           PRODUCTOS
           ========================================= */

        .productos-pedido-admin {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 15px;

            padding: 25px;

        }


        .productos-pedido-admin h2 {

            margin-top: 0;

            margin-bottom: 20px;

        }


        .tabla-detalle-pedido {

            width: 100%;

            border-collapse: collapse;

        }


        .tabla-detalle-pedido th {

            padding: 13px;

            text-align: left;

            background: #f7f7f7;

            color: #555;

            font-size: 13px;

        }


        .tabla-detalle-pedido td {

            padding: 15px 13px;

            border-bottom: 1px solid #eee;

            font-size: 14px;

        }


        .tabla-detalle-pedido tr:last-child td {

            border-bottom: none;

        }


        /* =========================================
           TOTAL
           ========================================= */

        .total-pedido-admin {

            display: flex;

            justify-content: flex-end;

            align-items: center;

            gap: 25px;

            padding-top: 20px;

            margin-top: 10px;

            border-top: 1px solid #eee;

        }


        .total-pedido-admin strong {

            color: #f7941d;

            font-size: 25px;

        }


        /* =========================================
           VOLVER
           ========================================= */

        .btn-volver-pedidos {

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


        .btn-volver-pedidos:hover {

            background: #e98212;

        }


        /* =========================================
           RESPONSIVE
           ========================================= */

        @media (max-width: 700px) {

            .informacion-pedido {

                grid-template-columns: 1fr;

            }


            .cabecera-pedido-admin {

                flex-direction: column;

                align-items: flex-start;

            }

        }

    </style>

</head>


<body>


<div class="contenedor">


    <!-- =========================================
         SIDEBAR
         ========================================= -->

    <?php include "../includes/sidebar.php"; ?>


    <!-- =========================================
         CONTENIDO
         ========================================= -->

    <main class="contenido">


        <!-- =========================================
             ENCABEZADO
             ========================================= -->

        <header class="encabezado">


            <div>

                <h1>
                    Detalle del pedido
                </h1>

                <p>
                    Consulta la información del pedido.
                </p>

            </div>


            <!-- PERFIL -->

            <div class="perfil">


                <div class="avatar">

                    <?php

                    echo htmlspecialchars(
                        $inicial_admin
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


        <div class="detalle-pedido-admin">


            <!-- =====================================
                 CABECERA DEL PEDIDO
                 ===================================== -->

            <section class="cabecera-pedido-admin">


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


                <span
                    class="estado <?php

                        echo $clase_estado;

                    ?>"
                >

                    ●

                    <?php

                    echo htmlspecialchars(
                        $pedido["estado"]
                    );

                    ?>

                </span>


            </section>


            <!-- =====================================
                 INFORMACIÓN
                 ===================================== -->

            <section class="informacion-pedido">


                <!-- CLIENTE -->

                <div class="informacion-item">

                    <span>
                        Cliente
                    </span>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $pedido["cliente"]
                        );

                        ?>

                    </strong>

                </div>


                <!-- PAGO -->

                <div class="informacion-item">

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

                            echo htmlspecialchars(
                                $pedido["metodo_pago"]
                            );

                        }

                        ?>

                    </strong>

                </div>


            </section>


            <!-- =====================================
                 PRODUCTOS
                 ===================================== -->

            <section class="productos-pedido-admin">


                <h2>
                    Productos del pedido
                </h2>


                <div
                    style="
                        overflow-x:auto;
                    "
                >


                    <table
                        class="tabla-detalle-pedido"
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


                        <?php if (
                            $resultado_detalles &&
                            $resultado_detalles->num_rows > 0
                        ): ?>


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


                        <?php else: ?>


                            <tr>

                                <td colspan="5">

                                    No hay productos registrados
                                    en este pedido.

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>


                </div>


                <!-- =================================
                     TOTAL
                     ================================= -->

                <div class="total-pedido-admin">


                    <span>
                        Total:
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
                    href="pedidos.php"
                    class="btn-volver-pedidos"
                >

                    ← Volver a pedidos

                </a>


            </section>


        </div>


    </main>


</div>


</body>

</html>