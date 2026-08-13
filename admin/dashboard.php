<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   DATOS DEL USUARIO
   ========================================= */

$nombre_usuario = $_SESSION["nombre"];
$rol_usuario = $_SESSION["rol"];


/* =========================================
   TOTAL DE VENTAS
   ========================================= */

$sql_ventas = "
    SELECT COUNT(*) AS total
    FROM ventas
    WHERE estado != 'Cancelada'
";

$resultado_ventas =
    $conexion->query($sql_ventas);

$total_ventas = 0;

if ($resultado_ventas) {

    $fila =
        $resultado_ventas->fetch_assoc();

    $total_ventas =
        intval($fila["total"]);

}


/* =========================================
   INGRESOS
   ========================================= */

$sql_ingresos = "
    SELECT COALESCE(SUM(total), 0) AS total
    FROM ventas
    WHERE estado != 'Cancelada'
";

$resultado_ingresos =
    $conexion->query($sql_ingresos);

$total_ingresos = 0;

if ($resultado_ingresos) {

    $fila =
        $resultado_ingresos->fetch_assoc();

    $total_ingresos =
        floatval($fila["total"]);

}


/* =========================================
   PRODUCTOS ACTIVOS
   ========================================= */

$sql_productos = "
    SELECT COUNT(*) AS total
    FROM productos
    WHERE activo = 1
";

$resultado_productos =
    $conexion->query($sql_productos);

$total_productos = 0;

if ($resultado_productos) {

    $fila =
        $resultado_productos->fetch_assoc();

    $total_productos =
        intval($fila["total"]);

}


/* =========================================
   PRODUCTOS CON POCO STOCK
   ========================================= */

$sql_stock = "
    SELECT COUNT(*) AS total
    FROM productos
    WHERE activo = 1
    AND stock <= 5
";

$resultado_stock =
    $conexion->query($sql_stock);

$productos_stock_bajo = 0;

if ($resultado_stock) {

    $fila =
        $resultado_stock->fetch_assoc();

    $productos_stock_bajo =
        intval($fila["total"]);

}


/* =========================================
   CLIENTES REGISTRADOS
   ========================================= */

$sql_clientes = "
    SELECT COUNT(*) AS total
    FROM usuarios
    WHERE rol = 'Cliente'
    AND estado = 'Activo'
";

$resultado_clientes =
    $conexion->query($sql_clientes);

$total_clientes = 0;

if ($resultado_clientes) {

    $fila =
        $resultado_clientes->fetch_assoc();

    $total_clientes =
        intval($fila["total"]);

}


/* =========================================
   PEDIDOS RECIENTES
   ========================================= */

$sql_recientes = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.fecha,
        ventas.estado,
        usuarios.nombre AS cliente
    FROM ventas
    LEFT JOIN usuarios
        ON ventas.usuario_id = usuarios.id
    ORDER BY ventas.id DESC
    LIMIT 5
";

$resultado_recientes =
    $conexion->query($sql_recientes);


/* =========================================
   INICIAL DEL USUARIO
   ========================================= */

