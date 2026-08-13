<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   OBTENER VENTAS / PEDIDOS ONLINE
   ========================================= */

$sql = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.fecha,
        ventas.metodo_pago,
        ventas.estado,
        usuarios.nombre AS cliente_nombre,
        usuarios.usuario AS cliente_usuario
    FROM ventas

    LEFT JOIN usuarios
        ON ventas.usuario_id = usuarios.id

    ORDER BY ventas.id DESC
";

$resultado = $conexion->query($sql);

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
        Pedidos | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >


    <style>

        /* =====================================
           ENCABEZADO
           ===================================== */

        .cabecera-productos {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

            margin-bottom: 20px;

        }


        .cabecera-productos h2 {

            margin-bottom: 5px;

        }


        .cabecera-productos p {

            margin: 0;

            color: #888;

            font-size: 13px;

        }


        /* =====================================
           INFORMACIÓN DEL CLIENTE
           ===================================== */

        .cliente-venta strong {

            display: block;

            font-size: 13px;

        }


        .cliente-venta span {

            display: block;

            margin-top: 3px;

            color: #888;

            font-size: 11px;

        }


        /* =====================================
           ESTADOS
           ===================================== */

        .estado-pendiente {

            color: #d88900;

            font-size: 12px;

            font-weight: 600;

        }


        .estado-pagada {

            color: #1d9b55;

            font-size: 12px;

            font-weight: 600;

        }


        .estado-cancelada {

            color: #d64545;

            font-size: 12px;

            font-weight: 600;

        }


        /* =====================================
           MÉTODO DE PAGO
           ===================================== */

        .metodo-tarjeta {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            font-size: 12px;

            color: #555;

        }


        /* =====================================
           BOTÓN VER
           ===================================== */

        .btn-ver-pedido {

            display: inline-block;

            padding: 7px 12px;

            background: #f7941d;

            color: white;

            text-decoration: none;

            border-radius: 7px;

            font-size: 12px;

            font-weight: 600;

            transition: 0.2s;

        }


        .btn-ver-pedido:hover {

            background: #e98212;

            transform: translateY(-1px);

        }


        /* =====================================
           MENSAJE SIN PEDIDOS
           ===================================== */

        .sin-datos {

            text-align: center;

            padding: 40px !important;

            color: #888;

        }


        /* =====================================
           PERFIL
           ===================================== */

        .perfil {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .perfil strong {

            display: block;

        }


        .perfil span {

            display: block;

            margin-top: 2px;

            color: #888;

            font-size: 12px;

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (max-width: 900px) {

            .cabecera-productos {

                align-items: flex-start;

            }

        }


        @media (max-width: 600px) {

            .cabecera-productos {

                flex-direction: column;

            }

        }

    </style>

</head>


<body>


<div class="contenedor">


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

                    Pedidos

                </h1>


                <p>

                    Consulta los pedidos realizados
                    desde la tienda en línea.

                </p>

            </div>


            <!-- PERFIL -->

            <div class="perfil">


                <div class="avatar">

                    <?php

                    echo htmlspecialchars(
                        strtoupper(
                            substr(
                                $_SESSION["nombre"],
                                0,
                                1
                            )
                        )
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
             PANEL
             ================================= -->

        <section class="panel-productos">


            <!-- CABECERA -->

            <div class="cabecera-productos">


                <div>

                    <h2>

                        Pedidos de la tienda

                    </h2>


                    <p>

                        Aquí aparecen las compras
                        realizadas por los clientes.

                    </p>

                </div>


            </div>


            <!-- =================================
                 TABLA
                 ================================= -->

            <div class="tabla-contenedor">


                <table>


                    <thead>

                        <tr>


                            <th>

                                Pedido

                            </th>


                            <th>

                                Cliente

                            </th>


                            <th>

                                Total

                            </th>


                            <th>

                                Pago

                            </th>


                            <th>

                                Fecha

                            </th>


                            <th>

                                Estado

                            </th>


                            <th>

                                Acción

                            </th>


                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado &&
                        $resultado->num_rows > 0
                    ): ?>


                        <?php while (
                            $pedido =
                            $resultado->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- PEDIDO -->

                                <td>

                                    <strong>

                                        #

                                        <?php

                                        echo intval(
                                            $pedido["id"]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- CLIENTE -->

                                <td>

                                    <div
                                        class="cliente-venta"
                                    >


                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $pedido[
                                                    "cliente_nombre"
                                                ]
                                                ??
                                                "Cliente"
                                            );

                                            ?>

                                        </strong>


                                        <span>

                                            @

                                            <?php

                                            echo htmlspecialchars(
                                                $pedido[
                                                    "cliente_usuario"
                                                ]
                                                ??
                                                ""
                                            );

                                            ?>

                                        </span>


                                    </div>

                                </td>


                                <!-- TOTAL -->

                                <td>

                                    <strong>

                                        $

                                        <?php

                                        echo number_format(
                                            $pedido["total"],
                                            2
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- PAGO -->

                                <td>

                                    <span
                                        class="metodo-tarjeta"
                                    >

                                        💳

                                        Tarjeta

                                    </span>

                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo date(
                                        "d/m/Y H:i",
                                        strtotime(
                                            $pedido["fecha"]
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- ESTADO -->

                                <td>


                                    <?php

                                    $estado =
                                        $pedido["estado"];

                                    ?>


                                    <?php if (
                                        $estado ===
                                        "Cancelada"
                                    ): ?>


                                        <span
                                            class="estado-cancelada"
                                        >

                                            ● Cancelada

                                        </span>


                                    <?php elseif (
                                        $estado ===
                                        "Pendiente"
                                    ): ?>


                                        <span
                                            class="estado-pendiente"
                                        >

                                            ● Pendiente

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="estado-pagada"
                                        >

                                            ● Pagada

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- ACCIÓN -->

                                <td>


                                    <a
                                        href="detalle_venta.php?id=<?php echo intval($pedido["id"]); ?>"
                                        class="btn-ver-pedido"
                                    >

                                        Ver pedido

                                    </a>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="sin-datos"
                            >

                                Todavía no hay pedidos
                                realizados desde la tienda.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


    </main>


</div>


</body>

</html>