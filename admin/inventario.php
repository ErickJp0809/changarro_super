<?php

require_once "verificar_acceso.php";
require_once "../config/conexion.php";


/* =========================================
   PRODUCTOS ACTIVOS
   ========================================= */

$sql_productos = "
    SELECT id, nombre, stock
    FROM productos
    WHERE activo = 1
    ORDER BY nombre ASC
";

$resultado_productos =
    $conexion->query($sql_productos);


/* =========================================
   CALCULAR RESUMEN
   ========================================= */

$total_productos = 0;
$total_stock = 0;

if ($resultado_productos) {

    while (
        $producto =
        $resultado_productos->fetch_assoc()
    ) {

        $total_productos++;

        $total_stock +=
            intval($producto["stock"]);

    }

}


/* =========================================
   MOVIMIENTOS DE INVENTARIO
   ========================================= */

$sql_movimientos = "
    SELECT
        movimientos_inventario.id,
        productos.nombre,
        movimientos_inventario.tipo,
        movimientos_inventario.cantidad,
        movimientos_inventario.motivo,
        movimientos_inventario.fecha
    FROM movimientos_inventario

    INNER JOIN productos
        ON movimientos_inventario.producto_id =
           productos.id

    ORDER BY
        movimientos_inventario.fecha DESC,
        movimientos_inventario.id DESC
";

$resultado_movimientos =
    $conexion->query($sql_movimientos);

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
        Inventario | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <style>

        /* =====================================
           RESUMEN
           ===================================== */

        .inventario-resumen {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .inventario-card {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 25px;

            box-shadow:
                0 3px 10px
                rgba(0, 0, 0, 0.03);

        }


        .inventario-card .titulo {

            display: block;

            color: #777;

            font-size: 13px;

            margin-bottom: 8px;

        }


        .inventario-card .numero {

            display: block;

            font-size: 30px;

            color: #222;

        }


        .inventario-card .descripcion {

            display: block;

            color: #999;

            font-size: 12px;

            margin-top: 5px;

        }


        /* =====================================
           PANEL
           ===================================== */

        .inventario-panel {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 14px;

            padding: 25px;

        }


        .inventario-panel-header {

            margin-bottom: 20px;

        }


        .inventario-panel-header h2 {

            margin: 0 0 5px 0;

            font-size: 20px;

        }


        .inventario-panel-header p {

            margin: 0;

            color: #888;

            font-size: 13px;

        }


        /* =====================================
           TABLA
           ===================================== */

        .tabla-contenedor {

            overflow-x: auto;

        }


        .inventario-tabla {

            width: 100%;

            border-collapse: collapse;

        }


        .inventario-tabla th {

            padding: 13px 12px;

            text-align: left;

            background: #f6f6f6;

            color: #555;

            font-size: 12px;

            border-bottom: 1px solid #e5e5e5;

        }


        .inventario-tabla td {

            padding: 14px 12px;

            font-size: 13px;

            border-bottom: 1px solid #eeeeee;

        }


        .inventario-tabla tr:last-child td {

            border-bottom: none;

        }


        /* =====================================
           MOVIMIENTOS
           ===================================== */

        .movimiento-entrada {

            color: #16834a;

            font-weight: 700;

        }


        .movimiento-salida {

            color: #d64545;

            font-weight: 700;

        }


        .tipo-movimiento {

            display: inline-block;

            padding: 5px 9px;

            border-radius: 15px;

            font-size: 11px;

            font-weight: 700;

        }


        .tipo-entrada {

            background: #e8f8ef;

            color: #16834a;

        }


        .tipo-salida {

            background: #ffe8e8;

            color: #c62828;

        }


        .fecha-movimiento {

            color: #777;

            white-space: nowrap;

        }


        .sin-datos {

            text-align: center;

            padding: 40px !important;

            color: #888;

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (max-width: 700px) {

            .inventario-resumen {

                grid-template-columns: 1fr;

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
                    Inventario
                </h1>

                <p>
                    Consulta las entradas y salidas
                    de productos.
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
             RESUMEN
             ================================= -->

        <section class="inventario-resumen">


            <!-- PRODUCTOS ACTIVOS -->

            <div class="inventario-card">


                <span class="titulo">

                    Productos activos

                </span>


                <strong class="numero">

                    <?php

                    echo $total_productos;

                    ?>

                </strong>


                <span class="descripcion">

                    Productos disponibles

                </span>


            </div>


            <!-- STOCK TOTAL -->

            <div class="inventario-card">


                <span class="titulo">

                    Stock total

                </span>


                <strong class="numero">

                    <?php

                    echo $total_stock;

                    ?>

                </strong>


                <span class="descripcion">

                    Artículos disponibles

                </span>


            </div>


        </section>


        <!-- =================================
             HISTORIAL DE MOVIMIENTOS
             ================================= -->

        <section class="inventario-panel">


            <div class="inventario-panel-header">


                <h2>

                    Movimientos de inventario

                </h2>


                <p>

                    Historial automático de entradas
                    y salidas de productos.

                </p>


            </div>


            <!-- =================================
                 TABLA
                 ================================= -->

            <div class="tabla-contenedor">


                <table
                    class="inventario-tabla"
                >


                    <thead>

                        <tr>

                            <th>
                                Producto
                            </th>

                            <th>
                                Tipo
                            </th>

                            <th>
                                Cantidad
                            </th>

                            <th>
                                Motivo
                            </th>

                            <th>
                                Fecha
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado_movimientos &&
                        $resultado_movimientos->num_rows > 0
                    ): ?>


                        <?php while (
                            $movimiento =
                            $resultado_movimientos
                            ->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- PRODUCTO -->

                                <td>

                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $movimiento[
                                                "nombre"
                                            ]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <!-- TIPO -->

                                <td>


                                    <?php if (
                                        $movimiento[
                                            "tipo"
                                        ] === "entrada"
                                    ): ?>


                                        <span
                                            class="
                                                tipo-movimiento
                                                tipo-entrada
                                            "
                                        >

                                            Entrada

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="
                                                tipo-movimiento
                                                tipo-salida
                                            "
                                        >

                                            Salida

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- CANTIDAD -->

                                <td>


                                    <?php if (
                                        $movimiento[
                                            "tipo"
                                        ] === "entrada"
                                    ): ?>


                                        <span
                                            class="
                                                movimiento-entrada
                                            "
                                        >

                                            +

                                            <?php

                                            echo intval(
                                                $movimiento[
                                                    "cantidad"
                                                ]
                                            );

                                            ?>

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="
                                                movimiento-salida
                                            "
                                        >

                                            -

                                            <?php

                                            echo intval(
                                                $movimiento[
                                                    "cantidad"
                                                ]
                                            );

                                            ?>

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- MOTIVO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $movimiento[
                                            "motivo"
                                        ]
                                        ??
                                        "Movimiento de inventario"
                                    );

                                    ?>

                                </td>


                                <!-- FECHA -->

                                <td>

                                    <span
                                        class="
                                            fecha-movimiento
                                        "
                                    >

                                        <?php

                                        echo date(
                                            "d/m/Y H:i",
                                            strtotime(
                                                $movimiento[
                                                    "fecha"
                                                ]
                                            )
                                        );

                                        ?>

                                    </span>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="5"
                                class="sin-datos"
                            >

                                No hay movimientos
                                de inventario registrados.

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