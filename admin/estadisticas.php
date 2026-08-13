<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";


/* =====================================================
   VENTAS DE HOY
   ===================================================== */

$sql_ventas_hoy = "
    SELECT
        COUNT(*) AS cantidad,
        COALESCE(SUM(total), 0) AS total
    FROM ventas
    WHERE DATE(fecha) = CURDATE()
    AND estado = 'Completada'
";

$resultado_ventas_hoy =
    $conexion->query($sql_ventas_hoy);

$ventas_hoy =
    $resultado_ventas_hoy->fetch_assoc();

$dinero_ventas_hoy =
    $ventas_hoy["total"];

$total_ventas_hoy =
    $ventas_hoy["cantidad"];


/* =====================================================
   VENTAS DEL MES
   ===================================================== */

$sql_ventas_mes = "
    SELECT
        COUNT(*) AS cantidad,
        COALESCE(SUM(total), 0) AS total
    FROM ventas
    WHERE MONTH(fecha) = MONTH(CURDATE())
    AND YEAR(fecha) = YEAR(CURDATE())
    AND estado = 'Completada'
";

$resultado_ventas_mes =
    $conexion->query($sql_ventas_mes);

$ventas_mes =
    $resultado_ventas_mes->fetch_assoc();

$dinero_ventas_mes =
    $ventas_mes["total"];

$total_ventas_mes =
    $ventas_mes["cantidad"];


/* =====================================================
   GANANCIA ESTIMADA DEL MES
   ===================================================== */

$sql_ganancia_mes = "
    SELECT
        COALESCE(
            SUM(
                detalle_venta.subtotal -
                (
                    detalle_venta.cantidad *
                    productos.precio_compra
                )
            ),
            0
        ) AS ganancia
    FROM detalle_venta

    INNER JOIN ventas
        ON detalle_venta.venta_id = ventas.id

    INNER JOIN productos
        ON detalle_venta.producto_id = productos.id

    WHERE MONTH(ventas.fecha) = MONTH(CURDATE())
    AND YEAR(ventas.fecha) = YEAR(CURDATE())
    AND ventas.estado = 'Completada'
";

$resultado_ganancia_mes =
    $conexion->query($sql_ganancia_mes);

$ganancia_mes =
    $resultado_ganancia_mes->fetch_assoc()["ganancia"];


/* =====================================================
   VENTAS DE LOS ÚLTIMOS 7 DÍAS
   ===================================================== */

$ventas_grafica = [];

for ($i = 6; $i >= 0; $i--) {

    $fecha = date(
        "Y-m-d",
        strtotime("-$i days")
    );

    $ventas_grafica[$fecha] = 0;
}


$sql_grafica = "
    SELECT
        DATE(fecha) AS dia,
        COALESCE(SUM(total), 0) AS total
    FROM ventas
    WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    AND estado = 'Completada'
    GROUP BY DATE(fecha)
    ORDER BY dia ASC
";

$resultado_grafica =
    $conexion->query($sql_grafica);


while (
    $venta_grafica =
    $resultado_grafica->fetch_assoc()
) {

    $dia =
        $venta_grafica["dia"];

    if (
        isset(
            $ventas_grafica[$dia]
        )
    ) {

        $ventas_grafica[$dia] =
            floatval(
                $venta_grafica["total"]
            );

    }

}


$max_venta =
    max($ventas_grafica);

if ($max_venta <= 0) {
    $max_venta = 1;
}


$total_semana =
    array_sum($ventas_grafica);


/* =====================================================
   PRODUCTOS MÁS VENDIDOS
   ===================================================== */

$sql_productos_vendidos = "
    SELECT
        productos.codigo,
        productos.nombre,
        SUM(detalle_venta.cantidad) AS cantidad_vendida

    FROM detalle_venta

    INNER JOIN productos
        ON detalle_venta.producto_id = productos.id

    INNER JOIN ventas
        ON detalle_venta.venta_id = ventas.id

    WHERE ventas.estado = 'Completada'

    GROUP BY
        productos.id,
        productos.codigo,
        productos.nombre

    ORDER BY cantidad_vendida DESC

    LIMIT 5
";

$resultado_productos_vendidos =
    $conexion->query(
        $sql_productos_vendidos
    );


