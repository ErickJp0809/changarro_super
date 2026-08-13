<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";


/* =========================================
   OBTENER VENTAS
   ========================================= */

$sql = "
    SELECT
        ventas.id,
        ventas.total,
        ventas.fecha,
        ventas.estado,
        usuarios.nombre AS usuario_nombre
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
        Ventas | Changarro Súper y Más
    </title>

    <link
        rel="stylesheet"
        href="../css/dashboard.css"
    >

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
                    Ventas
                </h1>

                <p>
                    Consulta y administra las ventas realizadas.
                </p>

            </div>


            <!-- PERFIL -->

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
             PANEL DE VENTAS
             ========================================= -->

        <section class="panel-productos">


            <!-- CABECERA -->

            <div class="cabecera-productos">


                <div>

                    <h2>
                        Historial de ventas
                    </h2>

                    <p>
                        Ventas registradas en el sistema.
                    </p>

                </div>


                <a
                    href="crear_venta.php"
                    class="btn-agregar"
                >

                    + Nueva venta

                </a>


            </div>


            <!-- =========================================
                 TABLA
                 ========================================= -->

            <div class="tabla-contenedor">


                <table>


                    <thead>

                        <tr>

                            <th>
                                Folio
                            </th>

                            <th>
                                Total
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Realizada por
                            </th>

                            <th>
                                Estado
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado &&
                        $resultado->num_rows > 0
                    ): ?>


                        <?php while (
                            $venta =
                            $resultado->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- FOLIO -->

                                <td>

                                    #

                                    <?php

                                    echo $venta["id"];

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


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo $venta["fecha"];

                                    ?>

                                </td>


                                <!-- REALIZADA POR -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $venta["usuario_nombre"]
                                        ?? "No disponible"
                                    );

                                    ?>

                                </td>


                                <!-- ESTADO -->

                                <td>


                                    <?php if (
                                        $venta["estado"]
                                        === "Cancelada"
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

                                            ● Completada

                                        </span>


                                    <?php endif; ?>


                                </td>


                                <!-- ACCIONES -->

                                <td>


                                    <a
                                        href="detalle_venta.php?id=<?php echo $venta["id"]; ?>"
                                        class="btn-editar"
                                    >

                                        Ver

                                    </a>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="6"
                                class="sin-datos"
                            >

                                No hay ventas registradas todavía.

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