$inicial =
    strtoupper(
        substr(
            $nombre_usuario,
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
        Inicio | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <style>

        /* =========================================
           ESTADOS DE PEDIDOS
           ========================================= */

        .estado-pendiente {

            color: #d88900;

            font-weight: 600;

        }


        .estado-preparacion {

            color: #2475b8;

            font-weight: 600;

        }


        .estado-camino {

            color: #7048b8;

            font-weight: 600;

        }


        .estado-completada {

            color: #159447;

            font-weight: 600;

        }


        .estado-inactivo {

            color: #d64545;

            font-weight: 600;

        }


        /* =========================================
           TABLA PEDIDOS
           ========================================= */

        .tabla-dashboard {

            width: 100%;

            border-collapse: collapse;

        }


        .tabla-dashboard th {

            text-align: left;

            padding: 14px 15px;

            color: #555;

            font-size: 13px;

            font-weight: 600;

            border-bottom: 1px solid #e8e8e8;

        }


        .tabla-dashboard td {

            padding: 15px;

            font-size: 14px;

            color: #444;

            border-bottom: 1px solid #eeeeee;

        }


        .tabla-dashboard tbody tr:hover {

            background: #fff8ef;

        }


        /* =========================================
           PANEL PEDIDOS
           ========================================= */

        .dashboard-pedidos {

            background: white;

            border: 1px solid #e8e8e8;

            border-radius: 15px;

            padding: 28px;

            margin-top: 25px;

        }


        .dashboard-pedidos-header {

            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 20px;

        }


        .dashboard-pedidos-header h2 {

            font-size: 19px;

            margin: 0 0 6px;

        }


        .dashboard-pedidos-header p {

            color: #888;

            font-size: 14px;

            margin: 0;

        }


        .ver-pedidos {

            color: #F7941D;

            text-decoration: none;

            font-size: 13px;

            font-weight: 600;

        }


        .ver-pedidos:hover {

            color: #E98212;

        }


        /* =========================================
           ALERTA STOCK
           ========================================= */

        .stock-alerta {

            margin-top: 25px;

            padding: 18px 22px;

            background: #fff8e8;

            border: 1px solid #f5d58b;

            border-left: 4px solid #F7941D;

            border-radius: 12px;

        }


        .stock-alerta h3 {

            margin-bottom: 6px;

            color: #9a6200;

            font-size: 16px;

        }


        .stock-alerta p {

            color: #6f7378;

            font-size: 14px;

        }


        .stock-alerta a {

            display: inline-block;

            margin-top: 12px;

            padding: 9px 14px;

            background: #F7941D;

            color: white;

            border-radius: 8px;

            text-decoration: none;

            font-size: 12px;

            font-weight: 600;

        }


        .stock-alerta a:hover {

            background: #E98212;

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

                    Inicio

                </h1>


                <p>

                    Resumen general de
                    Changarro Súper y Más.

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
                            $nombre_usuario
                        );

                        ?>

                    </strong>


                    <span>

                        <?php

                        echo htmlspecialchars(
                            $rol_usuario
                        );

                        ?>

                    </span>

                </div>


            </div>


        </header>


        <!-- =========================================
             ESTADÍSTICAS
             AQUÍ VOLVEMOS A LAS CLASES ORIGINALES
             ========================================= -->

        <section class="tarjetas">


            <!-- =====================================
                 VENTAS
                 ===================================== -->

            <div class="tarjeta">


                <div>

                    <span>

                        Ventas realizadas

                    </span>


                    <h2>

                        <?php

                        echo number_format(
                            $total_ventas
                        );

                        ?>

                    </h2>


                    <p>

                        Pedidos registrados

                    </p>

                </div>


                <div class="icono">

                    $

                </div>


            </div>


            <!-- =====================================
                 INGRESOS
                 ===================================== -->

            <div class="tarjeta">


                <div>

                    <span>

                        Ingresos

                    </span>


                    <h2>

                        $

                        <?php

                        echo number_format(
                            $total_ingresos,
                            2
                        );

                        ?>

                    </h2>


                    <p>

                        Ventas no canceladas

                    </p>

                </div>


                <div class="icono">

                    $

                </div>


            </div>


            <!-- =====================================
                 PRODUCTOS
                 ===================================== -->

            <div class="tarjeta">


                <div>

                    <span>

                        Productos activos

                    </span>


                    <h2>

                        <?php

                        echo number_format(
                            $total_productos
                        );

                        ?>

                    </h2>


                    <p>

                        Productos disponibles

                    </p>

                </div>


                <div class="icono">

                    ▣

                </div>


            </div>


            <!-- =====================================
                 CLIENTES
                 ===================================== -->

            <div class="tarjeta">


                <div>

                    <span>

                        Clientes

                    </span>


                    <h2>

                        <?php

                        echo number_format(
                            $total_clientes
                        );

                        ?>

                    </h2>


                    <p>

                        Cuentas activas

                    </p>

                </div>


                <div class="icono">

                    ♟

                </div>


            </div>


        </section>


        <!-- =========================================
             PEDIDOS RECIENTES
             ========================================= -->

        <section class="dashboard-pedidos">


            <div class="dashboard-pedidos-header">


                <div>

                    <h2>

                        Pedidos recientes

                    </h2>


                    <p>

                        Últimos pedidos realizados
                        desde la tienda en línea.

                    </p>

                </div>


                <a
                    href="pedidos.php"
                    class="ver-pedidos"
                >

                    Ver todos →

                </a>


            </div>


            <div
                style="
                    overflow-x:auto;
                "
            >


                <table
                    class="tabla-dashboard"
                >


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
                                Estado
                            </th>

                            <th>
                                Fecha
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado_recientes &&
                        $resultado_recientes->num_rows > 0
                    ): ?>


                        <?php while (
                            $venta =
                            $resultado_recientes
                            ->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- PEDIDO -->

                                <td>

                                    #

                                    <?php

                                    echo intval(
                                        $venta["id"]
                                    );

                                    ?>

                                </td>


                                <!-- CLIENTE -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $venta["cliente"]
                                        ?? "Cliente"
                                    );

                                    ?>

                                </td>


                                <!-- TOTAL -->

                                <td>

                                    $

                                    <?php

                                    echo number_format(
                                        $venta["total"],
                                        2
                                    );

                                    ?>

                                </td>


                                <!-- ESTADO -->

                                <td>


                                    <?php

                                    $estado =
                                        $venta["estado"];

                                    ?>


                                    <?php if (
                                        $estado ===
                                        "Pendiente"
                                    ): ?>


                                        <span
                                            class="estado-pendiente"
                                        >

                                            ● Pendiente

                                        </span>


                                    <?php elseif (
                                        $estado ===
                                        "En preparación"
                                    ): ?>


                                        <span
                                            class="estado-preparacion"
                                        >

                                            ● En preparación

                                        </span>


                                    <?php elseif (
                                        $estado ===
                                        "En camino"
                                    ): ?>


                                        <span
                                            class="estado-camino"
                                        >

                                            ● En camino

                                        </span>


                                    <?php elseif (
                                        $estado ===
                                        "Completada"
                                    ): ?>


                                        <span
                                            class="estado-completada"
                                        >

                                            ● Completada

                                        </span>


                                    <?php elseif (
                                        $estado ===
                                        "Cancelada"
                                    ): ?>


                                        <span
                                            class="estado-inactivo"
                                        >

                                            ● Cancelada

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="estado-activo"
                                        >

                                            ●

                                            <?php

                                            echo htmlspecialchars(
                                                $estado
                                            );

                                            ?>

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $venta["fecha"]
                                        )
                                    );

                                    ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="5"
                                style="
                                    text-align:center;
                                    color:#888;
                                    padding:30px;
                                "
                            >

                                Todavía no hay pedidos.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


        <!-- =========================================
             ALERTA DE STOCK
             ========================================= -->

        <?php if (
            $productos_stock_bajo > 0
        ): ?>


            <div class="stock-alerta">


                <h3>

                    ⚠️ Stock bajo

                </h3>


                <p>

                    Hay

                    <strong>

                        <?php

                        echo $productos_stock_bajo;

                        ?>

                    </strong>

                    producto(s) con
                    5 unidades o menos.

                </p>


                <a
                    href="productos.php"
                >

                    Revisar productos

                </a>


            </div>


        <?php endif; ?>


    </main>


</div>


</body>

</html>