/* =====================================================
   VENTAS POR USUARIO
   ===================================================== */

$sql_ventas_usuario = "
    SELECT
        usuarios.nombre,
        COUNT(ventas.id) AS cantidad_ventas,
        COALESCE(SUM(ventas.total), 0) AS total_ventas

    FROM ventas

    LEFT JOIN usuarios
        ON ventas.usuario_id = usuarios.id

    WHERE ventas.estado = 'Completada'

    GROUP BY
        usuarios.id,
        usuarios.nombre

    ORDER BY total_ventas DESC

    LIMIT 5
";

$resultado_ventas_usuario =
    $conexion->query(
        $sql_ventas_usuario
    );


/* =====================================================
   ÚLTIMAS VENTAS
   ===================================================== */

$sql_ultimas_ventas = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.fecha,
        usuarios.nombre AS usuario_nombre

    FROM ventas

    LEFT JOIN usuarios
        ON ventas.usuario_id = usuarios.id

    WHERE ventas.estado = 'Completada'

    ORDER BY ventas.id DESC

    LIMIT 5
";

$resultado_ultimas_ventas =
    $conexion->query(
        $sql_ultimas_ventas
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
        Estadísticas | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

    <style>

        /* =========================================
           TARJETAS DE ESTADÍSTICAS
           ========================================= */

        .estadisticas-tarjetas {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;

        }


        .estadistica-card {

            background: white;

            border-radius: 12px;

            padding: 22px;

            border: 1px solid #e7e7e7;

        }


        .estadistica-card span {

            color: #777;

            font-size: 14px;

        }


        .estadistica-card h2 {

            margin: 8px 0 4px;

            font-size: 27px;

        }


        .estadistica-card p {

            margin: 0;

            color: #777;

            font-size: 13px;

        }


        /* =========================================
           GRÁFICA
           ========================================= */

        .grafica-panel {

            background: white;

            border-radius: 12px;

            padding: 25px;

            border: 1px solid #e7e7e7;

            margin-bottom: 25px;

        }


        .grafica-panel h2 {

            margin: 0 0 5px;

        }


        .grafica-panel > p {

            margin-top: 0;

            color: #777;

            font-size: 14px;

        }


        .grafica {

            height: 280px;

            display: flex;

            align-items: flex-end;

            gap: 18px;

            padding:
                20px
                10px
                0;

            border-bottom:
                1px solid #ddd;

        }


        .grafica-dia {

            flex: 1;

            height: 100%;

            display: flex;

            flex-direction: column;

            justify-content: flex-end;

            align-items: center;

            gap: 8px;

        }


        .grafica-barra-contenedor {

            width: 100%;

            height: 220px;

            display: flex;

            align-items: flex-end;

            justify-content: center;

        }


        .grafica-barra {

            width: 55%;

            min-height: 4px;

            background: #222;

            border-radius:
                6px
                6px
                0
                0;

            position: relative;

            transition:
                height 0.3s ease;

        }


        .grafica-barra:hover {

            background: #444;

        }


        .grafica-valor {

            position: absolute;

            bottom: calc(100% + 7px);

            left: 50%;

            transform:
                translateX(-50%);

            font-size: 11px;

            font-weight: 600;

            white-space: nowrap;

        }


        .grafica-fecha {

            font-size: 12px;

            color: #777;

            margin-bottom: 10px;

        }


        .grafica-total {

            margin-top: 20px;

            text-align: right;

            color: #777;

        }


        .grafica-total strong {

            color: #222;

            font-size: 17px;

        }


        /* =========================================
           GRID
           ========================================= */

        .estadisticas-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 25px;

            margin-bottom: 25px;

        }


        .estadisticas-panel {

            background: white;

            border-radius: 12px;

            padding: 22px;

            border: 1px solid #e7e7e7;

        }


        .estadisticas-panel h2 {

            margin-top: 0;

            margin-bottom: 5px;

        }


        .estadisticas-panel p {

            margin-top: 0;

            color: #777;

            font-size: 14px;

            margin-bottom: 20px;

        }


        /* =========================================
           TABLAS
           ========================================= */

        .tabla-estadisticas {

            width: 100%;

            border-collapse: collapse;

        }


        .tabla-estadisticas th,
        .tabla-estadisticas td {

            padding: 12px;

            text-align: left;

            border-bottom:
                1px solid #eee;

        }


        .tabla-estadisticas th {

            font-size: 13px;

        }


        .tabla-estadisticas td {

            font-size: 14px;

        }


        .codigo-estadistica {

            font-weight: 600;

        }


        .sin-datos {

            text-align: center !important;

            color: #777;

            padding: 25px !important;

        }


        /* =========================================
           RESPONSIVE
           ========================================= */

        @media (max-width: 900px) {

            .estadisticas-tarjetas {

                grid-template-columns:
                    1fr;

            }


            .estadisticas-grid {

                grid-template-columns:
                    1fr;

            }

        }


        @media (max-width: 700px) {

            .grafica {

                gap: 7px;

            }


            .grafica-barra {

                width: 70%;

            }


            .grafica-fecha {

                font-size: 10px;

            }


            .grafica-valor {

                font-size: 9px;

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


        <!-- ENCABEZADO -->

        <header class="encabezado">


            <div>

                <h1>
                    Estadísticas
                </h1>

                <p>
                    Analiza el rendimiento de Changarro Súper y Más.
                </p>

            </div>


            <div class="perfil">


                <div class="avatar">

                    <?php

                    echo strtoupper(
                        substr(
                            $_SESSION["nombre"],
                            0,
                            1
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


        <!-- =========================================
             TARJETAS
             ========================================= -->

        <section class="estadisticas-tarjetas">


            <div class="estadistica-card">

                <span>
                    Ventas de hoy
                </span>

                <h2>

                    $

                    <?php

                    echo number_format(
                        $dinero_ventas_hoy,
                        2
                    );

                    ?>

                </h2>

                <p>

                    <?php

                    echo $total_ventas_hoy;

                    ?>

                    venta(s) realizadas

                </p>

            </div>


            <div class="estadistica-card">

                <span>
                    Ventas del mes
                </span>

                <h2>

                    $

                    <?php

                    echo number_format(
                        $dinero_ventas_mes,
                        2
                    );

                    ?>

                </h2>

                <p>

                    <?php

                    echo $total_ventas_mes;

                    ?>

                    venta(s) este mes

                </p>

            </div>


            <div class="estadistica-card">

                <span>
                    Ganancia estimada
                </span>

                <h2>

                    $

                    <?php

                    echo number_format(
                        $ganancia_mes,
                        2
                    );

                    ?>

                </h2>

                <p>
                    Ganancia estimada del mes
                </p>

            </div>


        </section>


        <!-- =========================================
             GRÁFICA
             ========================================= -->

        <section class="grafica-panel">


            <h2>
                Ventas de los últimos 7 días
            </h2>

            <p>
                Comportamiento de las ventas realizadas durante la última semana.
            </p>


            <div class="grafica">


                <?php foreach (
                    $ventas_grafica
                    as $fecha => $total
                ): ?>


                    <?php

                    $altura =
                        ($total / $max_venta) * 100;


                    $nombre_dia =
                        date(
                            "D",
                            strtotime($fecha)
                        );


                    $nombre_dias = [

                        "Mon" => "Lun",
                        "Tue" => "Mar",
                        "Wed" => "Mié",
                        "Thu" => "Jue",
                        "Fri" => "Vie",
                        "Sat" => "Sáb",
                        "Sun" => "Dom"

                    ];


                    $dia_mostrar =
                        $nombre_dias[
                            $nombre_dia
                        ];

                    ?>


                    <div class="grafica-dia">


                        <div
                            class="grafica-barra-contenedor"
                        >


                            <div
                                class="grafica-barra"
                                style="
                                    height:
                                    <?php
                                    echo $altura;
                                    ?>%;
                                "
                            >


                                <?php if (
                                    $total > 0
                                ): ?>

                                    <span
                                        class="grafica-valor"
                                    >

                                        $

                                        <?php

                                        echo number_format(
                                            $total,
                                            0
                                        );

                                        ?>

                                    </span>

                                <?php endif; ?>


                            </div>


                        </div>


                        <span
                            class="grafica-fecha"
                        >

                            <?php

                            echo $dia_mostrar;

                            ?>

                        </span>


                    </div>


                <?php endforeach; ?>


            </div>


            <div class="grafica-total">

                Total de los últimos 7 días:

                <strong>

                    $

                    <?php

                    echo number_format(
                        $total_semana,
                        2
                    );

                    ?>

                </strong>

            </div>


        </section>


        <!-- =========================================
             PRODUCTOS / USUARIOS
             ========================================= -->

        <section class="estadisticas-grid">


            <!-- PRODUCTOS MÁS VENDIDOS -->

            <div class="estadisticas-panel">


                <h2>
                    🏆 Productos más vendidos
                </h2>

                <p>
                    Los productos con mayor cantidad de unidades vendidas.
                </p>


                <table
                    class="tabla-estadisticas"
                >


                    <thead>

                        <tr>

                            <th>
                                Código
                            </th>

                            <th>
                                Producto
                            </th>

                            <th>
                                Unidades
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado_productos_vendidos->num_rows > 0
                    ): ?>


                        <?php while (
                            $producto =
                            $resultado_productos_vendidos->fetch_assoc()
                        ): ?>


                            <tr>

                                <td>

                                    <strong
                                        class="codigo-estadistica"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $producto["codigo"]
                                        );

                                        ?>

                                    </strong>

                                </td>


                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["nombre"]
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo $producto[
                                        "cantidad_vendida"
                                    ];

                                    ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="3"
                                class="sin-datos"
                            >

                                Todavía no hay productos vendidos.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


            <!-- VENTAS POR USUARIO -->

            <div class="estadisticas-panel">


                <h2>
                    👤 Ventas por usuario
                </h2>

                <p>
                    Usuarios con mayor monto de ventas realizadas.
                </p>


                <table
                    class="tabla-estadisticas"
                >


                    <thead>

                        <tr>

                            <th>
                                Usuario
                            </th>

                            <th>
                                Ventas
                            </th>

                            <th>
                                Total
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado_ventas_usuario->num_rows > 0
                    ): ?>


                        <?php while (
                            $usuario =
                            $resultado_ventas_usuario->fetch_assoc()
                        ): ?>


                            <tr>

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $usuario["nombre"]
                                        ?? "No disponible"
                                    );

                                    ?>

                                </td>


                                <td>

                                    <?php

                                    echo $usuario[
                                        "cantidad_ventas"
                                    ];

                                    ?>

                                </td>


                                <td>

                                    $

                                    <?php

                                    echo number_format(
                                        $usuario[
                                            "total_ventas"
                                        ],
                                        2
                                    );

                                    ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="3"
                                class="sin-datos"
                            >

                                Todavía no hay ventas registradas.

                            </td>

                        </tr>


                    <?php endif; ?>


                    </tbody>


                </table>


            </div>


        </section>


        <!-- =========================================
             ÚLTIMAS VENTAS
             ========================================= -->

        <section
            class="estadisticas-panel"
            style="margin-bottom:25px;"
        >


            <h2>
                🧾 Últimas ventas
            </h2>

            <p>
                Últimas operaciones completadas.
            </p>


            <table
                class="tabla-estadisticas"
            >


                <thead>

                    <tr>

                        <th>
                            Folio
                        </th>

                        <th>
                            Total
                        </th>

                        <th>
                            Realizada por
                        </th>

                        <th>
                            Fecha
                        </th>

                    </tr>

                </thead>


                <tbody>


                <?php if (
                    $resultado_ultimas_ventas->num_rows > 0
                ): ?>


                    <?php while (
                        $venta =
                        $resultado_ultimas_ventas->fetch_assoc()
                    ): ?>


                        <tr>


                            <td>

                                #<?php

                                echo $venta["id"];

                                ?>

                            </td>


                            <td>

                                $

                                <?php

                                echo number_format(
                                    $venta["total"],
                                    2
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $venta["usuario_nombre"]
                                    ?? "No disponible"
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo $venta["fecha"];

                                ?>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                <?php else: ?>


                    <tr>

                        <td
                            colspan="4"
                            class="sin-datos"
                        >

                            Todavía no hay ventas.

                        </td>

                    </tr>


                <?php endif; ?>


                </tbody>


            </table>


        </section>


    </main>


</div>


</body>

</html>