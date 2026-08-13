<?php

session_start();

if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/conexion.php";


/* =========================================
   OBTENER PRODUCTOS ACTIVOS
   ========================================= */

$sql = "
    SELECT *
    FROM productos
    WHERE activo = 1
    ORDER BY id DESC
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
        Productos | Changarro Súper y Más
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
                    Productos
                </h1>

                <p>
                    Administra los productos de Changarro Súper y Más
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
             PANEL DE PRODUCTOS
             ========================================= -->

        <section class="panel-productos">


            <!-- CABECERA -->

            <div class="cabecera-productos">


                <div>

                    <h2>
                        Lista de productos
                    </h2>

                    <p>
                        Productos registrados en el sistema
                    </p>

                </div>


                <a
                    href="agregar_producto.php"
                    class="btn-agregar"
                >

                    + Agregar producto

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
                                Código
                            </th>

                            <th>
                                Producto
                            </th>

                            <th>
                                Categoría
                            </th>

                            <th>
                                Compra
                            </th>

                            <th>
                                Venta
                            </th>

                            <th>
                                Stock
                            </th>

                            <th>
                                Fecha
                            </th>

                            <th>
                                Acciones
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php if (
                        $resultado->num_rows > 0
                    ): ?>


                        <?php while (
                            $producto =
                            $resultado->fetch_assoc()
                        ): ?>


                            <tr>


                                <!-- CÓDIGO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["codigo"]
                                        ?? "Sin código"
                                    );

                                    ?>

                                </td>


                                <!-- PRODUCTO -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["nombre"]
                                    );

                                    ?>

                                </td>


                                <!-- CATEGORÍA -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $producto["categoria"]
                                    );

                                    ?>

                                </td>


                                <!-- PRECIO DE COMPRA -->

                                <td>

                                    $

                                    <?php

                                    echo number_format(
                                        $producto["precio_compra"],
                                        2
                                    );

                                    ?>

                                </td>


                                <!-- PRECIO DE VENTA -->

                                <td>

                                    $

                                    <?php

                                    echo number_format(
                                        $producto["precio_venta"],
                                        2
                                    );

                                    ?>

                                </td>


                                <!-- STOCK -->

                                <td>


                                    <?php

                                    $stock =
                                        $producto["stock"];


                                    if (
                                        $stock == 0
                                    ) {

                                        $estado_stock =
                                            "rojo";

                                        $texto_stock =
                                            "Agotado";

                                    } elseif (
                                        $stock <= 10
                                    ) {

                                        $estado_stock =
                                            "amarillo";

                                        $texto_stock =
                                            "Stock bajo";

                                    } else {

                                        $estado_stock =
                                            "verde";

                                        $texto_stock =
                                            "Stock suficiente";

                                    }

                                    ?>


                                    <div
                                        class="stock-indicador"
                                    >


                                        <span
                                            class="stock-numero"
                                        >

                                            <?php

                                            echo $stock;

                                            ?>

                                        </span>


                                        <span
                                            class="stock-foco <?php echo $estado_stock; ?>"
                                            title="<?php echo $texto_stock; ?>"
                                        >
                                        </span>


                                    </div>


                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?php

                                    echo date(
                                        "d/m/Y",
                                        strtotime(
                                            $producto[
                                                "fecha_registro"
                                            ]
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>


                                    <a
                                        href="editar_producto.php?id=<?php echo $producto["id"]; ?>"
                                        class="btn-editar"
                                    >

                                        Editar

                                    </a>


                                    <a
                                        href="eliminar_producto.php?id=<?php echo $producto["id"]; ?>"
                                        class="btn-eliminar"
                                        onclick="return confirm('¿Deseas eliminar este producto?');"
                                    >

                                        Eliminar

                                    </a>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="8"
                                class="sin-productos"
                            >

                                No hay productos registrados todavía.

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