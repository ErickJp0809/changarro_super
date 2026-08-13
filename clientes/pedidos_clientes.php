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


$usuario_id =
    intval($_SESSION["id"]);


/* =========================================
   OBTENER PEDIDOS DEL CLIENTE
   ========================================= */

$sql = "

    SELECT

        id,
        total,
        fecha,
        estado

    FROM ventas

    WHERE usuario_id = ?

    ORDER BY id DESC

";


$stmt =
    $conexion->prepare($sql);


$stmt->bind_param(
    "i",
    $usuario_id
);


$stmt->execute();


$resultado =
    $stmt->get_result();


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
        Mis pedidos | Changarro Súper y Más
    </title>


    <link
        rel="stylesheet"
        href="../css/clientes.css"
    >


    <style>

        /* =====================================
           CONTENEDOR
           ===================================== */

        .pedidos-contenedor {

            background: white;

            border: 1px solid #e5e5e5;

            border-radius: 15px;

            padding: 25px;

        }


        .pedidos-contenedor h2 {

            margin: 0 0 6px;

        }


        .pedidos-descripcion {

            margin: 0 0 25px;

            color: #777;

            font-size: 14px;

        }


        /* =====================================
           TABLA
           ===================================== */

        .tabla-pedidos {

            width: 100%;

            border-collapse: collapse;

        }


        .tabla-pedidos th {

            padding: 14px;

            text-align: left;

            background: #f7f7f7;

            color: #555;

            font-size: 13px;

        }


        .tabla-pedidos td {

            padding: 16px 14px;

            border-bottom: 1px solid #eeeeee;

            font-size: 14px;

        }


        .tabla-pedidos tr:last-child td {

            border-bottom: none;

        }


        /* =====================================
           ESTADOS
           ===================================== */

        .estado {

            display: inline-flex;

            align-items: center;

            padding: 6px 10px;

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


        .estado-preparacion {

            background: #eaf4ff;

            color: #2475b8;

        }


        .estado-camino {

            background: #f0eaff;

            color: #7048b8;

        }


        .estado-cancelada {

            background: #ffe9e9;

            color: #d52f2f;

        }


        /* =====================================
           BOTÓN VER
           ===================================== */

        .btn-ver-pedido {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 7px;

            background: #f7941d;

            color: white;

            text-decoration: none;

            font-size: 12px;

            font-weight: bold;

        }


        .btn-ver-pedido:hover {

            background: #e98212;

        }


        /* =====================================
           BOTÓN CANCELAR
           ===================================== */

        .btn-cancelar-pedido {

            display: inline-block;

            padding: 7px 12px;

            margin-left: 6px;

            border-radius: 7px;

            background: #d52f2f;

            color: white;

            border: none;

            font-size: 12px;

            font-weight: bold;

            cursor: pointer;

        }


        .btn-cancelar-pedido:hover {

            background: #b92323;

        }


        /* =====================================
           MENSAJE DE ÉXITO
           ===================================== */

        .mensaje-exito {

            margin-bottom: 20px;

            padding: 13px 16px;

            border-radius: 8px;

            background: #e8f7ee;

            color: #16834a;

            border: 1px solid #bce8ce;

            font-size: 14px;

            font-weight: 600;

        }


        /* =====================================
           MENSAJE DE ERROR
           ===================================== */

        .mensaje-error {

            margin-bottom: 20px;

            padding: 13px 16px;

            border-radius: 8px;

            background: #ffe9e9;

            color: #d52f2f;

            border: 1px solid #f2bcbc;

            font-size: 14px;

            font-weight: 600;

        }


        /* =====================================
           SIN PEDIDOS
           ===================================== */

        .sin-pedidos {

            padding: 50px 20px;

            text-align: center;

            color: #777;

        }


        .sin-pedidos-icono {

            font-size: 45px;

            margin-bottom: 12px;

        }


        .sin-pedidos h3 {

            margin: 0 0 8px;

            color: #222;

        }


        .sin-pedidos p {

            margin: 0;

            font-size: 14px;

        }


        /* =====================================
           RESPONSIVE
           ===================================== */

        @media (max-width: 700px) {

            .tabla-pedidos {

                display: block;

                overflow-x: auto;

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


            <!-- INICIO -->

            <a href="dashboard_clientes.php">

                <span>⌂</span>

                Inicio

            </a>


            <!-- PRODUCTOS -->

            <a href="productos_clientes.php">

                <span>▣</span>

                Productos

            </a>


            <!-- CARRITO -->

            <a href="carrito_clientes.php">

                <span>🛒</span>

                Mi carrito

            </a>


            <!-- PEDIDOS -->

            <a
                href="pedidos_clientes.php"
                class="activo"
            >

                <span>▤</span>

                Mis pedidos

            </a>


            <!-- CUENTA -->

            <a href="cuenta.php">

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

                    Mis pedidos

                </h1>


                <p>

                    Consulta el historial de tus compras.

                </p>

            </div>


            <!-- SOLO INICIAL -->

            <div class="cliente-avatar">

                <?php

                echo htmlspecialchars(
                    $inicial_cliente
                );

                ?>

            </div>


        </header>


        <!-- =========================================
             MENSAJE DE CANCELACIÓN
             ========================================= -->

        <?php if (
            isset($_GET["cancelado"])
        ): ?>

            <div class="mensaje-exito">

                ✓ El pedido fue cancelado correctamente.
                El stock de los productos ha sido devuelto.

            </div>

        <?php endif; ?>


        <!-- =========================================
             MENSAJE DE ERROR
             ========================================= -->

        <?php if (
            isset($_GET["error"])
        ): ?>

            <div class="mensaje-error">

                ✕

                <?php

                echo htmlspecialchars(
                    $_GET["error"]
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- =========================================
             PEDIDOS
             ========================================= -->

        <section class="pedidos-contenedor">


            <h2>

                Historial de pedidos

            </h2>


            <p class="pedidos-descripcion">

                Aquí puedes consultar los pedidos
                que has realizado.

            </p>


            <?php if (
                $resultado &&
                $resultado->num_rows > 0
            ): ?>


                <div
                    style="
                        overflow-x:auto;
                    "
                >


                    <table
                        class="tabla-pedidos"
                    >


                        <thead>

                            <tr>

                                <th>
                                    Pedido
                                </th>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Total
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


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $pedido["fecha"]
                                    );

                                    ?>

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


                                <!-- ESTADO -->

                                <td>


                                    <?php

                                    $estado =
                                        $pedido["estado"];


                                    if (
                                        $estado ===
                                        "Cancelada"
                                    ) {

                                        $clase_estado =
                                            "estado-cancelada";

                                    } elseif (
                                        $estado ===
                                        "Pendiente"
                                    ) {

                                        $clase_estado =
                                            "estado-pendiente";

                                    } elseif (
                                        $estado ===
                                        "En preparación"
                                    ) {

                                        $clase_estado =
                                            "estado-preparacion";

                                    } elseif (
                                        $estado ===
                                        "En camino"
                                    ) {

                                        $clase_estado =
                                            "estado-camino";

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


                                </td>


                                <!-- ACCIÓN -->

                                <td>


                                    <!-- VER PEDIDO -->

                                    <a
                                        href="detalle_pedido_clientes.php?id=<?php echo intval($pedido["id"]); ?>"
                                        class="btn-ver-pedido"
                                    >

                                        Ver pedido

                                    </a>


                                    <!-- CANCELAR -->

                                    <?php if (
                                        $estado ===
                                        "Pendiente"
                                    ): ?>


                                        <form
                                            method="POST"
                                            action="cancelar_pedido.php"
                                            style="display:inline;"
                                            onsubmit="return confirm('¿Estás seguro de que deseas cancelar este pedido? El stock de los productos será devuelto.');"
                                        >


                                            <input
                                                type="hidden"
                                                name="pedido_id"
                                                value="<?php echo intval($pedido["id"]); ?>"
                                            >


                                            <button
                                                type="submit"
                                                class="btn-cancelar-pedido"
                                            >

                                                Cancelar

                                            </button>


                                        </form>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <!-- =================================
                     SIN PEDIDOS
                     ================================= -->

                <div class="sin-pedidos">


                    <div
                        class="sin-pedidos-icono"
                    >

                        📦

                    </div>


                    <h3>

                        Todavía no tienes pedidos

                    </h3>


                    <p>

                        Cuando realices una compra,
                        aparecerá aquí.

                    </p>


                </div>


            <?php endif; ?>


        </section>


    </main>


</div>


</body>

</html>